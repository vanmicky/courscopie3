<?php
require_once __DIR__ . '/auth.php';
require_admin();
verify_csrf();
require_once __DIR__ . '/chapter-insights.php';

$pdo = db();
if (!$pdo) {
    page_head('Admin indisponible', '', ['math' => false]);
    echo '<main class="page-container py-8"><p>Base non configuree.</p></main></body></html>';
    exit;
}
$v64Schema = admin_ensure_v69_schema();

function slugify_admin(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text ?? '', '-') ?: 'chapitre';
}
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'save';
    try {

    if ($action === 'toggle' && $id) {
        $pdo->prepare('UPDATE chapters SET is_published = 1 - is_published WHERE id=?')->execute([$id]);
        admin_flash('success', 'Statut du chapitre mis à jour.');
        header('Location: chapitres.php?level=' . (int)($_GET['level'] ?? 0));
        exit;
    }

    if ($action === 'delete' && $id) {
        $stmt = $pdo->prepare('DELETE FROM chapters WHERE id=?');
        $stmt->execute([$id]);
        admin_flash('success', 'Chapitre supprimé.');
        header('Location: chapitres.php?deleted=1');
        exit;
    }

    if ($action === 'duplicate' && $id) {
        try {
            $stmt = $pdo->prepare('SELECT level_id FROM chapters WHERE id=?');
            $stmt->execute([$id]);
            $targetLevelId = (int)$stmt->fetchColumn();
            if (!$targetLevelId) throw new RuntimeException('Chapitre introuvable.');
            $newId = duplicate_complete_chapter($pdo, $id, $targetLevelId);
            admin_flash('success', 'Chapitre complet dupliqué en brouillon.');
            header('Location: chapitre.php?chapter_id=' . $newId);
            exit;
        } catch (Throwable $e) {
            error_log('Complete chapter duplication: ' . $e->getMessage());
            header('Location: chapitres.php?duplicate_error=1');
            exit;
        }
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if ($slug === '') $slug = slugify_admin($title);

    $data = [
        'level_id' => (int)($_POST['level_id'] ?? 0),
        'subject_id' => $_POST['subject_id'] !== '' ? (int)$_POST['subject_id'] : null,
        'slug' => $slug,
        'number' => max(1, (int)($_POST['number'] ?? 1)),
        'title' => $title,
        'summary' => trim($_POST['summary'] ?? ''),
        'legacy_path' => trim($_POST['legacy_path'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
    ];

    if ($id) {
        $data['id'] = $id;
        $stmt = $pdo->prepare('UPDATE chapters SET level_id=:level_id, subject_id=:subject_id, slug=:slug, number=:number, title=:title, summary=:summary, legacy_path=:legacy_path, sort_order=:sort_order, is_published=:is_published WHERE id=:id');
        $stmt->execute($data);
    } else {
        $stmt = $pdo->prepare('INSERT INTO chapters(level_id, subject_id, slug, number, title, summary, legacy_path, sort_order, is_published) VALUES(:level_id, :subject_id, :slug, :number, :title, :summary, :legacy_path, :sort_order, :is_published)');
        $stmt->execute($data);
    }

    admin_flash('success', 'Chapitre enregistré.');
    header('Location: chapitres.php?level=' . urlencode((string)$data['level_id']) . '&saved=1');
    exit;
    } catch (Throwable $e) {
        error_log('Chapter admin error: ' . $e->getMessage());
        $error = (string)$e->getCode() === '23000'
            ? 'Ce slug existe déjà. Choisissez une autre adresse pour ce chapitre.'
            : 'Le chapitre n’a pas pu être enregistré. Vérifiez les champs et la base de données.';
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM chapters WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$levels = $pdo->query('SELECT * FROM levels ORDER BY sort_order,id')->fetchAll();
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY name')->fetchAll();
$activeLevel = (int)($_GET['level'] ?? ($edit['level_id'] ?? 0));

$sql = 'SELECT c.*, l.short AS level_short, l.name AS level_name, l.accent AS level_accent, s.name AS subject_name FROM chapters c JOIN levels l ON l.id=c.level_id LEFT JOIN subjects s ON s.id=c.subject_id';
$params = [];
if ($activeLevel) {
    $sql .= ' WHERE c.level_id=?';
    $params[] = $activeLevel;
}
$sql .= ' ORDER BY l.sort_order, c.sort_order, c.number, c.id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$chapters = $stmt->fetchAll();

$levelCounts = [];
foreach ($pdo->query('SELECT level_id, COUNT(*) AS n FROM chapters GROUP BY level_id') as $row) {
    $levelCounts[(int)$row['level_id']] = (int)$row['n'];
}
$totalChapters = array_sum($levelCounts);

page_head('Admin - Cours', 'Le programme : niveaux, chapitres et matières.', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>

  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">Cours</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Le programme en un seul endroit : choisir un niveau, gérer ses chapitres et leur publication.</p>
  </section>
  <?php if ($error): ?><div class="admin-inline-notice is-error mt-5" role="alert"><strong>Enregistrement impossible</strong><span><?= h($error) ?></span></div><?php endif; ?>

  <section class="card mt-8 p-6">
    <h2 class="section-title"><?= $edit ? 'Modifier un chapitre' : 'Ajouter un chapitre' ?></h2>
    <form method="post" class="work-filter-panel mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
      <div class="work-filter-grid">
        <label>Niveau
          <select name="level_id" required>
            <?php foreach ($levels as $level): ?>
              <option value="<?= (int)$level['id'] ?>" <?= (int)($edit['level_id'] ?? $activeLevel) === (int)$level['id'] ? 'selected' : '' ?>><?= h($level['short'] . ' — ' . $level['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Matière
          <select name="subject_id">
            <option value="">Automatique</option>
            <?php foreach ($subjects as $subject): ?>
              <option value="<?= (int)$subject['id'] ?>" <?= (int)($edit['subject_id'] ?? 0) === (int)$subject['id'] ? 'selected' : '' ?>><?= h($subject['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Numéro
          <input type="number" min="1" name="number" value="<?= h($edit['number'] ?? '1') ?>" required>
        </label>
        <label>Titre
          <input name="title" value="<?= h($edit['title'] ?? '') ?>" required>
        </label>
        <label>Slug
          <input name="slug" value="<?= h($edit['slug'] ?? '') ?>" placeholder="auto si vide">
        </label>
        <label>Ordre
          <input type="number" name="sort_order" value="<?= h($edit['sort_order'] ?? $edit['number'] ?? '0') ?>">
        </label>
      </div>

      <label class="mt-4 block font-bold">Résumé
        <textarea name="summary" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 p-4 dark:border-slate-700 dark:bg-slate-900"><?= h($edit['summary'] ?? '') ?></textarea>
      </label>

      <input type="hidden" name="legacy_path" value="<?= h($edit['legacy_path'] ?? '') ?>">

      <div class="mt-4 flex flex-wrap items-center gap-3">
        <label><input type="checkbox" name="is_published" <?= !isset($edit) || (int)($edit['is_published'] ?? 1) === 1 ? 'checked' : '' ?>> Publié</label>
        <button class="btn-primary" type="submit">Enregistrer</button>
        <?php if ($edit): ?><a class="btn-secondary" href="chapitres.php">Annuler</a><?php endif; ?>
        <?php if ($edit): ?><a class="btn-secondary" href="ressources-chapitre.php?chapter_id=<?= (int)$edit['id'] ?>">Vidéos et savoirs</a><a class="btn-secondary" href="fichiers.php?chapter_id=<?= (int)$edit['id'] ?>">Documents</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="mt-8">
    <nav class="cours-levels" aria-label="Choisir un niveau">
      <a class="cours-level<?= $activeLevel === 0 ? ' is-active' : '' ?>" href="chapitres.php">Tous <span class="cours-level-count"><?= $totalChapters ?></span></a>
      <?php foreach ($levels as $level): $n = $levelCounts[(int)$level['id']] ?? 0; ?>
        <a class="cours-level<?= $activeLevel === (int)$level['id'] ? ' is-active' : '' ?>" href="chapitres.php?level=<?= (int)$level['id'] ?>" style="--niveau-accent:<?= h($level['accent']) ?>"><span class="cours-level-dot" aria-hidden="true"></span><?= h($level['short']) ?> <span class="cours-level-count"><?= $n ?></span></a>
      <?php endforeach; ?>
      <a class="cours-level-add" href="niveaux.php">+ Niveau</a>
    </nav>

    <form method="get" class="work-filter-panel mt-5" data-chapter-filter>
      <div class="work-filter-grid">
        <label>Rechercher un chapitre
          <input type="search" data-chapter-search placeholder="Titre, notion, numéro…" autocomplete="off">
        </label>
      </div>
      <p class="filter-reset" data-chapter-count role="status" aria-live="polite"><?= count($chapters) ?> chapitre<?= count($chapters) > 1 ? 's' : '' ?></p>
    </form>

    <div class="chapter-grid mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-chapter-list>
      <?php foreach ($chapters as $chapter): ?>
        <article class="chapter-tile<?= (int)$chapter['is_published'] ? '' : ' is-draft' ?>" data-card data-chapter-item style="--card-accent:<?= h($chapter['level_accent'] ?? '#2563eb') ?>">
          <span class="chapter-number"><?= h($chapter['level_short']) ?> · Chapitre <?= (int)$chapter['number'] ?><?php if ($chapter['subject_name']): ?> · <?= h($chapter['subject_name']) ?><?php endif; ?></span>
          <span class="status-pill <?= (int)$chapter['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$chapter['is_published'] ? 'Publié' : 'Brouillon' ?></span>
          <h3><?= h($chapter['title']) ?></h3>
          <?php if ($chapter['summary']): ?><p><?= h($chapter['summary']) ?></p><?php endif; ?>
          <div class="chapter-actions">
            <a href="chapitre.php?chapter_id=<?= (int)$chapter['id'] ?>">Gérer le chapitre</a>
            <a href="chapitres.php?edit=<?= (int)$chapter['id'] ?>">Paramètres</a>
            <a href="../chapitre.php?slug=<?= h($chapter['slug']) ?>">Voir</a>
            <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$chapter['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="btn-publish <?= (int)$chapter['is_published'] ? 'is-hide' : 'is-publish' ?>" type="submit"><?= (int)$chapter['is_published'] ? 'Masquer' : 'Publier' ?></button></form>
            <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$chapter['id'] ?>"><input type="hidden" name="action" value="duplicate"><button class="btn-secondary" type="submit">Dupliquer</button></form>
            <form method="post" onsubmit="return confirm('Supprimer ce chapitre ? Les éléments liés peuvent aussi être supprimés.')">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int)$chapter['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button class="btn-secondary" type="submit">Supprimer</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
      <p class="student-empty" data-chapter-empty hidden>Aucun chapitre ne correspond à cette recherche.</p>
    </div>
  </section>
</main>
<script src="../assets/js/chapter-filter.js" defer></script>
</body>
</html>
