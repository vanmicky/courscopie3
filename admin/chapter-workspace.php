<?php
// Shared, chapter-scoped queries and writes for the chapter workspace.
function workspace_types(): array {
    return [
        'documents' => ['table' => 'files', 'label' => 'Documents', 'title' => 'label'],
        'exercices' => ['table' => 'exercises', 'label' => 'Exercices', 'title' => 'title'],
        'videos' => ['table' => 'chapter_videos', 'label' => 'Vidéos', 'title' => 'title'],
        'qcm' => ['table' => 'qcms', 'label' => 'QCM', 'title' => 'title'],
        'flashcards' => ['table' => 'flashcard_decks', 'label' => 'Flashcards', 'title' => 'title'],
        'savoirs' => ['table' => 'chapter_skills', 'label' => 'Savoirs', 'title' => 'label'],
    ];
}

function workspace_rows(PDO $pdo, string $table, int $chapterId): array {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE chapter_id=? ORDER BY sort_order,id");
    $stmt->execute([$chapterId]);
    return $stmt->fetchAll();
}

function workspace_item(PDO $pdo, string $table, int $id, int $chapterId): array {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id=? AND chapter_id=?");
    $stmt->execute([$id, $chapterId]);
    $row = $stmt->fetch();
    if (!$row) throw new RuntimeException('Ce contenu ne fait pas partie du chapitre.');
    return $row;
}

function workspace_required(string $key, int $limit = 0): string {
    $value = trim((string)($_POST[$key] ?? ''));
    if ($value === '') throw new RuntimeException('Complétez tous les champs obligatoires.');
    if ($limit && preg_match_all('/./us', $value) > $limit) throw new RuntimeException('Le texte est trop long (maximum ' . $limit . ' caractères).');
    return $value;
}

/**
 * Valeur du champ « Publié ».
 *
 * Le formulaire envoie un champ caché à 0 puis la case à 1 quand elle est
 * cochée. `isset()` ne suffit donc pas : il est vrai même pour « 0 », ce qui
 * publiait tout par erreur. On lit la valeur.
 */
function workspace_published_flag(): int {
    return (string)($_POST['is_published'] ?? '0') === '1' ? 1 : 0;
}

function workspace_choice(string $key, array $values, string $fallback): string {
    $value = (string)($_POST[$key] ?? $fallback);
    if (!in_array($value, $values, true)) throw new RuntimeException('Valeur de champ non autorisée.');
    return $value;
}

/**
 * Colonnes d'image sur les cartes et les questions (créées à la demande).
 * `image_width` est un pourcentage de la largeur disponible : 10 à 100.
 */
function workspace_image_columns(PDO $pdo, string $table): bool {
    static $etat = [];
    if (array_key_exists($table, $etat)) return $etat[$table];
    try {
        $colonnes = [];
        foreach ($pdo->query("SHOW COLUMNS FROM `$table`") as $c) $colonnes[$c['Field']] = true;
        if (!isset($colonnes['image_path'])) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `image_path` VARCHAR(255) NULL AFTER `sort_order`");
        }
        if (!isset($colonnes['image_width'])) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `image_width` SMALLINT UNSIGNED NOT NULL DEFAULT 40 AFTER `image_path`");
        }
        // Une carte peut aussi illustrer son verso.
        if ($table === 'flashcards') {
            if (!isset($colonnes['image_back_path'])) {
                $pdo->exec("ALTER TABLE `flashcards` ADD COLUMN `image_back_path` VARCHAR(255) NULL AFTER `image_width`");
            }
            if (!isset($colonnes['image_back_width'])) {
                $pdo->exec("ALTER TABLE `flashcards` ADD COLUMN `image_back_width` SMALLINT UNSIGNED NOT NULL DEFAULT 40 AFTER `image_back_path`");
            }
        }
        return $etat[$table] = true;
    } catch (Throwable $e) {
        error_log('Image schema: ' . $e->getMessage());
        return $etat[$table] = false;
    }
}

/**
 * Traite l'image envoyée avec le formulaire.
 *
 * Renvoie uniquement les colonnes modifiées : si rien n'est envoyé ni coché, le
 * tableau est vide et l'image existante est conservée.
 */
