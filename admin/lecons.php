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
$lessonsSchemaReady = false;

try {
    $column = $pdo->query("SHOW COLUMNS FROM `lessons` LIKE 'kind'")->fetch();
    if (!$column) {
        $pdo->exec("ALTER TABLE `lessons` ADD COLUMN `kind` VARCHAR(80) NOT NULL DEFAULT 'Cours' AFTER `title`");
        $message = 'Base de données mise à niveau. Le champ Type est maintenant disponible.';
    }
    $lessonsSchemaReady = true;
} catch (Throwable $e) {
    $error = 'La base de données doit être mise à niveau. Importez database/migrate_v61_to_v62.sql dans phpMyAdmin, puis rechargez cette page.';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'save';
    try {
        if (!$lessonsSchemaReady) {
            throw new RuntimeException('Enregistrement impossible tant que la mise à niveau SQL V62 n’est pas appliquée.');
        }
        if ($action === 'delete' && $id) {
            $chapterId = (int)($_POST['chapter_id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM lessons WHERE id=?');
            $stmt->execute([$id]);
            header('Location: lecons.php?chapter_id=' . $chapterId);
            exit;
        }
        $data = [
            'chapter_id' => (int)($_POST['chapter_id'] ?? 0),
            'title' => trim($_POST['title'] ?? ''),
            'kind' => trim($_POST['kind'] ?? 'Cours') ?: 'Cours',
            'content' => trim($_POST['content'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];
        if ($id) {
            $data['id'] = $id;
            $stmt = $pdo->prepare('UPDATE lessons SET chapter_id=:chapter_id, title=:title, kind=:kind, content=:content, sort_order=:sort_order, is_published=:is_published WHERE id=:id');
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare('INSERT INTO lessons(chapter_id,title,kind,content,sort_order,is_published) VALUES(:chapter_id,:title,:kind,:content,:sort_order,:is_published)');
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: lecons.php?chapter_id=' . $data['chapter_id'] . '&edit=' . $id . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (!empty($_GET['saved'])) $message = 'Bloc enregistre.';
$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM lessons WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$chapters = $pdo->query('SELECT c.*, l.short AS level_short, l.accent FROM chapters c JOIN levels l ON l.id=c.level_id ORDER BY l.sort_order,c.number,c.id')->fetchAll();
$activeChapterId = (int)($_GET['chapter_id'] ?? ($edit['chapter_id'] ?? ($chapters[0]['id'] ?? 0)));
$activeChapter = null;
foreach ($chapters as $chapter) if ((int)$chapter['id'] === $activeChapterId) $activeChapter = $chapter;
$lessons = $activeChapterId ? get_lessons($activeChapterId, true) : [];
$files = $activeChapterId ? get_chapter_files($activeChapterId, true) : [];

page_head('Admin - Blocs de cours');
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">Blocs de cours</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Construire les chapitres avec des définitions, méthodes, exemples, activités et bilans.</p>
  </section>
  <?php if ($message): ?><p class="mt-5 text-sm font-black text-green-700"><?= h($message) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="mt-5 text-sm font-black text-red-700"><?= h($error) ?></p><?php endif; ?>

  <section class="card mt-8 p-6">
    <form method="get" class="work-filter-panel">
      <div class="work-filter-grid">
        <label>Chapitre<select name="chapter_id"><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= $activeChapterId===(int)$chapter['id']?'selected':'' ?>><?= h($chapter['level_short'].' · Ch '.$chapter['number'].' · '.$chapter['title']) ?></option><?php endforeach; ?></select></label>
        <div class="filter-submit"><button class="btn-primary" type="submit">Ouvrir</button></div>
      </div>
    </form>
  </section>

  <section class="card mt-8 p-6" style="--accent:<?= h($activeChapter['accent'] ?? '#2563eb') ?>">
    <h2 class="section-title"><?= $edit ? 'Modifier le bloc' : 'Ajouter un bloc' ?></h2>
    <form method="post" class="work-filter-panel mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
      <div class="work-filter-grid">
        <label>Chapitre<select name="chapter_id" required><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= (int)($edit['chapter_id'] ?? $activeChapterId)===(int)$chapter['id']?'selected':'' ?>><?= h($chapter['level_short'].' · Ch '.$chapter['number'].' · '.$chapter['title']) ?></option><?php endforeach; ?></select></label>
        <label>Type<select name="kind"><?php foreach (['Cours','Définition','Méthode','Exemple','Activité','Bilan','À retenir'] as $kind): ?><option value="<?= h($kind) ?>" <?= ($edit['kind'] ?? 'Cours')===$kind?'selected':'' ?>><?= h($kind) ?></option><?php endforeach; ?></select></label>
        <label>Titre<input name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
        <label>Ordre<input type="number" name="sort_order" value="<?= h($edit['sort_order'] ?? count($lessons) + 1) ?>"></label>
      </div>
      <label class="mt-4 block font-bold">Contenu avec LaTeX<textarea name="content" rows="10" class="mt-2 w-full rounded-2xl border border-slate-200 p-4 dark:border-slate-700 dark:bg-slate-900" required><?= h($edit['content'] ?? '') ?></textarea></label>
      <?php latex_help_panel(); ?>
      <div class="mt-4 flex flex-wrap items-center gap-3">
        <label><input type="checkbox" name="is_published" <?= !isset($edit) || (int)($edit['is_published'] ?? 1) === 1 ? 'checked' : '' ?>> Publié</label>
        <button class="btn-primary" type="submit">Enregistrer</button>
        <?php if ($edit): ?><a class="btn-secondary" href="lecons.php?chapter_id=<?= (int)$activeChapterId ?>">Nouveau bloc</a><?php endif; ?>
        <?php if ($activeChapter): ?><a class="btn-secondary" href="../chapitre.php?slug=<?= h($activeChapter['slug']) ?>">Aperçu public</a><?php endif; ?>
      </div>
    </form>
  </section>

  <?php if ($activeChapter): ?>
    <section class="card mt-8 p-6">
      <div class="admin-list-header"><div><span class="badge"><?= h($activeChapter['level_short']) ?></span><h2 class="section-title mt-3"><?= h($activeChapter['title']) ?></h2></div><a class="btn-secondary" href="fichiers.php?chapter_id=<?= (int)$activeChapterId ?>">Ajouter un fichier</a></div>
      <?php if ($files): ?><div class="file-chip-list mt-4"><?php foreach ($files as $file): ?><a class="file-chip" href="../<?= h($file['path']) ?>" target="_blank" rel="noopener"><?= h($file['label'] ?: $file['original_name']) ?></a><?php endforeach; ?></div><?php endif; ?>
    </section>
  <?php endif; ?>

  <section class="lesson-admin-list mt-8">
    <?php foreach ($lessons as $lesson): ?>
      <article class="lesson-block">
        <span class="lesson-kind"><?= h($lesson['kind'] ?? 'Cours') ?> · <strong class="status-pill <?= (int)$lesson['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$lesson['is_published'] ? 'Publié' : 'Brouillon' ?></strong></span>
        <h3><?= h($lesson['title']) ?></h3>
        <div class="lesson-content mt-3"><?= render_text($lesson['content']) ?></div>
        <div class="work-actions mt-4"><a href="lecons.php?chapter_id=<?= (int)$activeChapterId ?>&edit=<?= (int)$lesson['id'] ?>">Modifier</a><form method="post" onsubmit="return confirm('Supprimer ce bloc ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="chapter_id" value="<?= (int)$activeChapterId ?>"><input type="hidden" name="id" value="<?= (int)$lesson['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div>
      </article>
    <?php endforeach; ?>
    <?php if (!$lessons): ?><p class="muted">Aucun bloc pour ce chapitre.</p><?php endif; ?>
  </section>
</main>
</body>
</html>
