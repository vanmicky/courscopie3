<?php
require_once __DIR__ . '/includes/repository.php';

$slug = trim($_GET['slug'] ?? '');
$preview = admin_preview_active();
$chapter = get_chapter($slug, $preview);
if (!$chapter) {
    http_response_code(404);
    page_head('Chapitre introuvable');
    site_header();
    echo '<main class="page-container py-8"><h1 class="page-title">Chapitre introuvable</h1></main>';
    site_footer();
    exit;
}

$chapterId = (int)($chapter['id'] ?? 0);
$documents = $chapterId ? get_chapter_files($chapterId, $preview) : [];
$exercises = $chapterId ? get_exercises(['chapter_id' => $chapterId, 'includeDrafts' => $preview]) : [];
$videos = $chapterId ? get_chapter_videos($chapterId, $preview) : [];
$qcms = $chapterId ? get_qcms(['chapter_id' => $chapterId, 'includeDrafts' => $preview]) : [];
$decks = $chapterId ? get_flashcard_decks(['chapter_id' => $chapterId, 'includeDrafts' => $preview]) : [];
$skills = $chapterId ? get_chapter_skills($chapterId, $preview) : [];
$documentTypes = document_type_labels();
$exerciseGroups = ['ds' => [], 'brevet' => [], 'bac' => []];
foreach ($exercises as $exercise) {
    $category = in_array($exercise['category'] ?? '', ['brevet', 'bac'], true) ? $exercise['category'] : 'ds';
    $exerciseGroups[$category][] = $exercise;
}
$isTroisieme = strpos((string)($chapter['level_slug'] ?? ''), 'troisieme') !== false;
$isPremiere = strpos((string)($chapter['level_slug'] ?? ''), 'premiere') !== false;
$exerciseSections = [
    ['id' => 'exercices', 'tab' => 'Exercices', 'badge' => 'Entraînement', 'title' => 'Exercices pour se préparer au DS', 'items' => $exerciseGroups['ds']],
];
if ($isTroisieme) $exerciseSections[] = ['id' => 'brevet', 'tab' => 'Brevet', 'badge' => 'Objectif examen', 'title' => 'Révision pour le Brevet', 'items' => $exerciseGroups['brevet']];
if ($isPremiere) $exerciseSections[] = ['id' => 'bac', 'tab' => 'Type Bac', 'badge' => 'Objectif Bac', 'title' => 'Exercices type Bac', 'items' => $exerciseGroups['bac']];

$skillsByCategory = ['Savoir' => [], 'Savoir-faire' => []];
foreach ($skills as $skill) {
    $category = ($skill['category'] ?? '') === 'Savoir-faire' ? 'Savoir-faire' : 'Savoir';
    $skillsByCategory[$category][] = $skill;
}

/* Onglets du chapitre. Les sections vides ne sont pas proposees : un eleve ne
   doit pas cliquer pour tomber sur « aucun contenu ». Le rendu reste une suite
   de sections ancrees si JavaScript est desactive (les liens defilent). */
$chapterTabs = [
    ['id' => 'documents', 'label' => 'Documents', 'count' => count($documents), 'icon' => 'file'],
];
foreach ($exerciseSections as $section) {
    $chapterTabs[] = ['id' => $section['id'], 'label' => $section['tab'], 'count' => count($section['items']), 'icon' => $section['id'] === 'exercices' ? 'pencil' : ($section['id'] === 'brevet' ? 'award' : 'cap')];
}
$chapterTabs[] = ['id' => 'videos', 'label' => 'Vidéos', 'count' => count($videos), 'icon' => 'video'];
$chapterTabs[] = ['id' => 'qcm', 'label' => 'QCM', 'count' => count($qcms), 'icon' => 'check'];
$chapterTabs[] = ['id' => 'flashcards', 'label' => 'Flashcards', 'count' => count($decks), 'icon' => 'cards'];
$chapterTabs[] = ['id' => 'savoirs', 'label' => 'Savoirs', 'count' => count($skills), 'icon' => 'target'];
$chapterTabs = array_values(array_filter($chapterTabs, static fn($tab) => $tab['count'] > 0));
$activeTabs = array_column($chapterTabs, 'id');
$chapterHasContent = (bool)$chapterTabs;