function workspace_image_fields(string $tab, int $childId): array {
    $tables = ['qcm' => 'qcm_questions', 'flashcards' => 'flashcards'];
    if (!isset($tables[$tab])) return [];
    $pdo = db();
    if (!$pdo || !workspace_image_columns($pdo, $tables[$tab])) return [];

    // Les cartes ont deux faces : recto (`image`) et verso (`image_back`).
    $cotes = $tab === 'flashcards' ? ['image', 'image_back'] : ['image'];
    $sortie = [];
    foreach ($cotes as $prefixe) {
        $champs = workspace_image_cote($prefixe, $tab, $childId);
        if ($champs !== []) $sortie = array_merge($sortie, $champs);
    }
    return $sortie;
}

/** Traite une image (une face) : envoi, retrait, changement de largeur. */
function workspace_image_cote(string $prefixe, string $tab, int $childId): array {
    $cheminCol = $prefixe . '_path';
    $largeurCol = $prefixe . '_width';
    $largeur = min(100, max(10, (int)($_POST[$prefixe . '_width'] ?? 40)));

    // Retrait explicite.
    if (!empty($_POST[$prefixe . '_remove'])) {
        return [$cheminCol => null, $largeurCol => $largeur];
    }

    $envoi = $_FILES[$prefixe] ?? null;
    $aUnFichier = is_array($envoi) && ($envoi['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if (!$aUnFichier) {
        // Pas de nouveau fichier : on ne touche qu'à la largeur si elle est envoyée.
        return isset($_POST[$prefixe . '_width']) ? [$largeurCol => $largeur] : [];
    }

    if ($envoi['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('L’image n’a pas pu être transférée (code ' . (int)$envoi['error'] . ').');
    }
    if ($envoi['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('L’image dépasse 5 Mo. Réduis-la avant de l’envoyer.');
    }
    $infos = @getimagesize($envoi['tmp_name']);
    $types = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$infos || !isset($types[$infos[2]])) {
        throw new RuntimeException('Format d’image non pris en charge (PNG, JPG, WEBP ou GIF).');
    }

    $dossier = dirname(__DIR__) . '/assets/uploads';
    if (!is_dir($dossier) && !@mkdir($dossier, 0755, true) && !is_dir($dossier)) {
        throw new RuntimeException('Le dossier des images ne peut pas être créé.');
    }
    $nom = $tab . '-' . $prefixe . '-' . $childId . '-' . bin2hex(random_bytes(6)) . '.' . $types[$infos[2]];
    if (!@move_uploaded_file($envoi['tmp_name'], $dossier . '/' . $nom)) {
        throw new RuntimeException('Impossible d’enregistrer l’image sur le serveur.');
    }
    @chmod($dossier . '/' . $nom, 0644);

    return [$cheminCol => 'assets/uploads/' . $nom, $largeurCol => $largeur];
}

/**
 * Champ image : envoi direct depuis le formulaire, largeur réglable, retrait.
 *
 * @param string $prefixe Nom des champs (`image` pour le recto/la question,
 *                        `image_back` pour le verso d'une carte).
 */
function ws_image_field(array $values, string $prefixe = 'image', string $label = 'Image (facultative)'): void {
    $chemin = (string)($values[$prefixe . '_path'] ?? '');
    $largeur = max(10, min(100, (int)($values[$prefixe . '_width'] ?? 40)));
    ?>
    <fieldset class="ws-image-field">
      <legend><?= h($label) ?></legend>
      <?php if ($chemin !== ''): ?>
        <div class="ws-image-current">
          <img src="../<?= h($chemin) ?>" alt="" style="width:<?= $largeur ?>%">
          <label class="ws-image-remove"><input type="checkbox" name="<?= h($prefixe) ?>_remove" value="1"> Retirer cette image</label>
        </div>
      <?php endif; ?>
      <label>Envoyer une image
        <input type="file" name="<?= h($prefixe) ?>" accept="image/png,image/jpeg,image/webp,image/gif">
      </label>
      <label>Largeur affichée (%)
        <input type="number" name="<?= h($prefixe) ?>_width" min="10" max="100" step="5" value="<?= $largeur ?>">
      </label>
      <p class="ws-image-hint">PNG, JPG, WEBP ou GIF — 5 Mo maximum. La largeur règle la taille d'affichage, l'image reste nette.</p>
    </fieldset>
    <?php
}

function workspace_write(PDO $pdo, string $table, array $data, int $id, int $chapterId): int {
    if ($id) {
        workspace_item($pdo, $table, $id, $chapterId);
        $columns = array_map(static function ($name) { return "`$name`=?"; }, array_keys($data));
        $pdo->prepare("UPDATE `$table` SET " . implode(',', $columns) . ' WHERE id=? AND chapter_id=?')
            ->execute(array_merge(array_values($data), [$id, $chapterId]));
        return $id;
    }
    $data['chapter_id'] = $chapterId;
    $columns = '`' . implode('`,`', array_keys($data)) . '`';
    $pdo->prepare("INSERT INTO `$table` ($columns) VALUES (" . implode(',', array_fill(0, count($data), '?')) . ')')->execute(array_values($data));
    return (int)$pdo->lastInsertId();
}

function workspace_save(PDO $pdo, array $chapter, string $tab): int {
    $types = workspace_types();
    $chapterId = (int)$chapter['id'];
    $id = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? 'save');
    if ($tab === 'informations') {
        $title = workspace_required('title', 255);
        $pdo->prepare('UPDATE chapters SET title=?,summary=?,number=?,is_published=? WHERE id=?')->execute([
            $title, trim((string)($_POST['summary'] ?? '')), max(1, (int)($_POST['number'] ?? 1)), workspace_published_flag(), $chapterId,
        ]);
        return 0;
    }
    $table = $types[$tab]['table'];
    if ($action === 'bulk_status') {
        $selected = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['selected'] ?? [])))));
        if (!$selected) throw new RuntimeException('Sélectionnez au moins un contenu.');
        $status = ($_POST['bulk_value'] ?? '') === 'publish' ? 1 : 0;
        $placeholders = implode(',', array_fill(0, count($selected), '?'));
        $params = array_merge([$status, $chapterId], $selected);
        $pdo->prepare("UPDATE `$table` SET is_published=? WHERE chapter_id=? AND id IN ($placeholders)")->execute($params);
        return 0;
    }
    if ($action === 'reorder') {
        $ordered = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)($_POST['ordered_ids'] ?? ''))))));
        if (!$ordered) throw new RuntimeException('Ordre de ressources invalide.');
        $placeholders = implode(',', array_fill(0, count($ordered), '?'));
        $check = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE chapter_id=? AND id IN ($placeholders)");
        $check->execute(array_merge([$chapterId], $ordered));
        if ((int)$check->fetchColumn() !== count($ordered)) throw new RuntimeException('Une ressource ne fait pas partie de ce chapitre.');
        $update = $pdo->prepare("UPDATE `$table` SET sort_order=? WHERE id=? AND chapter_id=?");
        foreach ($ordered as $position => $orderedId) $update->execute([($position + 1) * 10, $orderedId, $chapterId]);
        return 0;
    }
    if ($id) workspace_item($pdo, $table, $id, $chapterId);
    if ($action === 'delete') {
        if (!$id) throw new RuntimeException('Sélectionnez un contenu.');
        // Keep shared files and their homework references; remove only the chapter association.
        $sql = $tab === 'documents' ? 'UPDATE files SET chapter_id=NULL WHERE id=? AND chapter_id=?' : "DELETE FROM `$table` WHERE id=? AND chapter_id=?";
        $pdo->prepare($sql)->execute([$id, $chapterId]);
        return 0;
    }
    if ($action === 'toggle') {
        if (!$id) throw new RuntimeException('Sélectionnez un contenu.');
        $pdo->prepare("UPDATE `$table` SET is_published=1-is_published WHERE id=? AND chapter_id=?")->execute([$id, $chapterId]);
        return $id;
    }
    if (in_array($action, ['save_child', 'delete_child'], true)) {
        if (!$id || !in_array($tab, ['qcm', 'flashcards'], true)) throw new RuntimeException('Série introuvable.');
        $childTable = $tab === 'qcm' ? 'qcm_questions' : 'flashcards';
        $parentColumn = $tab === 'qcm' ? 'qcm_id' : 'deck_id';
        $childId = (int)($_POST['child_id'] ?? 0);
        if ($childId) {
            $check = $pdo->prepare("SELECT id FROM `$childTable` WHERE id=? AND `$parentColumn`=?");
            $check->execute([$childId, $id]);
            if (!$check->fetch()) throw new RuntimeException('La question ou la carte ne fait pas partie de cette série.');
        }
        if ($action === 'delete_child') {
            $pdo->prepare("DELETE FROM `$childTable` WHERE id=? AND `$parentColumn`=?")->execute([$childId, $id]);
            return $id;
        }
        // Une carte : recto + verso. Une question : énoncé + explication.
        $data = $tab === 'qcm'
            ? ['question_text' => workspace_required('question_text'), 'explanation' => trim((string)($_POST['explanation'] ?? ''))]
            : ['front_text' => workspace_required('front_text'), 'back_text' => workspace_required('back_text')];
        $data['sort_order'] = (int)($_POST['sort_order'] ?? 0);

        // Image facultative, envoyée directement depuis le formulaire.
        $imageChamps = workspace_image_fields($tab, $childId);
        if ($imageChamps !== []) $data = array_merge($data, $imageChamps);

        // Réponses d'un QCM : de 2 à 6, seules les réponses remplies sont gardées.
        $choices = [];
        if ($tab === 'qcm') {
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $letter) {
                $texte = trim((string)($_POST['choice_' . $letter] ?? ''));
                if ($texte !== '') $choices[$letter] = $texte;
            }
            if (count($choices) < 2) {
                throw new RuntimeException('Indiquez au moins deux réponses (A et B au minimum).');
            }
            $correct = (string)($_POST['correct'] ?? 'A');
            if (!isset($choices[$correct])) {
                throw new RuntimeException('La bonne réponse doit être l’une des réponses saisies.');
            }
        }
        $pdo->beginTransaction();
        try {
            if ($childId) {
                $sets = array_map(static function ($key) { return "`$key`=?"; }, array_keys($data));
                $pdo->prepare("UPDATE `$childTable` SET " . implode(',', $sets) . ' WHERE id=?')->execute(array_merge(array_values($data), [$childId]));
            } else {
                $data[$parentColumn] = $id;
                $pdo->prepare("INSERT INTO `$childTable` (`" . implode('`,`', array_keys($data)) . '`) VALUES (' . implode(',', array_fill(0, count($data), '?')) . ')')->execute(array_values($data));
                $childId = (int)$pdo->lastInsertId();
            }
            if ($tab === 'qcm') {
                $pdo->prepare('DELETE FROM qcm_choices WHERE question_id=?')->execute([$childId]);
                $insert = $pdo->prepare('INSERT INTO qcm_choices(question_id,choice_label,choice_text,is_correct,sort_order) VALUES(?,?,?,?,?)');
                foreach ($choices as $letter => $text) $insert->execute([$childId, $letter, $text, $letter === $correct ? 1 : 0, ord($letter) - 65]);
            }
            $pdo->commit();
        } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
        return $id;
    }
    if ($action !== 'save') throw new RuntimeException('Action inconnue.');
    $data = ['is_published' => workspace_published_flag(), 'sort_order' => (int)($_POST['sort_order'] ?? 0)];
    if ($tab === 'documents') {
        $data['label'] = workspace_required('label', 190);
        $data['resource_type'] = workspace_choice('resource_type', array_keys(document_type_labels()), 'document');
        $data['level_id'] = (int)$chapter['level_id'];
        if (!$id) {
            store_uploaded_file($_FILES['file'] ?? [], array_merge($data, ['chapter_id' => $chapterId]));
            return 0;
        }
    } elseif ($tab === 'savoirs') {
        $data['label'] = workspace_required('label', 500);
        $data['category'] = workspace_choice('category', ['Savoir', 'Savoir-faire'], 'Savoir');
    } else {
        $data['title'] = workspace_required('title', 255);
        if ($tab === 'videos') {
            $data['url'] = workspace_required('url', 500);
            if (!filter_var($data['url'], FILTER_VALIDATE_URL) || !preg_match('~^https?://~i', $data['url'])) throw new RuntimeException('Saisissez une adresse vidéo HTTP ou HTTPS valide.');
            $data['description'] = trim((string)($_POST['description'] ?? ''));
        } elseif ($tab === 'exercices') {
            $data['statement'] = workspace_required('statement');
            $data['correction'] = trim((string)($_POST['correction'] ?? ''));
            $data['category'] = workspace_choice('category', ['ds','brevet','bac'], 'ds');
            $data['difficulty'] = workspace_choice('difficulty', ['facile','normal','difficile','défi'], 'normal');
        } else {
            $data['level_id'] = (int)$chapter['level_id'];
            if (!$id) {
                $base = substr(slugify_text($data['title'], $tab), 0, 155) . '-' . bin2hex(random_bytes(4));
                $data['slug'] = $base;
            }
        }
    }
    return workspace_write($pdo, $table, $data, $id, $chapterId);
}
