<?php
require_once __DIR__ . '/auth.php';
require_admin();
verify_csrf();

$pdo = db();
$schema = admin_ensure_v64_schema();
$error = $schema['ok'] ? '' : $schema['message'];

if (!$pdo) {
    page_head('Admin indisponible');
    echo '<main class="page-container py-8"><p>Base non configurée.</p></main></body></html>';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $schema['ok']) {
    $action = $_POST['action'] ?? '';
    $chapterId = (int)($_POST['chapter_id'] ?? 0);
    try {
        if ($action === 'delete_video') {
            $pdo->prepare('DELETE FROM chapter_videos WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
            admin_flash('success', 'Vidéo supprimée.');
            header('Location: ressources-chapitre.php?chapter_id=' . $chapterId);
            exit;
        }
        if ($action === 'delete_skill') {
            $pdo->prepare('DELETE FROM chapter_skills WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
            admin_flash('success', 'Élément supprimé.');
            header('Location: ressources-chapitre.php?chapter_id=' . $chapterId);
            exit;
        }
        if ($action === 'save_video') {
            $id = (int)($_POST['id'] ?? 0);
            $url = trim($_POST['url'] ?? '');
            if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('~^https?://~i', $url)) {
                throw new RuntimeException('Saisissez une adresse vidéo complète commençant par https://.');
            }
            $data = [
                'chapter_id' => $chapterId,
                'title' => trim($_POST['title'] ?? ''),
                'url' => $url,
                'description' => trim($_POST['description'] ?? ''),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
            ];
            if ($id) {
                $data['id'] = $id;
                $stmt = $pdo->prepare('UPDATE chapter_videos SET chapter_id=:chapter_id,title=:title,url=:url,description=:description,sort_order=:sort_order,is_published=:is_published WHERE id=:id');
            } else {
                $stmt = $pdo->prepare('INSERT INTO chapter_videos(chapter_id,title,url,description,sort_order,is_published) VALUES(:chapter_id,:title,:url,:description,:sort_order,:is_published)');
            }
            $stmt->execute($data);
            admin_flash('success', 'Vidéo enregistrée.');
            header('Location: ressources-chapitre.php?chapter_id=' . $chapterId . '&saved=1');
            exit;
        }
        if ($action === 'save_skill') {
            $id = (int)($_POST['id'] ?? 0);
            $category = in_array($_POST['category'] ?? '', ['Savoir', 'Savoir-faire'], true) ? $_POST['category'] : 'Savoir';
            $data = [
                'chapter_id' => $chapterId,
                'category' => $category,
                'label' => trim($_POST['label'] ?? ''),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
            ];
            if ($data['label'] === '') throw new RuntimeException('Le texte du savoir est obligatoire.');
            if ($id) {
                $data['id'] = $id;
                $stmt = $pdo->prepare('UPDATE chapter_skills SET chapter_id=:chapter_id,category=:category,label=:label,sort_order=:sort_order,is_published=:is_published WHERE id=:id');
            } else {
                $stmt = $pdo->prepare('INSERT INTO chapter_skills(chapter_id,category,label,sort_order,is_published) VALUES(:chapter_id,:category,:label,:sort_order,:is_published)');
            }
            $stmt->execute($data);
            admin_flash('success', 'Savoir ou savoir-faire enregistré.');
            header('Location: ressources-chapitre.php?chapter_id=' . $chapterId . '&saved=1');
            exit;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$chapters = $pdo->query('SELECT c.*,l.short AS level_short,l.accent FROM chapters c JOIN levels l ON l.id=c.level_id ORDER BY l.sort_order,c.number,c.id')->fetchAll();
$activeChapterId = (int)($_GET['chapter_id'] ?? ($chapters[0]['id'] ?? 0));
$activeChapter = null;
foreach ($chapters as $chapter) if ((int)$chapter['id'] === $activeChapterId) $activeChapter = $chapter;

$videoEdit = null;
if ($schema['ok'] && !empty($_GET['video'])) {
    $stmt = $pdo->prepare('SELECT * FROM chapter_videos WHERE id=?');
    $stmt->execute([(int)$_GET['video']]);
    $videoEdit = $stmt->fetch() ?: null;
}
$skillEdit = null;
if ($schema['ok'] && !empty($_GET['skill'])) {
    $stmt = $pdo->prepare('SELECT * FROM chapter_skills WHERE id=?');
    $stmt->execute([(int)$_GET['skill']]);
    $skillEdit = $stmt->fetch() ?: null;
}
$videos = $schema['ok'] && $activeChapterId ? get_chapter_videos($activeChapterId, true) : [];
$skills = $schema['ok'] && $activeChapterId ? get_chapter_skills($activeChapterId, true) : [];

page_head('Admin - Vidéos et savoirs');
admin_nav();
?>
<main class="page-container py-8" style="--accent:<?= h($activeChapter['accent'] ?? '#2563eb') ?>">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Ressources de chapitre</span>
    <h1 class="page-title mt-4">Vidéos et savoirs</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Ajouter les vidéos à regarder et la liste des connaissances que les élèves pourront cocher.</p>
  </section>
  <?php if ($error): ?><div class="admin-inline-notice is-error mt-5" role="alert"><strong>Enregistrement impossible</strong><span><?= h($error) ?></span></div><?php endif; ?>

  <section class="card mt-8 p-6">
    <form method="get" class="work-filter-panel">
      <div class="work-filter-grid">
        <label>Chapitre<select name="chapter_id"><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= $activeChapterId === (int)$chapter['id'] ? 'selected' : '' ?>><?= h($chapter['level_short'] . ' · Chapitre ' . $chapter['number'] . ' · ' . $chapter['title']) ?></option><?php endforeach; ?></select></label>
        <div class="filter-submit"><button class="btn-primary" type="submit">Ouvrir</button></div>
      </div>
    </form>
  </section>

  <?php if ($activeChapter && $schema['ok']): ?>
    <div class="chapter-admin-columns mt-8">
      <section class="card p-6">
        <h2 class="section-title"><?= $videoEdit ? 'Modifier la vidéo' : 'Ajouter une vidéo' ?></h2>
        <form method="post" class="work-filter-panel mt-5">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="save_video"><input type="hidden" name="id" value="<?= h($videoEdit['id'] ?? '') ?>"><input type="hidden" name="chapter_id" value="<?= (int)$activeChapterId ?>">
          <label>Titre<input name="title" value="<?= h($videoEdit['title'] ?? '') ?>" required></label>
          <label class="mt-4">Lien YouTube, Vimeo ou vidéo<input name="url" type="url" value="<?= h($videoEdit['url'] ?? '') ?>" placeholder="https://..." required></label>
          <label class="mt-4">Description<textarea name="description" rows="3"><?= h($videoEdit['description'] ?? '') ?></textarea></label>
          <div class="work-filter-grid mt-4"><label>Ordre<input type="number" name="sort_order" value="<?= h($videoEdit['sort_order'] ?? count($videos) + 1) ?>"></label><label>Statut<span><input type="checkbox" name="is_published" <?= !$videoEdit || (int)$videoEdit['is_published'] === 1 ? 'checked' : '' ?>> Publiée</span></label></div>
          <div class="mt-4 flex flex-wrap gap-3"><button class="btn-primary" type="submit">Enregistrer la vidéo</button><?php if ($videoEdit): ?><a class="btn-secondary" href="ressources-chapitre.php?chapter_id=<?= (int)$activeChapterId ?>">Annuler</a><?php endif; ?></div>
        </form>
      </section>

      <section class="card p-6">
        <h2 class="section-title"><?= $skillEdit ? 'Modifier l’élément' : 'Ajouter un savoir' ?></h2>
        <form method="post" class="work-filter-panel mt-5">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="save_skill"><input type="hidden" name="id" value="<?= h($skillEdit['id'] ?? '') ?>"><input type="hidden" name="chapter_id" value="<?= (int)$activeChapterId ?>">
          <label>Catégorie<select name="category"><option value="Savoir" <?= ($skillEdit['category'] ?? '') === 'Savoir' ? 'selected' : '' ?>>Savoir</option><option value="Savoir-faire" <?= ($skillEdit['category'] ?? '') === 'Savoir-faire' ? 'selected' : '' ?>>Savoir-faire</option></select></label>
          <label class="mt-4">Texte avec LaTeX<textarea name="label" rows="4" required><?= h($skillEdit['label'] ?? '') ?></textarea></label>
          <?php latex_help_panel(); ?>
          <div class="work-filter-grid mt-4"><label>Ordre<input type="number" name="sort_order" value="<?= h($skillEdit['sort_order'] ?? count($skills) + 1) ?>"></label><label>Statut<span><input type="checkbox" name="is_published" <?= !$skillEdit || (int)$skillEdit['is_published'] === 1 ? 'checked' : '' ?>> Publié</span></label></div>
          <div class="mt-4 flex flex-wrap gap-3"><button class="btn-primary" type="submit">Enregistrer l’élément</button><?php if ($skillEdit): ?><a class="btn-secondary" href="ressources-chapitre.php?chapter_id=<?= (int)$activeChapterId ?>">Annuler</a><?php endif; ?></div>
        </form>
      </section>
    </div>

    <section class="card mt-8 p-6">
      <h2 class="section-title">Vidéos enregistrées</h2>
      <div class="admin-resource-list mt-5"><?php foreach ($videos as $video): ?><article><div><span class="status-pill <?= (int)$video['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$video['is_published'] ? 'Publiée' : 'Brouillon' ?></span><h3><?= h($video['title']) ?></h3><p><?= h($video['url']) ?></p></div><div class="work-actions"><a href="ressources-chapitre.php?chapter_id=<?= (int)$activeChapterId ?>&video=<?= (int)$video['id'] ?>">Modifier</a><form method="post" onsubmit="return confirm('Supprimer cette vidéo ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete_video"><input type="hidden" name="chapter_id" value="<?= (int)$activeChapterId ?>"><input type="hidden" name="id" value="<?= (int)$video['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div></article><?php endforeach; ?><?php if (!$videos): ?><p class="muted">Aucune vidéo.</p><?php endif; ?></div>
    </section>

    <section class="card mt-8 p-6">
      <h2 class="section-title">Savoirs et savoir-faire enregistrés</h2>
      <div class="admin-resource-list mt-5"><?php foreach ($skills as $skill): ?><article><div><span class="status-pill <?= (int)$skill['is_published'] ? 'is-live' : 'is-draft' ?>"><?= h($skill['category']) ?> · <?= ((int)$skill['is_published'] === 1) ? 'Publié' : 'Brouillon' ?></span><div class="mt-3"><?= render_text($skill['label']) ?></div></div><div class="work-actions"><a href="ressources-chapitre.php?chapter_id=<?= (int)$activeChapterId ?>&skill=<?= (int)$skill['id'] ?>">Modifier</a><form method="post" onsubmit="return confirm('Supprimer cet élément ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete_skill"><input type="hidden" name="chapter_id" value="<?= (int)$activeChapterId ?>"><input type="hidden" name="id" value="<?= (int)$skill['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div></article><?php endforeach; ?><?php if (!$skills): ?><p class="muted">Aucun savoir ou savoir-faire.</p><?php endif; ?></div>
    </section>
  <?php endif; ?>
</main>
</body>
</html>
