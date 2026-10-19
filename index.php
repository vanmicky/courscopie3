<?php
require_once __DIR__ . '/includes/repository.php';
$levels = get_levels();
page_head('Accueil', 'Cours de physique-chimie, mathématiques et NSI.', ['math' => false]);
site_header();
?>
<main id="main-content" class="student-page student-home editorial-home">
  <section id="niveaux" class="page-container">
    <div class="home-hero">
      <div class="search-row">
        <div class="home-hero-copy">
          <span class="editorial-eyebrow">Physique-Chimie · Maths · NSI</span>
          <h1 class="home-title">Votre <span class="grad-text">espace de cours</span></h1>
          <p class="editorial-intro"><span>Mathématiques</span><span>Physique-Chimie</span><span>NSI</span></p>
        </div>
        <label class="editorial-search" for="searchInput">
          <span>Rechercher un niveau</span>
          <input id="searchInput" type="search" placeholder="Classe ou matière…" autocomplete="off" aria-controls="level-results">
        </label>
      </div>
      <?php if (!db_ready()): ?><p class="notice-warning mt-4">Mode sans base connectée : les données de départ sont affichées.</p><?php endif; ?>
      <p class="editorial-result-count" data-search-status role="status" aria-live="polite"><?= count($levels) ?> niveau<?= count($levels) > 1 ? 'x' : '' ?></p>
    </div>
    <div id="level-results" class="home-level-grid">
      <?php foreach ($levels as $level): ?>
        <a data-card class="card level-card" href="<?= h(base_path('niveau.php?slug=' . $level['slug'])) ?>" style="--accent:<?= h($level['accent']) ?>">
          <span class="level-accent" aria-hidden="true"></span>
          <?php if (!empty($level['image'])): ?><?= responsive_picture((string)$level['image'], (string)(($level['alt_text'] ?? '') ?: $level['name']), 'level-image', '(min-width: 1100px) 30vw, (min-width: 700px) 45vw, 92vw') ?><?php else: ?><div class="level-image level-image-placeholder" aria-hidden="true"><strong><?= h($level['short']) ?></strong></div><?php endif; ?>
          <div class="card-body">
            <span class="editorial-level-tag"><?= h($level['short']) ?></span>
            <h2><?= h($level['name']) ?></h2>
            <p><?= h($level['summary']) ?></p>
            <span class="editorial-card-link">Accéder aux chapitres <?= icon('arrow-right', 'link-icon', 16) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="editorial-empty" data-search-empty hidden>
      <h2>Aucun niveau trouvé</h2>
      <p class="muted">Essayez un autre mot-clé (par exemple « 3e » ou « maths »).</p>
      <button type="button" class="btn-secondary" data-search-reset>Effacer la recherche</button>
    </div>
  </section>
</main>
<?php site_footer(); ?>
