<?php
require_once __DIR__ . '/auth.php';
require_admin();
verify_csrf();
require_once __DIR__ . '/chapter-workspace.php';
require_once __DIR__ . '/chapter-insights.php';
$pdo = db();
$schema = admin_ensure_v69_schema();
$chapterId = (int)($_GET['chapter_id'] ?? 0);
$stmt = $pdo->prepare('SELECT c.*,l.short AS level_short,l.accent FROM chapters c JOIN levels l ON l.id=c.level_id WHERE c.id=?');
$stmt->execute([$chapterId]);
$chapter = $stmt->fetch();
if (!$chapter) {
    http_response_code(404);
    page_head('Chapitre introuvable'); admin_nav();
    echo '<main class="page-container py-8"><h1>Chapitre introuvable</h1><a href="chapitres.php">Retour aux chapitres</a></main></body></html>';
    exit;
}
$types = workspace_types();
$tab = (string)($_GET['tab'] ?? 'informations');
if ($tab !== 'informations' && !isset($types[$tab])) $tab = 'informations';
$error = $schema['ok'] ? '' : $schema['message'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $schema['ok']) {
    try {
        if (($_POST['action'] ?? '') === 'duplicate_complete') {
            $newId = duplicate_complete_chapter($pdo, $chapterId, (int)($_POST['target_level_id'] ?? 0));
            admin_flash('success', 'Chapitre et toutes ses ressources dupliqués en brouillon.');
            header('Location: chapitre.php?' . http_build_query(['chapter_id' => $newId]));
            exit;
        }
        if (($_POST['action'] ?? '') === 'chapter_toggle') {
            $nouveauStatut = (int)$chapter['is_published'] === 1 ? 0 : 1;
            $pdo->prepare('UPDATE chapters SET is_published=? WHERE id=?')->execute([$nouveauStatut, $chapterId]);
            admin_flash('success', $nouveauStatut ? 'Chapitre publié.' : 'Chapitre passé en brouillon.');
            header('Location: chapitre.php?' . http_build_query(['chapter_id' => $chapterId, 'tab' => $tab]));
            exit;
        }
        $savedId = workspace_save($pdo, $chapter, $tab);
        $messages = ['delete'=>'Contenu retiré du chapitre.','bulk_status'=>'Statut des contenus mis à jour.','reorder'=>'Nouvel ordre enregistré.'];
        $action = (string)($_POST['action'] ?? '');
        $message = $messages[$action] ?? 'Modifications enregistrées.';
        // Prévenir quand un contenu enregistré n'apparaîtra pas sur le site.
        if ($action === 'save' && !isset($_POST['is_published'])) {
            $message = 'Enregistré en brouillon : ce contenu reste invisible sur le site.';
        }
        admin_flash('success', $message);
        // Après une carte enregistrée, on revient sur le bloc des cartes.
        $ancre = (($_POST['action'] ?? '') === 'save_child') ? '#questions' : '';
        header('Location: chapitre.php?' . http_build_query(['chapter_id' => $chapterId, 'tab' => $tab, 'edit' => $savedId]) . $ancre);
        exit;
    } catch (Throwable $e) {
        if ($e instanceof PDOException) { error_log($e->getMessage()); $error = 'Enregistrement impossible. Vérifiez les champs et réessayez.'; }
        else $error = $e->getMessage();
    }
}
$all = [];
foreach ($types as $key => $type) $all[$key] = $schema['ok'] ? workspace_rows($pdo, $type['table'], $chapterId) : [];
$health = $schema['ok'] ? chapter_health($pdo, $chapter) : ['score'=>0,'completed'=>0,'total'=>7,'checks'=>[],'alerts'=>[]];
$levels = $pdo->query('SELECT id,short,name FROM levels ORDER BY sort_order,id')->fetchAll();
$edit = null;
$editId = (int)($_GET['edit'] ?? 0);
foreach ($all[$tab] ?? [] as $row) if ((int)$row['id'] === $editId) $edit = $row;
$values = $tab === 'informations' ? $chapter : ($edit ?? []);
if (!$edit && $tab === 'exercices') $values['category'] = 'ds';
if ($error && ($_POST['action'] ?? '') === 'save') {
    $values = $_POST;
    $values['is_published'] = isset($_POST['is_published']) ? 1 : 0;
}
$children = [];
$childEdit = [];
if ($edit && in_array($tab, ['qcm','flashcards'], true)) {
    $children = $tab === 'qcm' ? get_qcm_questions($editId) : get_flashcards($editId);
    foreach ($children as $child) if ((int)$child['id'] === (int)($_GET['child'] ?? 0)) $childEdit = $child;
    if ($error && ($_POST['action'] ?? '') === 'save_child') {
        $childEdit = $_POST;
        $childEdit['id'] = (int)($_POST['child_id'] ?? 0);
    }
}
function ws_url(array $extra = []): string {
    global $chapterId, $tab;
    return 'chapitre.php?' . http_build_query(array_merge(['chapter_id' => $chapterId, 'tab' => $tab], $extra));
}
function ws_field(string $label, string $name, array $values, string $type = 'text', bool $required = false): void {
    $value = $values[$name] ?? '';
    echo '<label class="workspace-field">' . h($label);
    if ($type === 'textarea') echo '<textarea rows="4" name="' . h($name) . '"' . ($required ? ' required' : '') . '>' . h((string)$value) . '</textarea>';
    else echo '<input type="' . h($type) . '" name="' . h($name) . '" value="' . h((string)$value) . '"' . ($required ? ' required' : '') . '>';
    echo '</label>';
}
function ws_select(string $label, string $name, array $choices, array $values, string $default = ''): void {
    echo '<label class="workspace-field">' . h($label) . '<select name="' . h($name) . '">';
    foreach ($choices as $value => $text) echo '<option value="' . h($value) . '"' . (($values[$name] ?? $default) === $value ? ' selected' : '') . '>' . h($text) . '</option>';
    echo '</select></label>';
}
function ws_hidden(string $action, int $id = 0): void {
    echo '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '"><input type="hidden" name="action" value="' . h($action) . '"><input type="hidden" name="id" value="' . $id . '">';
}
page_head('Gérer : ' . $chapter['title']); admin_nav();
?>
<main class="page-container py-8 chapter-workspace" style="--accent:<?= h($chapter['accent']) ?>">
  <a class="nav-link" href="chapitres.php">← Tous les chapitres</a>
  <header class="workspace-heading">
    <div>
      <span class="workspace-badge"><?= h($chapter['level_short']) ?> · Chapitre <?= (int)$chapter['number'] ?></span>
      <h1><?= h($chapter['title']) ?></h1>
      <form method="post" class="chapter-publish">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="chapter_toggle">
        <span class="status-pill <?= $chapter['is_published'] ? 'is-live' : 'is-draft' ?>" aria-live="polite"><?= $chapter['is_published'] ? 'Publié' : 'Brouillon' ?></span>
        <button class="chapter-publish-btn" type="submit"><?= $chapter['is_published'] ? 'Passer en brouillon' : 'Publier le chapitre' ?></button>
        <?php if ($chapter['is_published']): ?><a class="chapter-view-link" href="../chapitre.php?slug=<?= h($chapter['slug']) ?>" target="_blank" rel="noopener">Voir sur le site ↗</a><?php endif; ?>
      </form>
    </div>
    <div class="workspace-heading-actions"><a class="btn-primary" href="../chapitre.php?slug=<?= h($chapter['slug']) ?>&preview=1" target="_blank" rel="noopener">Prévisualiser comme un élève ↗</a><details class="workspace-duplicate"><summary class="btn-secondary">Dupliquer</summary><form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="duplicate_complete"><label>Niveau de destination<select name="target_level_id"><?php foreach ($levels as $level): ?><option value="<?= (int)$level['id'] ?>" <?= (int)$level['id'] === (int)$chapter['level_id'] ? 'selected' : '' ?>><?= h($level['short'] . ' — ' . $level['name']) ?></option><?php endforeach; ?></select></label><button class="btn-primary" type="submit">Créer la copie complète</button></form></details></div>
  </header>
  <nav class="workspace-tabs" aria-label="Ressources du chapitre">
    <a href="<?= h(ws_url(['tab'=>'informations'])) ?>" <?= $tab === 'informations' ? 'aria-current="page"' : '' ?>>Informations</a>
    <?php foreach ($types as $key => $type): ?><a href="<?= h(ws_url(['tab'=>$key])) ?>" <?= $tab === $key ? 'aria-current="page"' : '' ?>><?= h($type['label']) ?><span><?= count($all[$key]) ?></span></a><?php endforeach; ?>
  </nav>
  <?php if ($error): ?><div class="admin-inline-notice is-error" role="alert"><?= h($error) ?></div><?php endif; ?>
  <?php if ($schema['ok']): ?>
  <?php if ($edit && in_array($tab, ['qcm', 'flashcards'], true)): ?>
    <div class="workspace-selected" role="status">
      <span class="workspace-selected-badge">Série sélectionnée</span>
      <strong><?= h($edit['title']) ?></strong>
      <span class="muted"><?= count($children) ?> <?= $tab === 'qcm' ? 'question(s)' : 'carte(s)' ?></span>
      <span class="workspace-selected-actions">
        <a class="btn-primary" href="#questions"><?= $tab === 'qcm' ? 'Ajouter une question' : 'Ajouter une carte' ?></a>
        <a class="btn-secondary" href="<?= h(ws_url(['edit' => 0])) ?>">Changer de série</a>
      </span>
    </div>
  <?php elseif (in_array($tab, ['qcm', 'flashcards'], true) && $all[$tab]): ?>
    <p class="workspace-hint">Choisissez une série dans la liste pour <?= $tab === 'qcm' ? 'ajouter ou modifier ses questions' : 'ajouter ou modifier ses cartes' ?>. Le bouton « Modifier » de chaque série ouvre son contenu.</p>
  <?php endif; ?>
  <div class="workspace-columns">
    <section class="workspace-editor">
      <h2 class="workspace-form-title">
        <?php if ($tab !== 'informations'): ?><span class="workspace-form-badge <?= $edit ? 'is-edit' : 'is-add' ?>"><?= $edit ? 'Modifier' : 'Ajouter' ?></span><?php endif; ?>
        <?= $tab === 'informations' ? 'Informations du chapitre' : h($types[$tab]['label']) ?>
        <?php if ($edit): ?><span class="workspace-form-subject">« <?= h($edit[$types[$tab]['title']] ?? '') ?> »</span><?php endif; ?>
      </h2>
      <form method="post" enctype="multipart/form-data" class="workspace-form" data-workspace-form>
        <?php ws_hidden('save', $edit ? (int)$edit['id'] : 0); ?>
        <?php if ($tab === 'informations'): ?>
          <?php ws_field('Titre', 'title', $values, 'text', true); ws_field('Numéro', 'number', $values, 'number', true); ws_field('Résumé', 'summary', $values, 'textarea'); ?>
        <?php elseif ($tab === 'documents'): ?>
          <?php ws_field('Nom affiché', 'label', $values, 'text', true); ws_select('Type', 'resource_type', document_type_labels(), $values, 'document'); ws_field('Ordre', 'sort_order', $values, 'number'); ?>
          <?php if (!$edit): ?><label class="workspace-field">Fichier<input type="file" name="file" required accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip"></label><?php else: ?><a href="../<?= h($edit['path']) ?>" target="_blank" rel="noopener">Ouvrir <?= h($edit['original_name']) ?> ↗</a><?php endif; ?>
        <?php elseif ($tab === 'savoirs'): ?>
          <?php ws_select('Catégorie', 'category', ['Savoir'=>'Savoir','Savoir-faire'=>'Savoir-faire'], $values, 'Savoir'); ws_field('Texte', 'label', $values, 'textarea', true); ws_field('Ordre', 'sort_order', $values, 'number'); ?>
        <?php else: ?>
          <?php ws_field('Titre', 'title', $values, 'text', true); ?>
          <?php if ($tab === 'exercices'): ?>
            <?php ws_select('Section', 'category', ['ds'=>'Préparation DS','brevet'=>'Révision Brevet','bac'=>'Type Bac'], $values, 'ds'); ws_select('Difficulté', 'difficulty', ['facile'=>'Facile','normal'=>'Normal','difficile'=>'Difficile','défi'=>'Défi'], $values, 'normal'); ws_field('Énoncé', 'statement', $values, 'textarea', true); ws_field('Correction', 'correction', $values, 'textarea'); ws_field('Ordre', 'sort_order', $values, 'number'); ?>
          <?php elseif ($tab === 'videos'): ?>
            <?php ws_field('Lien vidéo', 'url', $values, 'url', true); ws_field('Description', 'description', $values, 'textarea'); ws_field('Ordre', 'sort_order', $values, 'number'); ?>
          <?php endif; ?>
          <?php if (in_array($tab, ['qcm','flashcards'], true)) ws_field('Ordre', 'sort_order', $values, 'number'); ?>
        <?php endif; ?>
        <?php if (in_array($tab, ['exercices','savoirs'], true)) latex_help_panel(); ?>
        <?php
          // Un nouveau contenu est publié par défaut : sans cela il restait en
          // brouillon et n'apparaissait jamais sur le site public.
          $creationContenu = $tab !== 'informations' && !$edit;
          $estPublie = $creationContenu ? true : (int)($values['is_published'] ?? 0) === 1;
        ?>
        <?php /* Champ caché + case : le formulaire envoie toujours une valeur
                 explicite (1 coché, 0 décoché), donc jamais de brouillon par
                 inadvertance. */ ?>
        <input type="hidden" name="is_published" value="0">
        <label class="workspace-checkbox"><input type="checkbox" name="is_published" value="1" <?= $estPublie ? 'checked' : '' ?>> Publié <span class="workspace-checkbox-hint">— visible sur le site</span></label>
        <div class="workspace-actions"><button class="btn-primary" type="submit">Enregistrer</button><?php if ($edit): ?><a class="btn-secondary" href="<?= h(ws_url()) ?>">Ajouter un autre contenu</a><?php endif; ?></div>
      </form>
    </section>
    <section class="workspace-list" aria-label="Contenus du chapitre">
      <?php if ($tab === 'informations'): ?>
        <div class="workspace-health"><div class="completion-ring" style="--progress:<?= (int)$health['score'] ?>%"><strong><?= (int)$health['score'] ?>%</strong><span>complet</span></div><div><h2>État du chapitre</h2><p><?= (int)$health['completed'] ?> critères prêts sur <?= (int)$health['total'] ?></p></div></div>
        <div class="workspace-checks"><?php foreach ($health['checks'] as $label => $complete): ?><span class="<?= $complete ? 'is-complete' : '' ?>"><?= $complete ? '✓' : '○' ?> <?= h($label) ?></span><?php endforeach; ?></div>
        <?php if ($health['alerts']): ?><details class="workspace-alerts"><summary><?= count($health['alerts']) ?> point(s) à vérifier</summary><ul><?php foreach ($health['alerts'] as $alert): ?><li><?= h($alert) ?></li><?php endforeach; ?></ul></details><?php endif; ?>
        <h2 class="mt-6">Contenus du chapitre</h2>
        <?php foreach ($types as $key => $type): $published = count(array_filter($all[$key], static function ($row) { return (int)$row['is_published'] === 1; })); ?><a class="workspace-overview-row" href="<?= h(ws_url(['tab'=>$key])) ?>"><strong><?= h($type['label']) ?></strong><span><?= count($all[$key]) ?> · <?= $published ?> publié(s)</span><span aria-hidden="true">→</span></a><?php endforeach; ?>
      <?php else: ?>
        <div class="workspace-list-heading"><h2><?= h($types[$tab]['label']) ?> <span class="muted">(<?= count($all[$tab]) ?>)</span></h2><span class="muted">Glisser ou utiliser les flèches pour réordonner</span></div>
        <form id="workspace-bulk" method="post" class="workspace-bulk"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="bulk_status"><label><input type="checkbox" data-select-all> Tout sélectionner</label><select name="bulk_value" aria-label="Action groupée"><option value="publish">Publier</option><option value="hide">Mettre en brouillon</option></select><button class="btn-secondary" type="submit">Appliquer</button></form>
        <form id="workspace-reorder" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="reorder"><input type="hidden" name="ordered_ids" data-ordered-ids></form>
        <div data-sortable-list>
        <?php foreach ($all[$tab] as $row): ?>
          <article class="workspace-resource <?= $editId === (int)$row['id'] ? 'is-selected' : '' ?>" data-sort-item data-id="<?= (int)$row['id'] ?>" draggable="true">
            <div class="workspace-resource-top"><label class="workspace-select"><input type="checkbox" name="selected[]" value="<?= (int)$row['id'] ?>" form="workspace-bulk" data-select-item><span class="sr-only">Sélectionner</span></label><span class="status-pill <?= (int)$row['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$row['is_published'] ? 'Publié' : 'Brouillon' ?></span><span class="workspace-order">Ordre <?= (int)$row['sort_order'] ?></span><button type="button" class="workspace-move" data-move="up" title="Monter" aria-label="Monter">↑</button><button type="button" class="workspace-move" data-move="down" title="Descendre" aria-label="Descendre">↓</button></div>
            <h3><?= h($row[$types[$tab]['title']] ?: ($row['original_name'] ?? 'Sans titre')) ?></h3>
            <div class="workspace-actions"><a class="btn-secondary" href="<?= h(ws_url(['edit'=>(int)$row['id']])) ?>">Modifier</a><form method="post"><?php ws_hidden('toggle', (int)$row['id']); ?><button class="btn-publish <?= $row['is_published'] ? 'is-hide' : 'is-publish' ?>" type="submit"><?= $row['is_published'] ? 'Masquer' : 'Publier' ?></button></form><form method="post" onsubmit="return confirm('<?= $tab === 'documents' ? 'Retirer ce document du chapitre ? Le fichier reste dans la médiathèque.' : 'Supprimer ce contenu et ses éléments associés ?' ?>')"><?php ws_hidden('delete', (int)$row['id']); ?><button class="btn-secondary" type="submit"><?= $tab === 'documents' ? 'Retirer' : 'Supprimer' ?></button></form></div>
          </article>
        <?php endforeach; ?>
        </div>
        <?php if (!$all[$tab]): ?><p class="chapter-empty">Aucun contenu pour le moment.</p><?php endif; ?>
      <?php endif; ?>
    </section>
  </div>
  <?php if ($edit && in_array($tab, ['qcm','flashcards'], true)): ?>
  <section class="workspace-children" id="questions">
    <h2><?= h($edit['title']) ?> · <?= $tab === 'qcm' ? 'Questions' : 'Cartes' ?> (<?= count($children) ?>)</h2>
    <div class="workspace-columns">
      <form method="post" enctype="multipart/form-data" class="workspace-form" data-workspace-form>
        <?php ws_hidden('save_child', $editId); ?><input type="hidden" name="child_id" value="<?= (int)($childEdit['id'] ?? 0) ?>">
        <h3><?= !empty($childEdit['id']) ? 'Modifier' : 'Ajouter' ?> <?= $tab === 'qcm' ? 'une question' : 'une carte' ?></h3>
        <?php if ($tab === 'qcm'): ?>
          <?php ws_field('Question', 'question_text', $childEdit, 'textarea', true);
          $answers = ['correct' => 'A'];
          foreach ($childEdit['choices'] ?? [] as $choice) { $answers['choice_' . $choice['choice_label']] = $choice['choice_text']; if ($choice['is_correct']) $answers['correct'] = $choice['choice_label']; }
          if ($error && ($_POST['action'] ?? '') === 'save_child') $answers = $_POST;
          // Deux réponses suffisent ; les quatre autres sont facultatives.
          foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $letter) {
              ws_field('Réponse ' . $letter . ($letter === 'A' || $letter === 'B' ? ' (obligatoire)' : ' (facultative)'), 'choice_' . $letter, $answers, 'textarea', false);
          }
          ws_select('Bonne réponse', 'correct', ['A'=>'A','B'=>'B','C'=>'C','D'=>'D','E'=>'E','F'=>'F'], $answers, 'A');
          ws_field('Explication', 'explanation', $childEdit, 'textarea'); ?>
        <?php else: ?>
          <?php ws_field('Recto', 'front_text', $childEdit, 'textarea', true); ws_field('Verso', 'back_text', $childEdit, 'textarea', true); ?>
        <?php endif; ?>
        <?php if ($tab === 'flashcards'): ?>
          <?php ws_image_field($childEdit, 'image', 'Image du recto'); ?>
          <?php ws_image_field($childEdit, 'image_back', 'Image du verso'); ?>
        <?php else: ?>
          <?php ws_image_field($childEdit, 'image', 'Image de la question'); ?>
        <?php endif; ?>
        <?php ws_field('Ordre', 'sort_order', $childEdit, 'number'); latex_help_panel(); ?>
        <div class="workspace-actions"><button class="btn-primary" type="submit">Enregistrer <?= $tab === 'qcm' ? 'la question' : 'la carte' ?></button><a class="btn-secondary" href="<?= h(ws_url(['edit'=>$editId])) ?>#questions">Nouvel élément</a></div>
      </form>
      <div class="workspace-child-list">
        <?php foreach ($children as $child): ?><?php
              $imageEnfant = (string)($child['image_path'] ?? '');
              $imageVerso = (string)($child['image_back_path'] ?? '');
              $numero = (int)$child['sort_order'];
              ?>
              <article class="admin-card<?= $tab === 'qcm' ? ' is-question' : '' ?>">
                <div class="admin-card-main">
                  <span class="admin-card-num" aria-hidden="true"><?= $numero ?></span>
                  <div class="admin-card-body">
                    <div class="admin-card-front"><?= render_text($child[$tab === 'qcm' ? 'question_text' : 'front_text']) ?></div>
                    <?php if ($imageEnfant !== ''): ?>
                      <img class="admin-card-thumb" src="../<?= h($imageEnfant) ?>" alt="" style="width:<?= max(10, min(100, (int)($child['image_width'] ?? 40))) ?>%">
                    <?php endif; ?>
                    <details class="admin-card-back">
                      <summary><?= $tab === 'qcm' ? 'Réponses et explication' : 'Voir le verso' ?></summary>
                      <?php if ($imageVerso !== ''): ?>
                        <img class="admin-card-thumb" src="../<?= h($imageVerso) ?>" alt="" style="width:<?= max(10, min(100, (int)($child['image_back_width'] ?? 40))) ?>%">
                      <?php endif; ?>
                      <div class="workspace-answer">
                        <?php if ($tab === 'qcm'): ?>
                          <?php foreach ($child['choices'] as $choice): ?>
                            <p class="<?= $choice['is_correct'] ? 'workspace-correct' : '' ?>"><?= h($choice['choice_label']) ?>. <?= render_text($choice['choice_text']) ?></p>
                          <?php endforeach; ?>
                          <?= render_text($child['explanation']) ?>
                        <?php else: ?>
                          <?= render_text($child['back_text']) ?>
                        <?php endif; ?>
                      </div>
                    </details>
                  </div>
                </div>
                <div class="admin-card-actions">
                  <a class="btn-secondary" href="<?= h(ws_url(['edit'=>$editId,'child'=>(int)$child['id']])) ?>#questions">Modifier</a>
                  <form method="post" onsubmit="return confirm('Supprimer cet élément ?')">
                    <?php ws_hidden('delete_child', $editId); ?>
                    <input type="hidden" name="child_id" value="<?= (int)$child['id'] ?>">
                    <button class="btn-delete" type="submit">Supprimer</button>
                  </form>
                </div>
              </article><?php endforeach; ?>
        <?php if (!$children): ?><p class="chapter-empty">Aucun élément dans cette série.</p><?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; endif; ?>
</main>
<script src="../assets/js/chapter-workspace.js?v=124" defer></script>
</body></html>
