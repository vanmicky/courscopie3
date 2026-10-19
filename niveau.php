<?php
require_once __DIR__ . '/includes/repository.php';
$slug = $_GET['slug'] ?? '';
$level = get_level($slug);
if (!$level) { http_response_code(404); page_head('Niveau introuvable', '', ['math' => false]); site_header(); echo '<main class="page-container"><section class="student-empty mt-8"><h1>Niveau introuvable</h1><p>Ce niveau n’existe pas ou n’est plus publié.</p><a class="btn-primary" href="' . h(base_path('index.php')) . '">Retour aux niveaux</a></section></main>'; site_footer(); exit; }
$chapters = get_chapters($slug);
$examSpace = level_exam_space($level);
$examExercises = $examSpace ? get_exercises(['level' => $slug, 'category' => $examSpace['type']]) : [];
page_head($level['name'], 'Chapitres et ressources pour ' . $level['name'], ['math' => false, 'accent' => (string)($level['accent'] ?? '')]);
site_header();
?>
<main id="main-content" class="page-container level-page student-page editorial-level" style="--accent:<?= h($level['accent']) ?>">
  <a href="<?= h(base_path('index.php')) ?>" class="nav-link">← Accueil</a>
  <section class="level-hero mt-5">
    <div class="level-hero-grid">
      <div class="level-hero-copy">
        <span class="badge"><?= h($level['short']) ?></span>
        <h1 class="page-title mt-4"><?= h($level['name']) ?></h1>
        <p class="level-summary"><?= h($level['summary']) ?></p>
        <div class="level-resource-strip">
          <a class="level-resource-pill" href="<?= h(base_path('travail-a-faire.php?level=' . $level['slug'])) ?>">Travail à faire</a>
          <a class="level-resource-pill" href="<?= h(base_path('exercices.php?level=' . $level['slug'])) ?>">Exercices</a>
          <a class="level-resource-pill" href="<?= h(base_path('qcm.php?level=' . $level['slug'])) ?>">QCM</a>
          <a class="level-resource-pill" href="<?= h(base_path('flashcards.php?level=' . $level['slug'])) ?>">Flashcards</a>
        </div>
      </div>
      <figure class="level-hero-figure">
        <?php if (!empty($level['image'])): ?><?= responsive_picture((string)$level['image'], (string)(($level['alt_text'] ?? '') ?: $level['name']), 'level-hero-image', '(min-width: 900px) 45vw, 92vw', true) ?><?php else: ?><div class="level-hero-image level-image-placeholder"><strong><?= h($level['short']) ?></strong></div><?php endif; ?>
        <figcaption class="level-hero-caption"><strong><?= h($level['short']) ?></strong><span><?= h($level['subject'] ?? $level['name']) ?></span></figcaption>
      </figure>
    </div>
  </section>
  <?php if ($examSpace): $corrected = count(array_filter($examExercises, static function ($exercise) { return trim((string)$exercise['correction']) !== ''; })); ?>
  <section class="exam-entry mt-7" aria-labelledby="exam-entry-title">
    <div><span class="exam-entry-kicker"><?= h($examSpace['eyebrow']) ?></span><h2 id="exam-entry-title"><?= h($examSpace['label']) ?></h2><p><?= h($examSpace['description']) ?></p></div>
    <div class="exam-entry-meta"><span><strong><?= count($examExercises) ?></strong> exercices</span><span><strong><?= $corrected ?></strong> corrigés</span><a class="btn-primary" href="<?= h(base_path('examen.php?level=' . $level['slug'] . '&type=' . $examSpace['type'])) ?>">Ouvrir l’espace</a></div>
  </section>
  <?php endif; ?>
  <section id="chapitres" class="mt-12">
    <div class="search-row">
      <div><span class="editorial-eyebrow"><?= h($level['short']) ?> · Programme</span><h2 class="section-title mt-3">Les chapitres</h2></div>
      <label class="editorial-search" for="searchInput"><span>Rechercher un chapitre</span><input id="searchInput" type="search" placeholder="Titre ou notion…" autocomplete="off" aria-controls="chapter-results"></label>
    </div>
    <p class="editorial-result-count" data-search-status role="status" aria-live="polite"><?= count($chapters) ?> chapitre<?= count($chapters) > 1 ? 's' : '' ?></p>
    <div id="chapter-results" class="chapter-grid">
      <?php foreach ($chapters as $chapter): ?>
      <article class="chapter-tile" data-card style="--card-accent:<?= h($level['accent']) ?>">
        <a class="chapter-link-full" href="<?= h(base_path('chapitre.php?slug=' . $chapter['slug'])) ?>">
          <span class="chapter-number">Chapitre <?= (int)$chapter['number'] ?></span>
          <h3><?= h($chapter['title']) ?></h3>
          <?php $resume = chapter_summary($chapter['summary'] ?? ''); ?>
          <?php if ($resume !== ''): ?><p><?= h($resume) ?></p><?php endif; ?>
        </a>
        <div class="chapter-actions">
          <a href="<?= h(base_path('chapitre.php?slug=' . $chapter['slug'])) ?>#documents">Documents</a>
          <a href="<?= h(base_path('chapitre.php?slug=' . $chapter['slug'])) ?>#exercices">Activités</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="editorial-empty" data-search-empty hidden>
      <h3>Aucun chapitre trouvé</h3>
      <p class="muted">Essayez un autre mot-clé du programme.</p>
      <button type="button" class="btn-secondary" data-search-reset>Effacer la recherche</button>
    </div>
  </section>
</main>
<?php site_footer(); ?>