page_head($chapter['title'], 'Documents, vidéos et activités du chapitre', ['accent' => (string)($chapter['accent'] ?? '')]);
site_header();
if ($preview) admin_preview_banner(base_path('admin/chapitre.php?chapter_id=' . $chapterId));
?>
<main class="page-container chapter-resource-page level-color-theme student-page" style="--accent:<?= h($chapter['accent'] ?? '#2563eb') ?>">
  <a href="<?= h(base_path('niveau.php?slug=' . $chapter['level_slug'])) ?>" class="nav-link">← Retour au niveau</a>
  <section class="chapter-page-hero mt-5">
    <div class="chapter-hero-grid">
      <div>
        <span class="badge"><?= h($chapter['level_short'] ?? '') ?> · Chapitre <?= (int)$chapter['number'] ?></span>
        <h1 class="page-title mt-4"><?= h($chapter['title']) ?></h1>
        <?php $resumeChapitre = chapter_summary($chapter['summary'] ?? ''); ?>
        <?php if ($resumeChapitre !== ''): ?><p class="chapter-hero-summary"><?= h($resumeChapitre) ?></p><?php endif; ?>
      </div>
      <dl class="chapter-hero-stats">
        <div><dt>Documents</dt><dd><?= count($documents) ?></dd></div>
        <div><dt>Exercices</dt><dd><?= count($exercises) ?></dd></div>
        <div><dt>QCM</dt><dd><?= count($qcms) ?></dd></div>
        <div><dt>Flashcards</dt><dd><?= count($decks) ?></dd></div>
      </dl>
    </div>
  </section>

  <?php if (!$chapterHasContent): ?>
    <section class="student-empty mt-8">
      <h2>Chapitre en préparation</h2>
      <p>Les documents, exercices et activités de ce chapitre seront publiés progressivement.</p>
      <a class="btn-secondary" href="<?= h(base_path('niveau.php?slug=' . $chapter['level_slug'])) ?>">Retour au niveau</a>
    </section>
  <?php else: ?>

  <?php if (count($chapterTabs) >= 2): ?>
  <nav class="chapter-tabs" data-chapter-tabs aria-label="Sections du chapitre">
    <?php foreach ($chapterTabs as $index => $tab): ?>
      <a class="chapter-tab<?= $index === 0 ? ' is-active' : '' ?>" href="#<?= h($tab['id']) ?>"
         data-tab-target="<?= h($tab['id']) ?>"<?= $index === 0 ? ' aria-current="true"' : '' ?>>
        <?= icon($tab['icon'], 'chapter-tab-icon', 18) ?>
        <span><?= h($tab['label']) ?></span>
        <span class="chapter-tab-count"><?= (int)$tab['count'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <?php endif; ?>

  <div class="chapter-panels mt-6" data-chapter-panels>
  <?php endif; ?>

  <?php if (in_array('documents', $activeTabs, true)): ?>
  <section id="documents" class="chapter-resource-section">
    <div class="chapter-section-heading"><div><span class="badge">Ressources</span><h2 class="section-title mt-3"><?= icon('file', 'heading-icon', 22) ?>Documents</h2></div><span class="resource-count"><?= count($documents) ?> document<?= count($documents) > 1 ? 's' : '' ?></span></div>
    <div class="document-resource-grid mt-5">
      <?php foreach ($documents as $document): $type = $document['resource_type'] ?? 'document'; $extension = strtoupper((string)pathinfo((string)$document['original_name'], PATHINFO_EXTENSION)); $iconClass = $extension === 'PDF' ? ' is-pdf' : (in_array($extension, ['PNG', 'JPG', 'JPEG', 'GIF', 'WEBP', 'SVG'], true) ? ' is-image' : ''); ?>
        <a class="document-resource-card<?= $iconClass ?><?= !(int)$document['is_published'] ? ' is-preview-draft' : '' ?>" href="<?= h(base_path($document['path'])) ?>" target="_blank" rel="noopener">
          <span class="document-type"><?= h($documentTypes[$type] ?? $documentTypes['document']) ?></span>
          <strong><?= h($document['label'] ?: $document['original_name']) ?></strong><?php if (!(int)$document['is_published']): ?><em>Brouillon</em><?php endif; ?>
          <span><?= h($extension !== '' ? $extension : 'FICHIER') ?> · Ouvrir le document</span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php foreach ($exerciseSections as $exerciseSection): $sectionExercises = $exerciseSection['items']; if (!in_array($exerciseSection['id'], $activeTabs, true)) continue; ?>
  <section id="<?= h($exerciseSection['id']) ?>" class="chapter-resource-section">
    <div class="chapter-section-heading"><div><span class="badge"><?= h($exerciseSection['badge']) ?></span><h2 class="section-title mt-3"><?= icon($exerciseSection['id'] === 'exercices' ? 'pencil' : ($exerciseSection['id'] === 'brevet' ? 'award' : 'cap'), 'heading-icon', 22) ?><?= h($exerciseSection['title']) ?></h2></div><span class="resource-count"><?= count($sectionExercises) ?> exercice<?= count($sectionExercises) > 1 ? 's' : '' ?></span></div>
    <div class="exercise-prep-list mt-5">
      <?php foreach ($sectionExercises as $index => $exercise): ?>
        <article class="exercise-prep-card">
          <div class="exercise-prep-index" aria-hidden="true"><?= (int)$index + 1 ?></div>
          <div>
            <span class="document-type"><?= h(ucfirst($exercise['difficulty'] ?: 'normal')) ?></span>
            <h3><?= h($exercise['title']) ?></h3>
            <div class="lesson-content mt-3"><?= render_text($exercise['statement']) ?></div>
            <?php if (trim((string)$exercise['correction']) !== ''): ?><details class="correction-box mt-4"><summary>Afficher la correction</summary><div><?= render_text($exercise['correction']) ?></div></details><?php else: ?><p class="exercise-no-correction">Correction à venir — cherchez d’abord, nous corrigerons ensemble.</p><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>

  <?php if (in_array('videos', $activeTabs, true)): ?>
  <section id="videos" class="chapter-resource-section">
    <div class="chapter-section-heading"><div><span class="badge">À regarder</span><h2 class="section-title mt-3"><?= icon('video', 'heading-icon', 22) ?>Vidéos à voir</h2></div><span class="resource-count"><?= count($videos) ?> vidéo<?= count($videos) > 1 ? 's' : '' ?></span></div>
    <div class="chapter-video-grid mt-5">
      <?php foreach ($videos as $video): $embed = video_embed_url($video['url']); ?>
        <article class="chapter-video-card<?= !(int)$video['is_published'] ? ' is-preview-draft' : '' ?>">
          <div class="chapter-video-media"><?php if ($embed): ?><iframe src="<?= h($embed) ?>" title="<?= h($video['title']) ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><?php else: ?><a href="<?= h($video['url']) ?>" target="_blank" rel="noopener">Ouvrir la vidéo</a><?php endif; ?></div>
          <div class="chapter-video-copy"><h3><?= h($video['title']) ?></h3><?php if (!empty($video['description'])): ?><p><?= h($video['description']) ?></p><?php endif; ?><a href="<?= h($video['url']) ?>" target="_blank" rel="noopener">Voir dans un nouvel onglet</a></div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (in_array('qcm', $activeTabs, true)): ?>
  <section id="qcm" class="chapter-resource-section">
    <div class="chapter-section-heading"><div><span class="badge">Auto-évaluation</span><h2 class="section-title mt-3"><?= icon('check', 'heading-icon', 22) ?>QCM</h2></div><span class="resource-count"><?= count($qcms) ?> QCM</span></div>
    <div class="chapter-activity-grid mt-5"><?php foreach ($qcms as $qcm): ?><a class="chapter-activity-card<?= !(int)$qcm['is_published'] ? ' is-preview-draft' : '' ?>" href="<?= h(base_path('qcm.php?slug=' . $qcm['slug'] . preview_suffix($preview))) ?>"><span>QCM<?= !(int)$qcm['is_published'] ? ' · Brouillon' : '' ?></span><strong><?= h($qcm['title']) ?></strong><small>Correction immédiate et explications</small></a><?php endforeach; ?></div>
  </section>
  <?php endif; ?>

  <?php if (in_array('flashcards', $activeTabs, true)): ?>
  <section id="flashcards" class="chapter-resource-section">
    <div class="chapter-section-heading"><div><span class="badge">Mémorisation</span><h2 class="section-title mt-3"><?= icon('cards', 'heading-icon', 22) ?>Flashcards</h2></div><span class="resource-count"><?= count($decks) ?> série<?= count($decks) > 1 ? 's' : '' ?></span></div>
    <div class="chapter-activity-grid mt-5"><?php foreach ($decks as $deck): ?><a class="chapter-activity-card<?= !(int)$deck['is_published'] ? ' is-preview-draft' : '' ?>" href="<?= h(base_path('flashcards.php?slug=' . $deck['slug'] . preview_suffix($preview))) ?>"><span>Flashcards<?= !(int)$deck['is_published'] ? ' · Brouillon' : '' ?></span><strong><?= h($deck['title']) ?></strong><small><?= (int)$deck['card_count'] ?> carte<?= (int)$deck['card_count'] > 1 ? 's' : '' ?> à réviser</small></a><?php endforeach; ?></div>
  </section>
  <?php endif; ?>

  <?php if (in_array('savoirs', $activeTabs, true)): ?>
  <section id="savoirs" class="chapter-resource-section" data-chapter-skills data-storage-key="<?= h('chapter-skills:' . $chapter['slug']) ?>">
    <div class="chapter-section-heading"><div><span class="badge">Objectifs</span><h2 class="section-title mt-3"><?= icon('target', 'heading-icon', 22) ?>Savoirs et savoir-faire</h2></div><span class="resource-count" data-skill-progress>0 / <?= count($skills) ?></span></div>
    <div class="skills-columns mt-5">
      <?php foreach ($skillsByCategory as $category => $items): ?>
        <section class="skill-group"><h3><?= h($category) ?></h3><div class="skill-checklist"><?php foreach ($items as $skill): ?><label class="skill-check"><input type="checkbox" value="<?= (int)$skill['id'] ?>"><span><?= render_text($skill['label']) ?></span></label><?php endforeach; ?><?php if (!$items): ?><p class="muted">Aucun élément.</p><?php endif; ?></div></section>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($chapterHasContent): ?></div><?php endif; ?>
</main>
<?php if ($skills): ?><script src="<?= h(base_path('assets/js/chapter-skills.js')) ?>" defer></script><?php endif; ?>
<?php if (count($chapterTabs) >= 2): ?><script src="<?= h(base_path('assets/js/chapter-tabs.js')) ?>" defer></script><?php endif; ?>
<?php site_footer(); ?>
