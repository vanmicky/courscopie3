<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = db();
if (!$pdo) {
    page_head('Admin indisponible', '', ['math' => false]);
    echo '<main class="page-container py-8"><p>Base non configuree.</p></main></body></html>';
    exit;
}
admin_ensure_v64_schema();

$contentTables = [
    'levels', 'subjects', 'chapters', 'lessons', 'exercises', 'qcms',
    'qcm_questions', 'qcm_choices', 'flashcard_decks', 'flashcards', 'files',
    'chapter_videos', 'chapter_skills', 'assignments', 'assignment_files', 'assignment_links', 'announcements',
];

function backup_table_name(string $table): string {
    return '`' . str_replace('`', '``', $table) . '`';
}

function backup_table_columns(PDO $pdo, string $table): array {
    $stmt = $pdo->query('SHOW COLUMNS FROM ' . backup_table_name($table));
    return array_map(static fn($row) => $row['Field'], $stmt->fetchAll());
}

function backup_ensure_log_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS backup_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(40) NOT NULL,
        mode VARCHAR(40) DEFAULT NULL,
        filename VARCHAR(255) DEFAULT NULL,
        row_count INT NOT NULL DEFAULT 0,
        table_count INT NOT NULL DEFAULT 0,
        status VARCHAR(40) NOT NULL DEFAULT 'success',
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function backup_log(PDO $pdo, string $action, string $mode, string $filename, int $rows, int $tables, string $status = 'success', string $message = ''): void {
    try {
        backup_ensure_log_table($pdo);
        $stmt = $pdo->prepare('INSERT INTO backup_logs (action,mode,filename,row_count,table_count,status,message) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$action, $mode, $filename, $rows, $tables, $status, $message]);
    } catch (Throwable $e) {
        // La sauvegarde reste utilisable meme si le journal ne peut pas etre ecrit.
    }
}

function backup_build_payload(PDO $pdo, array $tables): array {
    $payload = [
        'app' => 'les-cours-m-gedion',
        'format' => 'content-backup-json',
        'version' => 'V73',
        'generated_at' => gmdate('c'),
        'tables' => [],
    ];
    foreach ($tables as $table) {
        $payload['tables'][$table] = $pdo->query('SELECT * FROM ' . backup_table_name($table))->fetchAll();
    }
    return $payload;
}

function backup_payload_row_count(array $payload): int {
    $count = 0;
    foreach (($payload['tables'] ?? []) as $rows) {
        if (is_array($rows)) $count += count($rows);
    }
    return $count;
}

function backup_send_json(PDO $pdo, array $tables): void {
    $payload = backup_build_payload($pdo, $tables);
    $filename = 'cours-gedion-contenus-' . date('Y-m-d-His') . '.json';
    backup_log($pdo, 'export', 'json', $filename, backup_payload_row_count($payload), count($tables));
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function backup_add_uploads_to_zip(ZipArchive $zip, string $uploadsDir): int {
    $added = 0;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploadsDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $file) {
        if (!$file->isFile()) continue;
        $absolute = $file->getPathname();
        $relative = 'uploads/' . ltrim(str_replace('\\', '/', substr($absolute, strlen($uploadsDir))), '/');
        if ($zip->addFile($absolute, $relative)) $added++;
    }
    return $added;
}

function backup_send_zip(PDO $pdo, array $tables, bool $complete): ?string {
    if (!class_exists('ZipArchive')) return "L'extension PHP ZipArchive n'est pas activee sur cet hebergement.";
    $uploadsDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadsDir)) return 'Le dossier uploads est introuvable.';

    $label = $complete ? 'sauvegarde-complete' : 'uploads';
    $filename = 'cours-gedion-' . $label . '-' . date('Y-m-d-His') . '.zip';
    $zipPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return "Impossible de preparer l'archive des fichiers.";
    }

    $rowCount = 0;
    if ($complete) {
        $payload = backup_build_payload($pdo, $tables);
        $rowCount = backup_payload_row_count($payload);
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false || !$zip->addFromString('contenus.json', $json)) {
            $zip->close();
            @unlink($zipPath);
            return "Impossible d'ajouter les contenus a l'archive.";
        }
        $zip->addFromString('LISEZ-MOI.txt', "Sauvegarde complete V73\n- contenus.json : donnees dynamiques\n- uploads/ : images et documents\n\nLes comptes administrateurs ne sont pas inclus.\n");
    }
    $fileCount = backup_add_uploads_to_zip($zip, $uploadsDir);
    $zip->close();
    backup_log($pdo, 'export', $complete ? 'complete' : 'uploads', $filename, $rowCount, $complete ? count($tables) : 0, 'success', $fileCount . ' fichier(s) inclus');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('X-Content-Type-Options: nosniff');
    readfile($zipPath);
    @unlink($zipPath);
    exit;
}

