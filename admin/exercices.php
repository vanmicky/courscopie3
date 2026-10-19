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
admin_ensure_v64_schema();

$message = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'save';
    try {
        if ($action === 'delete' && $id) {
            $stmt = $pdo->prepare('DELETE FROM exercises WHERE id=?');
            $stmt->execute([$id]);
            admin_flash('success', 'Exercice supprimé.');
            header('Location: exercices.php?deleted=1');
            exit;
        }
        $data = [
            'chapter_id' => (int)($_POST['chapter_id'] ?? 0),
            'title' => trim($_POST['title'] ?? ''),
            'statement' => trim($_POST['statement'] ?? ''),
            'correction' => trim($_POST['correction'] ?? ''),
            'difficulty' => trim($_POST['difficulty'] ?? 'normal') ?: 'normal',
            'category' => in_array($_POST['category'] ?? '', ['ds', 'brevet', 'bac'], true) ? $_POST['category'] : 'ds',
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];
        if ($id) {
            $data['id'] = $id;
            $stmt = $pdo->prepare('UPDATE exercises SET chapter_id=:chapter_id, title=:title, statement=:statement, correction=:correction, difficulty=:difficulty, category=:category, is_published=:is_published WHERE id=:id');
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare('INSERT INTO exercises(chapter_id,title,statement,correction,difficulty,category,is_published) VALUES(:chapter_id,:title,:statement,:correction,:difficulty,:category,:is_published)');
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
        }
        admin_flash('success', 'Exercice enregistré dans la bonne section.');
        header('Location: exercices.php?edit=' . $id . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM exercises WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$levels = $pdo->query('SELECT * FROM levels ORDER BY sort_order,id')->fetchAll();
$chapters = $pdo->query('SELECT c.*, l.short AS level_short FROM chapters c JOIN levels l ON l.id=c.level_id ORDER BY l.sort_order,c.number')->fetchAll();
$listFilters = [
    'includeDrafts' => true,
    'level_id' => (int)($_GET['level_id'] ?? 0),
    'status' => $_GET['status'] ?? '',
    'q' => trim($_GET['q'] ?? ''),
];
$rows = get_exercises($listFilters);

page_head('Admin - Exercices');
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">Exercices</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Créer des exercices avec énoncé, correction et LaTeX.</p>
  </section>
  <?php if ($message): ?><p class="mt-5 text-sm font-black text-green-700"><?= h($message) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="mt-5 text-sm font-black text-red-700"><?= h($error) ?></p><?php endif; ?>

  <section class="card mt-8 p-6">
    <h2 class="section-title"><?= $edit ? 'Modifier l’exercice' : 'Ajouter un exercice' ?></h2>
    <form method="post" class="work-filter-panel mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
      <div class="work-filter-grid">
        <label>Chapitre<select name="chapter_id" required><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= (int)($edit['chapter_id'] ?? 0)===(int)$chapter['id']?'selected':'' ?>><?= h($chapter['level_short'].' · Ch '.$chapter['number'].' · '.$chapter['title']) ?></option><?php endforeach; ?></select></label>
        <label>Titre<input name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
        <label>Difficulté<select name="difficulty"><?php foreach (['facile','normal','difficile','défi'] as $difficulty): ?><option value="<?= h($difficulty) ?>" <?= ($edit['difficulty'] ?? 'normal')===$difficulty?'selected':'' ?>><?= h($difficulty) ?></option><?php endforeach; ?></select></label>
        <label>Section<select name="category"><option value="ds" <?= ($edit['category'] ?? 'ds') === 'ds' ? 'selected' : '' ?>>Préparation au DS</option><option value="brevet" <?= ($edit['category'] ?? '') === 'brevet' ? 'selected' : '' ?>>Révision Brevet</option><option value="bac" <?= ($edit['category'] ?? '') === 'bac' ? 'selected' : '' ?>>Exercice type Bac</option></select></label>
      </div>
      <label class="mt-4 block font-bold">Énoncé avec LaTeX<textarea name="statement" rows="7" class="mt-2 w-full rounded-2xl border border-slate-200 p-4 dark:border-slate-700 dark:bg-slate-900" required><?= h($edit['statement'] ?? '') ?></textarea></label>
      <label class="mt-4 block font-bold">Correction<textarea name="correction" rows="7" class="mt-2 w-full rounded-2xl border border-slate-200 p-4 dark:border-slate-700 dark:bg-slate-900"><?= h($edit['correction'] ?? '') ?></textarea></label>
      <?php latex_help_panel(); ?>
      <div class="mt-4 flex flex-wrap items-center gap-3">
        <label><input type="checkbox" name="is_published" <?= !isset($edit) || (int)($edit['is_published'] ?? 1) === 1 ? 'checked' : '' ?>> Publié</label>
        <button class="btn-primary" type="submit">Enregistrer</button>
        <?php if ($edit): ?><a class="btn-secondary" href="exercices.php">Nouvel exercice</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="card mt-8 p-6">
    <div class="admin-list-header">
      <div><span class="badge">Liste</span><h2 class="section-title mt-3">Exercices existants</h2></div>
      <a class="btn-secondary" href="exercices.php">Réinitialiser</a>
    </div>
    <form method="get" class="work-filter-panel mt-5">
      <div class="work-filter-grid">
        <label>Niveau<select name="level_id"><option value="0">Tous</option><?php foreach ($levels as $level): ?><option value="<?= (int)$level['id'] ?>" <?= (int)$listFilters['level_id']===(int)$level['id']?'selected':'' ?>><?= h($level['short'].' — '.$level['name']) ?></option><?php endforeach; ?></select></label>
        <label>Statut<select name="status"><option value="">Tous</option><option value="1" <?= $listFilters['status']==='1'?'selected':'' ?>>Publié</option><option value="0" <?= $listFilters['status']==='0'?'selected':'' ?>>Brouillon</option></select></label>
        <label>Recherche<input type="search" name="q" value="<?= h($listFilters['q']) ?>" placeholder="Titre, chapitre..."></label>
        <div class="filter-submit"><button class="btn-primary" type="submit">Filtrer</button></div>
      </div>
    </form>
  </section>

  <section class="exercise-public-list mt-6">
    <?php foreach ($rows as $exercise): ?>
      <article class="exercise-public-card" style="--card-accent:<?= h($exercise['accent']) ?>">
        <span class="chapter-number"><?= h($exercise['level_short']) ?> · <?= h($exercise['chapter_title']) ?> · <?= h(['ds' => 'Préparation DS', 'brevet' => 'Brevet', 'bac' => 'Type Bac'][$exercise['category'] ?? 'ds'] ?? 'Préparation DS') ?> · <strong class="status-pill <?= (int)$exercise['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$exercise['is_published'] ? 'Publié' : 'Brouillon' ?></strong></span>
        <h2><?= h($exercise['title']) ?></h2>
        <div class="lesson-content mt-4"><?= render_text($exercise['statement']) ?></div>
        <?php if (trim((string)$exercise['correction']) !== ''): ?><details class="correction-box mt-4"><summary>Correction</summary><div class="mt-3"><?= render_text($exercise['correction']) ?></div></details><?php endif; ?>
        <div class="work-actions mt-4"><a href="exercices.php?edit=<?= (int)$exercise['id'] ?>">Modifier</a><a href="../exercices.php">Voir la liste</a><form method="post" onsubmit="return confirm('Supprimer cet exercice ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$exercise['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div>
      </article>
    <?php endforeach; ?>
    <?php if (!$rows): ?><p class="muted">Aucun exercice pour le moment.</p><?php endif; ?>
  </section>
</main>
</body>
</html>
