<?php
require_once __DIR__ . '/auth.php';
require_admin();
verify_csrf();

$pdo = db();
if (!$pdo) {
    page_head('Admin indisponible');
    echo '<main class="page-container py-8"><p>Base non configuree.</p></main></body></html>';
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete' && $id) {
        $stmt = $pdo->prepare('DELETE FROM assignments WHERE id=?');
        $stmt->execute([$id]);
        header('Location: assignments.php');
        exit;
    }

    try {
        $data = [
            'level_id' => (int)$_POST['level_id'],
            'subject_id' => $_POST['subject_id'] !== '' ? (int)$_POST['subject_id'] : null,
            'chapter_id' => $_POST['chapter_id'] !== '' ? (int)$_POST['chapter_id'] : null,
            'title' => trim($_POST['title']),
            'kind' => trim($_POST['kind'] ?: 'Travail'),
            'instructions' => trim($_POST['instructions']),
            'due_date' => $_POST['due_date'] ?: null,
            'priority' => $_POST['priority'] ?: 'normal',
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        if ($id) {
            $data['id'] = $id;
            $stmt = $pdo->prepare('UPDATE assignments SET level_id=:level_id, subject_id=:subject_id, chapter_id=:chapter_id, title=:title, kind=:kind, instructions=:instructions, due_date=:due_date, priority=:priority, is_published=:is_published WHERE id=:id');
            $stmt->execute($data);
            $assignmentId = $id;
        } else {
            $stmt = $pdo->prepare('INSERT INTO assignments(level_id,subject_id,chapter_id,title,kind,instructions,due_date,priority,is_published) VALUES(:level_id,:subject_id,:chapter_id,:title,:kind,:instructions,:due_date,:priority,:is_published)');
            $stmt->execute($data);
            $assignmentId = (int)$pdo->lastInsertId();
        }

        $pdo->prepare('DELETE FROM assignment_files WHERE assignment_id=?')->execute([$assignmentId]);
        foreach ($_POST['file_ids'] ?? [] as $fileId) {
            $fileId = (int)$fileId;
            if ($fileId > 0) {
                $stmt = $pdo->prepare('INSERT INTO assignment_files(assignment_id,file_id,label) VALUES(?,?,NULL)');
                $stmt->execute([$assignmentId, $fileId]);
            }
        }

        if (!empty($_FILES['new_file']) && (int)$_FILES['new_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $stored = store_uploaded_file($_FILES['new_file'], [
                'level_id' => $data['level_id'],
                'chapter_id' => $data['chapter_id'],
                'label' => trim($_POST['new_file_label'] ?? '') ?: null,
                'is_published' => 1,
            ]);
            $stmt = $pdo->prepare('INSERT INTO assignment_files(assignment_id,file_id,label) VALUES(?,?,?)');
            $stmt->execute([$assignmentId, $stored['id'], trim($_POST['new_file_label'] ?? '') ?: null]);
        }

        $pdo->prepare('DELETE FROM assignment_links WHERE assignment_id=?')->execute([$assignmentId]);
        $linkLabel = trim($_POST['link_label'] ?? '');
        $linkUrl = trim($_POST['link_url'] ?? '');
        if ($linkLabel !== '' && filter_var($linkUrl, FILTER_VALIDATE_URL)) {
            $stmt = $pdo->prepare('INSERT INTO assignment_links(assignment_id,label,url) VALUES(?,?,?)');
            $stmt->execute([$assignmentId, $linkLabel, $linkUrl]);
        }

        header('Location: assignments.php?edit=' . $assignmentId . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}


$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM assignments WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$linkedFileIds = [];
$linkedFiles = [];
$linkedLinks = [];
if ($edit) {
    $linkedFiles = get_assignment_files((int)$edit['id'], true);
    $linkedLinks = get_assignment_links((int)$edit['id']);
    $linkedFileIds = array_map(fn($file) => (int)$file['id'], $linkedFiles);
}

$levels = $pdo->query('SELECT * FROM levels ORDER BY sort_order,id')->fetchAll();
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY name')->fetchAll();
$chapters = $pdo->query('SELECT c.*, l.short AS level_short FROM chapters c JOIN levels l ON l.id=c.level_id ORDER BY l.sort_order,c.number')->fetchAll();
$files = get_files();
$rows = $pdo->query('SELECT a.*, l.short AS level_short, c.title AS chapter_title FROM assignments a JOIN levels l ON l.id=a.level_id LEFT JOIN chapters c ON c.id=a.chapter_id ORDER BY a.due_date DESC,a.id DESC')->fetchAll();

page_head('Admin - Travail à faire');
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="card mt-5 p-6">
    <span class="badge">Travail à faire</span>
    <h1 class="page-title mt-4"><?= $edit ? 'Modifier' : 'Ajouter' ?> un travail</h1>
    <?php if ($message): ?><p class="mt-4 text-sm font-black text-green-700"><?= h($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="mt-4 text-sm font-black text-red-700"><?= h($error) ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="work-filter-panel mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
      <div class="work-filter-grid">
        <label>Niveau
          <select name="level_id" required><?php foreach($levels as $l): ?><option value="<?= (int)$l['id'] ?>" <?= (int)($edit['level_id'] ?? 0)===(int)$l['id']?'selected':'' ?>><?= h($l['short'].' — '.$l['name']) ?></option><?php endforeach; ?></select>
        </label>
        <label>Matière
          <select name="subject_id"><option value="">Auto</option><?php foreach($subjects as $s): ?><option value="<?= (int)$s['id'] ?>" <?= (int)($edit['subject_id'] ?? 0)===(int)$s['id']?'selected':'' ?>><?= h($s['name']) ?></option><?php endforeach; ?></select>
        </label>
        <label>Chapitre
          <select name="chapter_id"><option value="">Sans chapitre</option><?php foreach($chapters as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)($edit['chapter_id'] ?? 0)===(int)$c['id']?'selected':'' ?>><?= h($c['level_short'].' · Ch '.$c['number'].' · '.$c['title']) ?></option><?php endforeach; ?></select>
        </label>
        <label>Titre<input name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
        <label>Type<input name="kind" value="<?= h($edit['kind'] ?? 'Travail') ?>"></label>
        <label>Date<input name="due_date" type="date" value="<?= h($edit['due_date'] ?? '') ?>"></label>
      </div>

      <label class="mt-4 block font-bold">Consignes avec LaTeX
        <textarea name="instructions" rows="6" class="mt-2 w-full rounded-2xl border border-slate-200 p-4 dark:border-slate-700 dark:bg-slate-900" placeholder="Exemple : Résoudre \(2x+5=17\)"><?= h($edit['instructions'] ?? '') ?></textarea>
      </label>

      <div class="mt-5 grid gap-4 lg:grid-cols-2">
        <section class="admin-file-box">
          <h2 class="text-lg font-black">Joindre un fichier</h2>
          <label class="mt-3 block font-bold">Nouveau fichier
            <input type="file" name="new_file" class="mt-2 w-full rounded-2xl border border-slate-200 p-3 dark:border-slate-700 dark:bg-slate-900">
          </label>
          <label class="mt-3 block font-bold">Nom affiche
            <input name="new_file_label" class="mt-2 w-full rounded-2xl border border-slate-200 p-3 dark:border-slate-700 dark:bg-slate-900" placeholder="Ex : Correction PDF">
          </label>
          <p class="mt-3 text-sm muted">Formats : PDF, images, documents Office, TXT, ZIP. Maximum 20 Mo.</p>
        </section>

        <section class="admin-file-box">
          <h2 class="text-lg font-black">Fichier déjà envoyé</h2>
          <div class="admin-check-list mt-3">
            <?php foreach ($files as $file): ?>
              <label><input type="checkbox" name="file_ids[]" value="<?= (int)$file['id'] ?>" <?= in_array((int)$file['id'], $linkedFileIds, true) ? 'checked' : '' ?>> <?= h($file['label'] ?: $file['original_name']) ?></label>
            <?php endforeach; ?>
            <?php if (!$files): ?><p class="muted">Aucun fichier pour le moment.</p><?php endif; ?>
          </div>
        </section>
      </div>

      <section class="admin-file-box mt-4">
        <h2 class="text-lg font-black">Ajouter un lien</h2>
        <?php $firstLink = $linkedLinks[0] ?? null; ?>
        <div class="work-filter-grid mt-3">
          <label>Libellé<input name="link_label" value="<?= h($firstLink['label'] ?? '') ?>" placeholder="Ex : Vidéo de révision"></label>
          <label>URL<input name="link_url" value="<?= h($firstLink['url'] ?? '') ?>" placeholder="https://..."></label>
        </div>
      </section>

      <div class="mt-4 flex flex-wrap items-center gap-3">
        <label><input type="checkbox" name="is_published" <?= !isset($edit) || ($edit['is_published'] ?? 1) ? 'checked' : '' ?>> Publié</label>
        <input type="hidden" name="priority" value="normal">
        <button class="btn-primary" type="submit">Enregistrer</button>
        <?php if ($edit): ?><a class="btn-secondary" href="assignments.php">Nouveau travail</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="work-board mt-8">
    <?php foreach($rows as $r): ?>
      <?php $rowFiles = get_assignment_files((int)$r['id'], true); $rowLinks = get_assignment_links((int)$r['id']); ?>
      <article class="work-card"><time class="work-date-badge" datetime="<?= h($r['due_date'] ?? '') ?>"><?= h(format_date_fr($r['due_date'] ?? null)) ?></time><div><span class="work-level-badge"><?= h($r['level_short']) ?></span><h2 class="mt-3 text-xl font-black"><?= h($r['title']) ?></h2><div class="work-bullets mt-2"><?= render_text($r['instructions']) ?></div><?php if ($rowFiles || $rowLinks): ?><div class="file-chip-list mt-4"><?php foreach ($rowFiles as $file): ?><a class="file-chip" href="../<?= h($file['path']) ?>" target="_blank" rel="noopener"><?= h($file['assignment_label'] ?: $file['label'] ?: $file['original_name']) ?></a><?php endforeach; ?><?php foreach ($rowLinks as $link): ?><a class="file-chip" href="<?= h($link['url']) ?>" target="_blank" rel="noopener"><?= h($link['label']) ?></a><?php endforeach; ?></div><?php endif; ?></div><div class="work-actions"><a href="assignments.php?edit=<?= (int)$r['id'] ?>">Modifier</a><form method="post" onsubmit="return confirm('Supprimer ce travail ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn-secondary" type="submit">Supprimer</button></form></div></article>
    <?php endforeach; ?>
  </section>
</main>
</body>
</html>