function backup_read_uploaded_json(array $file, array $tables): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Choisissez un fichier JSON exporte depuis le site.');
    }
    if (($file['size'] ?? 0) < 2 || ($file['size'] ?? 0) > 25 * 1024 * 1024) {
        throw new RuntimeException('Le fichier JSON doit faire moins de 25 Mo.');
    }
    if (strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION)) !== 'json') {
        throw new RuntimeException('Le fichier choisi doit avoir l extension .json.');
    }
    $payload = json_decode((string)file_get_contents($file['tmp_name']), true);
    if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Le fichier JSON est illisible ou endommage.');
    }
    if (($payload['app'] ?? '') !== 'les-cours-m-gedion' || ($payload['format'] ?? '') !== 'content-backup-json') {
        throw new RuntimeException('Ce fichier ne correspond pas a une sauvegarde du site.');
    }
    if (!preg_match('/^V(5[8-9]|[6-9][0-9])$/', (string)($payload['version'] ?? ''))) {
        throw new RuntimeException('Version de sauvegarde non compatible avec V73.');
    }
    if (!isset($payload['tables']) || !is_array($payload['tables'])) {
        throw new RuntimeException('La liste des contenus est absente.');
    }
    $unknown = array_diff(array_keys($payload['tables']), $tables);
    foreach (['chapter_videos', 'chapter_skills'] as $optionalTable) {
        if (!isset($payload['tables'][$optionalTable])) $payload['tables'][$optionalTable] = [];
    }
    $missing = array_diff($tables, array_keys($payload['tables']));
    if ($unknown) throw new RuntimeException('Table non autorisee dans le fichier : ' . implode(', ', $unknown));
    if ($missing) throw new RuntimeException('Sauvegarde incomplete. Contenus manquants : ' . implode(', ', $missing));
    foreach ($tables as $table) {
        if (!is_array($payload['tables'][$table])) throw new RuntimeException('Donnees invalides pour : ' . $table);
    }
    if (backup_payload_row_count($payload) > 250000) {
        throw new RuntimeException('Cette sauvegarde contient trop de lignes pour une restauration automatique.');
    }
    return $payload;
}

function backup_import_content(PDO $pdo, array $tables, array $payload): int {
    $inserted = 0;
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    $pdo->beginTransaction();
    try {
        foreach (array_reverse($tables) as $table) $pdo->exec('DELETE FROM ' . backup_table_name($table));
        foreach ($tables as $table) {
            $columns = backup_table_columns($pdo, $table);
            foreach ($payload['tables'][$table] as $row) {
                if (!is_array($row)) continue;
                $row = array_intersect_key($row, array_flip($columns));
                if (!$row) continue;
                $names = array_keys($row);
                $quoted = array_map(static fn($name) => backup_table_name($name), $names);
                $placeholders = array_map(static fn($name) => ':' . $name, $names);
                $stmt = $pdo->prepare('INSERT INTO ' . backup_table_name($table) . ' (' . implode(',', $quoted) . ') VALUES (' . implode(',', $placeholders) . ')');
                foreach ($row as $key => $value) $stmt->bindValue(':' . $key, $value);
                $stmt->execute();
                $inserted++;
            }
        }
        $pdo->commit();
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        return $inserted;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        throw $e;
    }
}

