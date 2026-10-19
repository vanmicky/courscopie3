<?php
require_once __DIR__ . '/auth.php';
require_admin();
verify_csrf();

$pdo = db();
if (!$pdo) {
    page_head('Admin indisponible', '', ['math' => false]);
    echo '<main class="page-container py-8"><p>Base non configuree.</p></main></body></html>';
    exit;
}

$message = '';
$error = '';
$schema = admin_ensure_v64_schema();
if (!$schema['ok']) $error = $schema['message'];
$documentTypes = document_type_labels();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? 'upload';
    try {
        if (!$schema['ok']) throw new RuntimeException($schema['message']);
        if ($action === 'save_meta') {
            $id = (int)($_POST['id'] ?? 0);
            $type = array_key_exists($_POST['resource_type'] ?? '', $documentTypes) ? $_POST['resource_type'] : 'document';
            $stmt = $pdo->prepare('UPDATE files SET label=?,resource_type=?,level_id=?,chapter_id=? WHERE id=?');
            $stmt->execute([
                trim($_POST['label'] ?? '') ?: null,
                $type,
                ($_POST['level_id'] ?? '') !== '' ? (int)$_POST['level_id'] : null,
                ($_POST['chapter_id'] ?? '') !== '' ? (int)$_POST['chapter_id'] : null,
                $id,
            ]);
            $message = 'Document mis à jour.';
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT * FROM files WHERE id=?');
            $stmt->execute([$id]);
            $file = $stmt->fetch();
            if ($file) {
                $pdo->prepare('DELETE FROM files WHERE id=?')->execute([$id]);
                $absolute = dirname(__DIR__) . '/' . $file['path'];
                if (is_file($absolute)) unlink($absolute);
                $message = 'Fichier supprime.';
            }
        } elseif ($action === 'toggle') {
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare('UPDATE files SET is_published = 1 - is_published WHERE id=?')->execute([$id]);
            $message = 'Visibilite modifiee.';
        } else {
            $stored = store_uploaded_file($_FILES['file'] ?? [], [
                'level_id' => $_POST['level_id'] !== '' ? (int)$_POST['level_id'] : null,
                'chapter_id' => $_POST['chapter_id'] !== '' ? (int)$_POST['chapter_id'] : null,
                'label' => trim($_POST['label'] ?? '') ?: null,
                'resource_type' => array_key_exists($_POST['resource_type'] ?? '', $documentTypes) ? $_POST['resource_type'] : 'document',
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
            ]);
            $message = 'Fichier envoye : ' . $stored['name'];
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$levels = $pdo->query('SELECT * FROM levels ORDER BY sort_order,id')->fetchAll();
$chapters = $pdo->query('SELECT c.*, l.short AS level_short FROM chapters c JOIN levels l ON l.id=c.level_id ORDER BY l.sort_order,c.number')->fetchAll();
$activeChapterId = (int)($_GET['chapter_id'] ?? 0);
$files = get_files([]);

page_head('Admin - Documents', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>

  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">Documents</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Classer chaque fichier comme activité, correction, feuille d’exercices ou carte mentale, puis l’associer à un chapitre.</p>
  </section>

  <?php if ($message): ?><p class="mt-5 text-sm font-black text-green-700"><?= h($message) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="mt-5 text-sm font-black text-red-700"><?= h($error) ?></p><?php endif; ?>

  <section class="card mt-8 p-6">
    <h2 class="section-title">Ajouter un document</h2>
    <form method="post" enctype="multipart/form-data" class="work-filter-panel mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="upload">
      <div class="work-filter-grid">
        <label>Fichier<input type="file" name="file" required></label>
        <label>Nom affiche<input name="label" placeholder="Ex : Correction exercices"></label>
        <label>Type de document<select name="resource_type"><?php foreach ($documentTypes as $value => $label): ?><option value="<?= h($value) ?>"><?= h($label) ?></option><?php endforeach; ?></select></label>
        <label>Niveau
          <select name="level_id"><option value="">Aucun</option><?php foreach ($levels as $level): ?><option value="<?= (int)$level['id'] ?>"><?= h($level['short'] . ' — ' . $level['name']) ?></option><?php endforeach; ?></select>
        </label>
        <label>Chapitre
          <select name="chapter_id"><option value="">Aucun</option><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= $activeChapterId===(int)$chapter['id']?'selected':'' ?>><?= h($chapter['level_short'] . ' · Ch ' . $chapter['number'] . ' · ' . $chapter['title']) ?></option><?php endforeach; ?></select>
        </label>
        <label>Statut <span><input type="checkbox" name="is_published" checked> Publié</span></label>
        <div class="filter-submit"><button class="btn-primary" type="submit">Envoyer</button></div>
      </div>
      <p class="mt-4 text-sm muted">Formats autorisés : PDF, images, Word, PowerPoint, Excel, TXT, ZIP. Maximum 20 Mo.</p>
    </form>
  </section>

  <section class="mt-8">
    <div class="work-filter-panel document-table-filters" data-document-filters>
      <div class="work-filter-grid">
        <label>Rechercher<input type="search" data-table-search value="<?= h($_GET['q'] ?? '') ?>" placeholder="Nom, type, chapitre..."></label>
        <label>Niveau<select data-table-level><option value="">Tous les niveaux</option><?php foreach ($levels as $level): ?><option value="<?= h($level['short']) ?>"><?= h($level['short'] . ' — ' . $level['name']) ?></option><?php endforeach; ?></select></label>
        <label>Chapitre<select data-table-chapter><option value="">Tous les chapitres</option><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= $activeChapterId === (int)$chapter['id'] ? 'selected' : '' ?>><?= h($chapter['level_short'] . ' · Ch ' . $chapter['number'] . ' · ' . $chapter['title']) ?></option><?php endforeach; ?></select></label>
        <label>Type<select data-table-type><option value="">Tous les types</option><?php foreach ($documentTypes as $value => $label): ?><option value="<?= h($label) ?>"><?= h($label) ?></option><?php endforeach; ?></select></label>
        <label>Statut<select data-table-status><option value="">Tous</option><option value="Publié">Publié</option><option value="Masqué">Masqué</option></select></label>
      </div>
    </div>

    <div class="document-table-shell mt-6" data-document-table data-page-size="10">
      <div class="document-table-summary"><strong data-table-count aria-live="polite"><?= count($files) ?> document(s)</strong><span>10 lignes par page</span></div>
      <label class="document-sort-mobile">Trier les documents<select data-sort-mobile><?php foreach (['date'=>'Date','name'=>'Document','type'=>'Type','level'=>'Niveau','chapter'=>'Chapitre','status'=>'Statut'] as $key=>$label): ?><option value="<?= h($key) ?>:desc"><?= h($label) ?> · <?= $key === 'date' ? 'Plus récents' : 'Décroissant' ?></option><option value="<?= h($key) ?>:asc"><?= h($label) ?> · <?= $key === 'date' ? 'Plus anciens' : 'Croissant' ?></option><?php endforeach; ?></select></label>
      <div class="document-table-scroll"><table class="document-data-table">
        <thead><tr>
          <th><button type="button" data-sort="name">Document <span aria-hidden="true">↕</span></button></th>
          <th><button type="button" data-sort="type">Type <span aria-hidden="true">↕</span></button></th>
          <th><button type="button" data-sort="level">Niveau <span aria-hidden="true">↕</span></button></th>
          <th><button type="button" data-sort="chapter">Chapitre <span aria-hidden="true">↕</span></button></th>
          <th><button type="button" data-sort="date">Ajouté le <span aria-hidden="true">↕</span></button></th>
          <th><button type="button" data-sort="status">Statut <span aria-hidden="true">↕</span></button></th>
          <th>Actions</th>
        </tr></thead>
        <tbody>
      <?php foreach ($files as $file): ?>
        <?php $fileType = $documentTypes[$file['resource_type'] ?? 'document'] ?? 'Document'; $fileName = $file['label'] ?: $file['original_name']; $fileStatus = (int)$file['is_published'] ? 'Publié' : 'Masqué'; ?>
        <tr data-document-row data-name="<?= h(strtolower($fileName)) ?>" data-type="<?= h($fileType) ?>" data-level="<?= h($file['level_short'] ?: 'Sans niveau') ?>" data-chapter="<?= h($file['chapter_title'] ?: 'Sans chapitre') ?>" data-chapter-id="<?= (int)($file['chapter_id'] ?? 0) ?>" data-date="<?= h($file['created_at'] ?? '') ?>" data-status="<?= h($fileStatus) ?>">
          <td data-label="Document"><strong><?= h($fileName) ?></strong><small><?= h($file['original_name']) ?> · <?= number_format(((int)$file['size_bytes']) / 1024, 0, ',', ' ') ?> Ko</small></td>
          <td data-label="Type"><span class="document-type"><?= h($fileType) ?></span></td>
          <td data-label="Niveau"><?= h($file['level_short'] ?: '—') ?></td>
          <td data-label="Chapitre"><?= h($file['chapter_title'] ?: '—') ?></td>
          <td data-label="Ajouté le" data-date-value="<?= h($file['created_at'] ?? '') ?>"><?= !empty($file['created_at']) ? h(date('d-m-Y', strtotime($file['created_at']))) : '—' ?></td>
          <td data-label="Statut"><span class="status-pill <?= (int)$file['is_published'] ? 'is-live' : 'is-draft' ?>"><?= h($fileStatus) ?></span></td>
          <td data-label="Actions"><div class="document-row-actions"><a href="../<?= h($file['path']) ?>" target="_blank" rel="noopener">Ouvrir</a><button type="button" data-edit-document="<?= (int)$file['id'] ?>" aria-expanded="false">Modifier</button></div></td>
        </tr>
        <tr class="document-edit-row" data-edit-row="<?= (int)$file['id'] ?>" hidden><td colspan="7"><form method="post" class="document-meta-form">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$file['id'] ?>"><input type="hidden" name="action" value="save_meta">
            <label>Nom affiché<input name="label" value="<?= h($file['label'] ?? '') ?>"></label>
            <label>Type<select name="resource_type"><?php foreach ($documentTypes as $value => $label): ?><option value="<?= h($value) ?>" <?= ($file['resource_type'] ?? 'document') === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label>
            <label>Niveau<select name="level_id"><option value="">Aucun</option><?php foreach ($levels as $level): ?><option value="<?= (int)$level['id'] ?>" <?= (int)($file['level_id'] ?? 0) === (int)$level['id'] ? 'selected' : '' ?>><?= h($level['short']) ?></option><?php endforeach; ?></select></label>
            <label>Chapitre<select name="chapter_id"><option value="">Aucun</option><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= (int)($file['chapter_id'] ?? 0) === (int)$chapter['id'] ? 'selected' : '' ?>><?= h($chapter['level_short'] . ' · Ch ' . $chapter['number'] . ' · ' . $chapter['title']) ?></option><?php endforeach; ?></select></label>
            <button class="btn-primary" type="submit">Enregistrer les informations</button>
          </form><div class="work-actions mt-4">
            <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$file['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="btn-secondary" type="submit"><?= (int)$file['is_published'] ? 'Masquer' : 'Publier' ?></button></form>
            <form method="post" onsubmit="return confirm('Supprimer ce fichier ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$file['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn-secondary" type="submit">Supprimer</button></form>
          </div></td></tr>
      <?php endforeach; ?>
        </tbody>
      </table></div>
      <p class="document-table-empty" data-table-empty hidden>Aucun document ne correspond aux filtres.</p>
      <nav class="document-pagination" aria-label="Pagination des documents"><button type="button" data-page-prev>Précédent</button><span data-page-label>Page 1</span><button type="button" data-page-next>Suivant</button></nav>
    </div>
  </section>
</main>
<script src="../assets/js/document-table.js" defer></script>
</body>
</html>
