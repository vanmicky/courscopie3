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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'save';
    try {
        if ($action === 'delete' && $id) {
            $stmt = $pdo->prepare('DELETE FROM levels WHERE id=?');
            $stmt->execute([$id]);
            header('Location: niveaux.php');
            exit;
        }
        $title = trim($_POST['name'] ?? '');
        $image = trim($_POST['current_image'] ?? '');
        $uploaded = store_site_image($_FILES['image'] ?? [], 'levels');
        if ($uploaded) $image = $uploaded;
        $data = [
            'slug' => trim($_POST['slug'] ?? '') ?: slugify_text($title, 'niveau'),
            'short' => trim($_POST['short'] ?? ''),
            'name' => $title,
            'subject' => trim($_POST['subject'] ?? ''),
            'accent' => trim($_POST['accent'] ?? '#2563eb') ?: '#2563eb',
            'image' => $image ?: null,
            'alt_text' => trim($_POST['alt_text'] ?? ''),
            'summary' => trim($_POST['summary'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];
        if ($id) {
            $data['id'] = $id;
            $stmt = $pdo->prepare('UPDATE levels SET slug=:slug, short=:short, name=:name, subject=:subject, accent=:accent, image=:image, alt_text=:alt_text, summary=:summary, sort_order=:sort_order, is_published=:is_published WHERE id=:id');
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare('INSERT INTO levels(slug,short,name,subject,accent,image,alt_text,summary,sort_order,is_published) VALUES(:slug,:short,:name,:subject,:accent,:image,:alt_text,:summary,:sort_order,:is_published)');
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: niveaux.php?edit=' . $id . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM levels WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$levels = $pdo->query('SELECT * FROM levels ORDER BY sort_order,id')->fetchAll();
$subjects = get_subjects(true);

page_head('Admin - Niveaux', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">Niveaux</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Créer les classes, choisir leur couleur, leur image et leur ordre d’affichage.</p>
  </section>
  <?php if ($message): ?><p class="mt-5 text-sm font-black text-green-700"><?= h($message) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="mt-5 text-sm font-black text-red-700"><?= h($error) ?></p><?php endif; ?>

  <section class="card mt-8 p-6">
    <h2 class="section-title"><?= $edit ? 'Modifier le niveau' : 'Ajouter un niveau' ?></h2>
    <form method="post" enctype="multipart/form-data" class="work-filter-panel mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
      <input type="hidden" name="current_image" value="<?= h($edit['image'] ?? '') ?>">
      <div class="work-filter-grid">
        <label>Nom<input name="name" required value="<?= h($edit['name'] ?? '') ?>" placeholder="Ex : Seconde Physique-Chimie"></label>
        <label>Nom court<input name="short" required value="<?= h($edit['short'] ?? '') ?>" placeholder="Ex : 2de PC"></label>
        <label>Slug<input name="slug" value="<?= h($edit['slug'] ?? '') ?>" placeholder="auto si vide"></label>
        <label>Discipline<select name="subject"><option value="">Libre</option><?php $currentSubject = $edit['subject'] ?? ''; $foundSubject = false; foreach ($subjects as $subject): $foundSubject = $foundSubject || $currentSubject === $subject['name']; ?><option value="<?= h($subject['name']) ?>" <?= $currentSubject===$subject['name']?'selected':'' ?>><?= h($subject['name']) ?></option><?php endforeach; ?><?php if ($currentSubject !== '' && !$foundSubject): ?><option value="<?= h($currentSubject) ?>" selected><?= h($currentSubject) ?></option><?php endif; ?></select></label>
        <label>Couleur<input type="color" name="accent" value="<?= h($edit['accent'] ?? '#2563eb') ?>"></label>
        <label>Ordre<input type="number" name="sort_order" value="<?= h($edit['sort_order'] ?? '0') ?>"></label>
      </div>
      <label class="mt-4 block font-bold">Résumé<textarea name="summary" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 p-4 dark:border-slate-700 dark:bg-slate-900"><?= h($edit['summary'] ?? '') ?></textarea></label>
      <section class="admin-file-box mt-4">
        <h3 class="text-lg font-black">Image du niveau</h3>
        <?php if (!empty($edit['image'])): ?><img class="admin-preview-image mt-3" src="../<?= h($edit['image']) ?>" alt="Image actuelle"><?php endif; ?>
        <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp,.gif,image/*" class="mt-3 w-full rounded-2xl border border-slate-200 p-3 dark:border-slate-700 dark:bg-slate-900">
        <label class="mt-3 block font-bold">Description de l’image<input name="alt_text" value="<?= h($edit['alt_text'] ?? '') ?>" placeholder="Ex : Élèves étudiant la physique en classe"></label>
        <p class="mt-3 text-sm muted">PNG, JPG, WEBP ou GIF. Maximum 5 Mo.</p>
      </section>
      <div class="mt-4 flex flex-wrap items-center gap-3">
        <label><input type="checkbox" name="is_published" <?= !isset($edit) || (int)($edit['is_published'] ?? 1) === 1 ? 'checked' : '' ?>> Publié</label>
        <button class="btn-primary" type="submit">Enregistrer</button>
        <?php if ($edit): ?><a class="btn-secondary" href="niveaux.php">Nouveau niveau</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="chapter-grid mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($levels as $level): ?>
      <article class="chapter-tile level-admin-card" data-card style="--card-accent:<?= h($level['accent']) ?>">
        <?php if (!empty($level['image'])): ?><img src="../<?= h($level['image']) ?>" alt="<?= h($level['alt_text'] ?: $level['name']) ?>"><?php else: ?><div class="admin-image-placeholder"><?= h($level['short']) ?></div><?php endif; ?>
        <span class="chapter-number"><?= h($level['short']) ?> · <strong class="status-pill <?= (int)$level['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$level['is_published'] ? 'Publié' : 'Brouillon' ?></strong></span>
        <h3><?= h($level['name']) ?></h3>
        <p><?= h($level['subject']) ?> · ordre <?= (int)$level['sort_order'] ?></p>
        <div class="chapter-actions"><a href="niveaux.php?edit=<?= (int)$level['id'] ?>">Modifier</a><a href="../niveau.php?slug=<?= h($level['slug']) ?>">Voir</a><form method="post" onsubmit="return confirm('Supprimer ce niveau et ses contenus lies ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$level['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
</body>
</html>