function backup_unique_slug(PDO $pdo, string $table, string $slug): string {
    $base = trim($slug) !== '' ? trim($slug) : 'contenu-importe';
    $base = substr($base, 0, 170);
    $candidate = $base;
    $suffix = 2;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . backup_table_name($table) . ' WHERE slug=?');
    while (true) {
        $stmt->execute([$candidate]);
        if ((int)$stmt->fetchColumn() === 0) return $candidate;
        $candidate = $base . '-import-' . $suffix++;
    }
}

function backup_append_content(PDO $pdo, array $tables, array $payload): int {
    $foreignKeys = [
        'chapters' => ['level_id' => 'levels', 'subject_id' => 'subjects'],
        'lessons' => ['chapter_id' => 'chapters'],
        'exercises' => ['chapter_id' => 'chapters'],
        'qcms' => ['chapter_id' => 'chapters', 'level_id' => 'levels'],
        'qcm_questions' => ['qcm_id' => 'qcms'],
        'qcm_choices' => ['question_id' => 'qcm_questions'],
        'flashcard_decks' => ['chapter_id' => 'chapters', 'level_id' => 'levels'],
        'flashcards' => ['deck_id' => 'flashcard_decks'],
        'files' => ['chapter_id' => 'chapters', 'level_id' => 'levels'],
        'chapter_videos' => ['chapter_id' => 'chapters'],
        'chapter_skills' => ['chapter_id' => 'chapters'],
        'assignments' => ['level_id' => 'levels', 'subject_id' => 'subjects', 'chapter_id' => 'chapters'],
        'assignment_files' => ['assignment_id' => 'assignments', 'file_id' => 'files'],
        'assignment_links' => ['assignment_id' => 'assignments'],
        'announcements' => ['level_id' => 'levels'],
    ];
    $slugTables = ['levels', 'subjects', 'chapters', 'qcms', 'flashcard_decks'];
    $idMaps = [];
    $inserted = 0;
    $pdo->beginTransaction();
    try {
        foreach ($tables as $table) {
            $columns = backup_table_columns($pdo, $table);
            $idMaps[$table] = [];
            foreach ($payload['tables'][$table] as $sourceRow) {
                if (!is_array($sourceRow)) continue;
                $oldId = isset($sourceRow['id']) ? (string)$sourceRow['id'] : '';
                $row = array_intersect_key($sourceRow, array_flip($columns));
                unset($row['id']);

                foreach (($foreignKeys[$table] ?? []) as $column => $parentTable) {
                    if (!array_key_exists($column, $row) || $row[$column] === null || $row[$column] === '') continue;
                    $oldForeignId = (string)$row[$column];
                    if (!isset($idMaps[$parentTable][$oldForeignId])) {
                        throw new RuntimeException('Lien incomplet pendant l ajout : ' . $table . '.' . $column);
                    }
                    $row[$column] = $idMaps[$parentTable][$oldForeignId];
                }

                if (in_array($table, $slugTables, true) && isset($row['slug'])) {
                    $row['slug'] = backup_unique_slug($pdo, $table, (string)$row['slug']);
                }
                if (!$row) continue;
                $names = array_keys($row);
                $quoted = array_map(static fn($name) => backup_table_name($name), $names);
                $placeholders = array_map(static fn($name) => ':' . $name, $names);
                $stmt = $pdo->prepare('INSERT INTO ' . backup_table_name($table) . ' (' . implode(',', $quoted) . ') VALUES (' . implode(',', $placeholders) . ')');
                foreach ($row as $key => $value) $stmt->bindValue(':' . $key, $value);
                $stmt->execute();
                if ($oldId !== '') $idMaps[$table][$oldId] = (int)$pdo->lastInsertId();
                $inserted++;
            }
        }
        $pdo->commit();
        return $inserted;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

$message = '';
$error = '';
$preview = null;
$previewName = '';

if (($_GET['export'] ?? '') === 'json') backup_send_json($pdo, $contentTables);
if (($_GET['export'] ?? '') === 'uploads') $error = backup_send_zip($pdo, $contentTables, false);
if (($_GET['export'] ?? '') === 'complete') $error = backup_send_zip($pdo, $contentTables, true);

verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $file = $_FILES['backup_file'] ?? [];
    $filename = basename((string)($file['name'] ?? ''));
    try {
        $payload = backup_read_uploaded_json($file, $contentTables);
        if ($action === 'preview_json') {
            $preview = $payload;
            $previewName = $filename;
            $message = 'Fichier verifie. Consultez le resume avant de restaurer.';
            backup_log($pdo, 'verification', 'json', $filename, backup_payload_row_count($payload), count($contentTables));
        } elseif ($action === 'import_json') {
            if (trim((string)($_POST['confirm_text'] ?? '')) !== 'RESTAURER') {
                throw new RuntimeException('Saisissez exactement RESTAURER pour confirmer.');
            }
            $count = backup_import_content($pdo, $contentTables, $payload);
            backup_log($pdo, 'restauration', 'replace', $filename, $count, count($contentTables));
            $message = $count . ' lignes restaurees. Les comptes admin sont restes inchanges.';
        } elseif ($action === 'append_json') {
            if (trim((string)($_POST['confirm_text'] ?? '')) !== 'AJOUTER') {
                throw new RuntimeException('Saisissez exactement AJOUTER pour confirmer.');
            }
            $count = backup_append_content($pdo, $contentTables, $payload);
            backup_log($pdo, 'import', 'append', $filename, $count, count($contentTables));
            $message = $count . ' lignes ajoutees sans supprimer les contenus existants.';
        } else {
            throw new RuntimeException('Action inconnue.');
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
        backup_log($pdo, $action === 'preview_json' ? 'verification' : ($action === 'append_json' ? 'import' : 'restauration'), 'json', $filename, 0, 0, 'error', $error);
    }
}

$counts = [];
foreach ($contentTables as $table) {
    try {
        $counts[$table] = (int)$pdo->query('SELECT COUNT(*) FROM ' . backup_table_name($table))->fetchColumn();
    } catch (Throwable $e) {
        $counts[$table] = 0;
    }
}

$history = [];
try {
    backup_ensure_log_table($pdo);
    $history = $pdo->query('SELECT * FROM backup_logs ORDER BY id DESC LIMIT 20')->fetchAll();
} catch (Throwable $e) {}

page_head('Admin - Sauvegarde', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
    <a class="btn-secondary" href="systeme.php">État du système</a>
  </div>

  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin · V73</span>
    <h1 class="page-title mt-4">Sauvegardes sécurisées</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Conservez les contenus, les images et les documents. Vérifiez toujours un fichier JSON avant sa restauration.</p>
  </section>

  <?php if ($message): ?><p class="notice-success mt-5" role="status"><?= h($message) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="notice-error mt-5" role="alert"><?= h($error) ?></p><?php endif; ?>

  <section class="backup-grid mt-8">
    <article class="card backup-feature-card p-6">
      <span class="badge">Recommandé</span>
      <h2 class="section-title mt-3">Sauvegarde complète</h2>
      <p class="mt-3 muted">Un seul ZIP contenant le fichier de données JSON, les images et tous les documents envoyés.</p>
      <a class="btn-primary mt-5 inline-flex" href="sauvegarde.php?export=complete">Télécharger le ZIP complet</a>
    </article>
    <article class="card p-6">
      <span class="badge">Données</span>
      <h2 class="section-title mt-3">Contenus JSON</h2>
      <p class="mt-3 muted">Niveaux, matières, chapitres, cours, devoirs, QCM, flashcards, exercices et liens.</p>
      <a class="btn-secondary mt-5 inline-flex" href="sauvegarde.php?export=json">Exporter le JSON</a>
    </article>
    <article class="card p-6">
      <span class="badge">Documents</span>
      <h2 class="section-title mt-3">Dossier uploads</h2>
      <p class="mt-3 muted">Images et fichiers uniquement. Cette option nécessite ZipArchive chez l’hébergeur.</p>
      <a class="btn-secondary mt-5 inline-flex" href="sauvegarde.php?export=uploads">Télécharger les fichiers</a>
    </article>
  </section>

  <section class="card mt-8 p-6 sm:p-8">
    <div class="backup-section-heading">
      <div><span class="badge">Étape 1</span><h2 class="section-title mt-3">Vérifier une sauvegarde</h2></div>
      <p class="muted">Aucun contenu n’est modifié pendant cette vérification.</p>
    </div>
    <form method="post" enctype="multipart/form-data" class="backup-upload-form mt-5">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="preview_json">
      <label class="grid gap-2 font-black">Fichier JSON
        <input type="file" name="backup_file" accept=".json,application/json" required>
      </label>
      <button class="btn-primary" type="submit">Vérifier le fichier</button>
    </form>
  </section>

  <?php if ($preview): ?>
    <section class="card backup-preview mt-8 p-6 sm:p-8">
      <span class="badge">Étape 2</span>
      <h2 class="section-title mt-3">Aperçu validé</h2>
      <dl class="backup-meta mt-5">
        <div><dt>Fichier</dt><dd><?= h($previewName) ?></dd></div>
        <div><dt>Version</dt><dd><?= h($preview['version'] ?? '') ?></dd></div>
        <div><dt>Date de création</dt><dd><?= h($preview['generated_at'] ?? 'Non indiquée') ?></dd></div>
        <div><dt>Total</dt><dd><?= backup_payload_row_count($preview) ?> lignes</dd></div>
      </dl>
      <div class="backup-table-grid mt-5">
        <?php foreach ($contentTables as $table): ?>
          <div class="backup-table-row"><strong><?= h($table) ?></strong><span><?= count($preview['tables'][$table]) ?></span></div>
        <?php endforeach; ?>
      </div>
      <div class="backup-choice-grid mt-6">
        <article class="backup-choice is-safe">
          <h3>Ajouter sans écraser</h3>
          <p>Conserve le site actuel et ajoute une copie des contenus. Les adresses identiques sont renommées automatiquement.</p>
          <form method="post" enctype="multipart/form-data" class="grid gap-3 mt-5">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="append_json">
            <label class="grid gap-2 font-black">Sélectionnez à nouveau le même fichier
              <input type="file" name="backup_file" accept=".json,application/json" required>
            </label>
            <label class="grid gap-2 font-black">Confirmation
              <input type="text" name="confirm_text" autocomplete="off" placeholder="Saisir AJOUTER" required>
            </label>
            <button class="btn-primary" type="submit">Ajouter les contenus</button>
          </form>
        </article>
        <article class="backup-choice is-danger">
          <h3>Remplacer tous les contenus</h3>
          <p>Efface les contenus actuels et restaure exactement la sauvegarde. Les comptes admin ne sont pas modifiés.</p>
          <form method="post" enctype="multipart/form-data" class="grid gap-3 mt-5">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="import_json">
            <label class="grid gap-2 font-black">Sélectionnez à nouveau le même fichier
              <input type="file" name="backup_file" accept=".json,application/json" required>
            </label>
            <label class="grid gap-2 font-black">Confirmation
              <input type="text" name="confirm_text" autocomplete="off" placeholder="Saisir RESTAURER" required>
            </label>
            <button class="btn-danger" type="submit">Remplacer et restaurer</button>
          </form>
        </article>
      </div>
    </section>
  <?php endif; ?>

  <section class="card mt-8 p-6">
    <h2 class="section-title">Contenus actuellement en ligne</h2>
    <div class="backup-table-grid mt-5">
      <?php foreach ($counts as $table => $count): ?>
        <div class="backup-table-row"><strong><?= h($table) ?></strong><span><?= (int)$count ?></span></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="card mt-8 p-6">
    <div class="backup-section-heading"><div><span class="badge">Journal</span><h2 class="section-title mt-3">Dernières opérations</h2></div><p class="muted">Les 20 opérations les plus récentes.</p></div>
    <?php if (!$history): ?>
      <p class="empty-state mt-5">Aucune opération enregistrée pour le moment.</p>
    <?php else: ?>
      <div class="backup-history mt-5">
        <?php foreach ($history as $entry): ?>
          <article class="backup-history-row">
            <div><strong><?= h(ucfirst($entry['action'])) ?></strong><span><?= h($entry['filename'] ?: $entry['mode']) ?></span></div>
            <div><span class="status-pill <?= $entry['status'] === 'success' ? 'is-success' : 'is-error' ?>"><?= $entry['status'] === 'success' ? 'Réussi' : 'Erreur' ?></span><time><?= h($entry['created_at']) ?></time></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
