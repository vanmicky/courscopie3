<?php
require_once __DIR__ . '/auth.php';
require_admin();
verify_csrf();
require_once __DIR__ . '/chapter-insights.php';
$pdo = db();
$schema = admin_ensure_v69_schema();
$error = $schema['ok'] ? '' : $schema['message'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $schema['ok']) {
    try {
        $selected = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['selected'] ?? [])))));
        if (!$selected) throw new RuntimeException('Sélectionnez au moins un chapitre.');
        $status = ($_POST['bulk_value'] ?? '') === 'publish' ? 1 : 0;
        $placeholders = implode(',', array_fill(0, count($selected), '?'));
        $pdo->prepare("UPDATE chapters SET is_published=? WHERE id IN ($placeholders)")->execute(array_merge([$status], $selected));
        admin_flash('success', $status ? 'Chapitres publiés.' : 'Chapitres replacés en brouillon.');
        header('Location: pilotage.php');
        exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$levels = $pdo->query('SELECT * FROM levels ORDER BY sort_order,id')->fetchAll();
$levelId = (int)($_GET['level'] ?? 0);
$state = (string)($_GET['state'] ?? 'all');
$query = 'SELECT c.*,l.short AS level_short,l.name AS level_name,l.accent FROM chapters c JOIN levels l ON l.id=c.level_id';
$params = [];
if ($levelId) { $query .= ' WHERE c.level_id=?'; $params[] = $levelId; }
$query .= ' ORDER BY l.sort_order,c.sort_order,c.number,c.id';
$stmt = $pdo->prepare($query); $stmt->execute($params); $chapters = $stmt->fetchAll();
$enriched = [];
foreach ($chapters as $chapter) {
    $health = chapter_health($pdo, $chapter);
    $chapter['health'] = $health;
    $chapter['last_change'] = chapter_last_change($pdo, (int)$chapter['id'], $chapter);
    $enriched[] = $chapter;
}
$chapters = $enriched;
$rows = [];
foreach ($chapters as $chapter) {
    $health = $chapter['health'];
    if ($state === 'published' && !(int)$chapter['is_published']) continue;
    if ($state === 'draft' && (int)$chapter['is_published']) continue;
    if ($state === 'attention' && $health['score'] === 100) continue;
    $rows[] = $chapter;
}
$allCount = count($chapters);
$publishedCount = count(array_filter($chapters, static function ($row) { return (int)$row['is_published'] === 1; }));
$readyCount = count(array_filter($chapters, static function ($row) { return $row['health']['score'] === 100; }));
$alertCount = array_sum(array_map(static function ($row) { return count($row['health']['alerts']); }, $chapters));
usort($chapters, static function ($a, $b) { return strcmp((string)$b['last_change'], (string)$a['last_change']); });
$recent = array_slice($chapters, 0, 5);

page_head('Pilotage pédagogique', '', ['math' => false]); admin_nav();
?>
<main class="page-container py-8 pedagogy-dashboard">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5"><span class="badge">V73</span><h1 class="page-title mt-3">Pilotage pédagogique</h1><p class="mt-3 muted">Repérer immédiatement les chapitres prêts, les brouillons et les ressources à compléter.</p></section>
  <?php if ($error): ?><div class="admin-inline-notice is-error mt-5" role="alert"><?= h($error) ?></div><?php endif; ?>
  <section class="pilotage-stats mt-7" aria-label="Résumé">
    <article><span>Chapitres</span><strong><?= $allCount ?></strong></article><article><span>Publiés</span><strong><?= $publishedCount ?></strong></article><article><span>Complets</span><strong><?= $readyCount ?></strong></article><article><span>Alertes</span><strong><?= $alertCount ?></strong></article>
  </section>
  <form class="work-filter-panel mt-7 pilotage-filters" method="get">
    <label>Niveau<select name="level"><option value="0">Tous les niveaux</option><?php foreach ($levels as $level): ?><option value="<?= (int)$level['id'] ?>" <?= $levelId === (int)$level['id'] ? 'selected' : '' ?>><?= h($level['short'] . ' — ' . $level['name']) ?></option><?php endforeach; ?></select></label>
    <label>État<select name="state"><option value="all">Tous</option><option value="attention" <?= $state === 'attention' ? 'selected' : '' ?>>À compléter</option><option value="published" <?= $state === 'published' ? 'selected' : '' ?>>Publiés</option><option value="draft" <?= $state === 'draft' ? 'selected' : '' ?>>Brouillons</option></select></label>
    <button class="btn-primary" type="submit">Filtrer</button>
  </form>
  <div class="pilotage-layout mt-7">
    <section>
      <form id="pilotage-bulk" class="workspace-bulk" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><label><input type="checkbox" data-pilotage-select-all> Tout sélectionner</label><select name="bulk_value"><option value="publish">Publier</option><option value="hide">Mettre en brouillon</option></select><button class="btn-secondary" type="submit">Appliquer</button></form>
      <div class="pilotage-list">
      <?php foreach ($rows as $chapter): $health = $chapter['health']; ?>
        <article class="pilotage-chapter" style="--card-accent:<?= h($chapter['accent']) ?>">
          <label class="workspace-select"><input type="checkbox" name="selected[]" value="<?= (int)$chapter['id'] ?>" form="pilotage-bulk" data-pilotage-select><span class="sr-only">Sélectionner</span></label>
          <div class="completion-bar" style="--progress:<?= (int)$health['score'] ?>%"><span></span></div>
          <div class="pilotage-chapter-main"><div><span class="chapter-number"><?= h($chapter['level_short']) ?> · Chapitre <?= (int)$chapter['number'] ?></span><h2><?= h($chapter['title']) ?></h2></div><div class="pilotage-score"><strong><?= (int)$health['score'] ?>%</strong><span><?= $chapter['is_published'] ? 'Publié' : 'Brouillon' ?></span></div></div>
          <div class="workspace-checks"><?php foreach ($health['checks'] as $label => $complete): ?><span class="<?= $complete ? 'is-complete' : '' ?>"><?= $complete ? '✓' : '○' ?> <?= h($label) ?></span><?php endforeach; ?></div>
          <?php if ($health['alerts']): ?><details class="workspace-alerts"><summary><?= count($health['alerts']) ?> point(s) à vérifier</summary><ul><?php foreach ($health['alerts'] as $alert): ?><li><?= h($alert) ?></li><?php endforeach; ?></ul></details><?php endif; ?>
          <div class="pilotage-actions"><span>Modifié le <?= h(format_date_fr($chapter['last_change'])) ?></span><a class="btn-primary" href="chapitre.php?chapter_id=<?= (int)$chapter['id'] ?>">Gérer</a><a class="btn-secondary" href="../chapitre.php?slug=<?= h($chapter['slug']) ?>&preview=1" target="_blank" rel="noopener">Prévisualiser</a></div>
        </article>
      <?php endforeach; ?>
      <?php if (!$rows): ?><p class="chapter-empty">Aucun chapitre ne correspond aux filtres.</p><?php endif; ?>
      </div>
    </section>
    <aside class="pilotage-recent"><h2>Dernières modifications</h2><?php foreach ($recent as $chapter): ?><a href="chapitre.php?chapter_id=<?= (int)$chapter['id'] ?>"><strong><?= h($chapter['title']) ?></strong><span><?= h($chapter['level_short']) ?> · <?= h(format_date_fr($chapter['last_change'])) ?></span></a><?php endforeach; ?></aside>
  </div>
</main>
<script src="../assets/js/pilotage.js" defer></script>
</body></html>
