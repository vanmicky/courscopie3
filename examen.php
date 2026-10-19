<?php
require_once __DIR__ . '/includes/repository.php';

$levelSlug = trim((string)($_GET['level'] ?? ''));
$requestedType = trim((string)($_GET['type'] ?? ''));
$level = get_level($levelSlug);
$space = $level ? level_exam_space($level) : null;
if (!$level || !$space || $requestedType !== $space['type']) {
    http_response_code(404);
    page_head('Espace de révision introuvable'); site_header();
    echo '<main class="page-container py-8 student-page"><section class="student-empty"><h1>Espace introuvable</h1><p>Ce niveau ne possède pas cet espace de révision.</p><a class="btn-primary" href="' . h(base_path('index.php')) . '">Retour aux niveaux</a></section></main>';
    site_footer(); exit;
}

$chapters = get_chapters($levelSlug);
$filters = [
    'level' => $levelSlug,
    'category' => $space['type'],
    'chapter_id' => (int)($_GET['theme'] ?? 0),
    'difficulty' => trim((string)($_GET['difficulty'] ?? '')),
    'correction' => in_array(($_GET['correction'] ?? ''), ['yes','no'], true) ? $_GET['correction'] : '',
    'q' => trim((string)($_GET['q'] ?? '')),
];
$exercises = get_exercises($filters);
$allExercises = get_exercises(['level' => $levelSlug, 'category' => $space['type']]);
$correctedCount = count(array_filter($allExercises, static function ($exercise) { return trim((string)$exercise['correction']) !== ''; }));
$themeCount = count(array_unique(array_map(static function ($exercise) { return (int)$exercise['chapter_id']; }, $allExercises)));
$difficultyLabels = ['facile'=>'Facile','normal'=>'Intermédiaire','difficile'=>'Difficile','défi'=>'Défi'];

page_head($space['label'] . ' · ' . $level['name'], $space['description'], ['accent' => (string)($level['accent'] ?? '')]);
site_header();
?>
<main class="page-container py-8 exam-space-page student-page level-color-theme" style="--accent:<?= h($level['accent']) ?>">
  <a class="nav-link" href="<?= h(base_path('niveau.php?slug=' . $levelSlug)) ?>">← <?= h($level['name']) ?></a>
  <section class="exam-hero mt-5">
    <div><span class="badge"><?= h($level['short']) ?> · <?= h($space['eyebrow']) ?></span><h1><?= h($space['label']) ?></h1><p><?= h($space['description']) ?></p></div>
    <div class="exam-hero-stats" aria-label="Contenus disponibles"><span><strong><?= count($allExercises) ?></strong> exercices</span><span><strong><?= $themeCount ?></strong> thèmes</span><span><strong><?= $correctedCount ?></strong> corrigés</span></div>
  </section>

  <form method="get" class="exam-filters mt-7" aria-label="Filtrer les exercices">
    <input type="hidden" name="level" value="<?= h($levelSlug) ?>"><input type="hidden" name="type" value="<?= h($space['type']) ?>">
    <label class="exam-search">Rechercher<input type="search" name="q" value="<?= h($filters['q']) ?>" placeholder="Notion, mot-clé, chapitre…"></label>
    <label>Thème<select name="theme"><option value="0">Tous les chapitres</option><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= $filters['chapter_id'] === (int)$chapter['id'] ? 'selected' : '' ?>>Chapitre <?= (int)$chapter['number'] ?> · <?= h($chapter['title']) ?></option><?php endforeach; ?></select></label>
    <label>Difficulté<select name="difficulty"><option value="">Toutes</option><?php foreach ($difficultyLabels as $value => $label): ?><option value="<?= h($value) ?>" <?= $filters['difficulty'] === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label>
    <label>Correction<select name="correction"><option value="">Toutes</option><option value="yes" <?= $filters['correction'] === 'yes' ? 'selected' : '' ?>>Avec correction</option><option value="no" <?= $filters['correction'] === 'no' ? 'selected' : '' ?>>Sans correction</option></select></label>
    <div class="exam-filter-actions"><button class="btn-primary" type="submit">Afficher</button><a class="btn-secondary" href="<?= h(base_path('examen.php?level=' . $levelSlug . '&type=' . $space['type'])) ?>">Réinitialiser</a></div>
  </form>

  <section class="exam-results mt-8" aria-labelledby="exam-results-title">
    <div class="exam-results-heading"><div><span class="badge">Entraînement</span><h2 id="exam-results-title"><?= count($exercises) ?> exercice(s)</h2></div><p>Les corrections restent masquées jusqu’à leur ouverture.</p></div>
    <div class="exam-exercise-grid mt-5">
      <?php foreach ($exercises as $index => $exercise): $hasCorrection = trim((string)$exercise['correction']) !== ''; ?>
      <article class="exam-exercise-card">
        <header><span class="exam-index"><?= (int)$index + 1 ?></span><div><span class="exam-theme"><?= h($exercise['level_short']) ?> · Chapitre <?= (int)$exercise['chapter_number'] ?></span><h3><?= h($exercise['title']) ?></h3><p><?= h($exercise['chapter_title']) ?></p></div></header>
        <div class="exam-tags"><span><?= h($difficultyLabels[$exercise['difficulty']] ?? ucfirst((string)$exercise['difficulty'])) ?></span><span class="<?= $hasCorrection ? 'has-correction' : 'no-correction' ?>"><?= $hasCorrection ? 'Correction disponible' : 'Sans correction' ?></span></div>
        <div class="lesson-content exam-statement"><?= render_text($exercise['statement']) ?></div>
        <?php if ($hasCorrection): ?><details class="exam-correction"><summary>Afficher la correction</summary><div class="lesson-content"><?= render_text($exercise['correction']) ?></div></details><?php endif; ?>
        <footer><a href="<?= h(base_path('chapitre.php?slug=' . $exercise['chapter_slug'])) ?>">Voir le chapitre</a></footer>
      </article>
      <?php endforeach; ?>
    </div>
    <?php if (!$exercises): ?><div class="student-empty"><h3>Aucun exercice trouvé</h3><p>Modifiez les filtres ou réinitialisez la recherche.</p><a class="btn-secondary" href="<?= h(base_path('examen.php?level=' . $levelSlug . '&type=' . $space['type'])) ?>">Afficher tous les exercices</a></div><?php endif; ?>
  </section>
</main>
<?php site_footer(); ?>
