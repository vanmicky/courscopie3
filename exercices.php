<?php
require_once __DIR__ . '/includes/repository.php';

$levels = get_levels();
$filters = ['level' => trim($_GET['level'] ?? ''), 'q' => trim($_GET['q'] ?? '')];
$exercises = get_exercises($filters);
$hasFilters = $filters['level'] !== '' || $filters['q'] !== '';
page_head('Exercices');
site_header();
?>
<main class="page-container student-page exercise-library-page">
  <a class="nav-link" href="<?= h(base_path('index.php')) ?>">← Accueil</a>
  <section class="work-global-hero mt-5">
    <span class="badge">Pratique</span>
    <h1 class="page-title mt-4">Exercices</h1>
    <p class="muted">Des énoncés classés par niveau et par chapitre. Cherchez d’abord, puis ouvrez la correction pour vérifier votre démarche.</p>
    <p class="library-count"><?= count($exercises) ?> exercice<?= count($exercises) > 1 ? 's' : '' ?> <?= $hasFilters ? 'correspondant aux filtres' : 'disponibles' ?></p>
  </section>
  <form method="get" class="work-filter-panel mt-8">
    <div class="work-filter-grid">
      <label>Niveau<select name="level"><option value="">Tous</option><?php foreach ($levels as $level): ?><option value="<?= h($level['slug']) ?>" <?= ($filters['level'] === $level['slug']) ? 'selected' : '' ?>><?= h($level['short'] . ' — ' . $level['name']) ?></option><?php endforeach; ?></select></label>
      <label>Recherche<input type="search" name="q" value="<?= h($filters['q']) ?>" placeholder="Exercice, chapitre…"></label>
      <div class="filter-submit"><button class="btn-primary" type="submit">Filtrer</button></div>
    </div>
    <?php if ($hasFilters): ?><a class="filter-reset" href="<?= h(base_path('exercices.php')) ?>">Réinitialiser les filtres</a><?php endif; ?>
  </form>
  <section class="exercise-public-list mt-8">
    <?php foreach ($exercises as $exercise): ?>
      <article class="exercise-public-card" style="--card-accent:<?= h($exercise['accent']) ?>">
        <span class="chapter-number"><?= h($exercise['level_short']) ?> · Chapitre <?= (int)$exercise['chapter_number'] ?> · <?= h(ucfirst((string)$exercise['difficulty'])) ?></span>
        <h2><?= h($exercise['title']) ?></h2>
        <div class="lesson-content mt-4"><?= render_text($exercise['statement']) ?></div>
        <?php if (trim((string)$exercise['correction']) !== ''): ?>
          <details class="correction-box mt-4"><summary>Voir la correction</summary><div><?= render_text($exercise['correction']) ?></div></details>
        <?php endif; ?>
        <div class="chapter-actions mt-4"><a href="<?= h(base_path('chapitre.php?slug=' . $exercise['chapter_slug'])) ?>">Voir le chapitre</a></div>
      </article>
    <?php endforeach; ?>
  </section>
  <?php if (!$exercises): ?>
    <div class="student-empty mt-8">
      <h2>Aucun exercice trouvé</h2>
      <p><?= $hasFilters ? 'Modifiez les filtres ou réinitialisez la recherche.' : 'Les exercices seront publiés progressivement.' ?></p>
      <?php if ($hasFilters): ?><a class="btn-secondary" href="<?= h(base_path('exercices.php')) ?>">Voir tous les exercices</a><?php endif; ?>
    </div>
  <?php endif; ?>
</main>
<?php site_footer(); ?>
