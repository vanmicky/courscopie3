<?php
require_once __DIR__ . '/includes/repository.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$method = $slug !== '' ? get_method($slug, admin_preview_active()) : null;

if (!$method) {
    http_response_code(404);
    page_head('Fiche méthode introuvable', '', ['math' => false, 'robots' => false]);
    site_header();
    echo '<main class="page-container student-page"><section class="student-empty mt-8"><h1>Fiche introuvable</h1><p>Cette méthode n’existe pas ou n’est plus publiée.</p><a class="btn-primary" href="' . h(base_path('methodes.php')) . '">Voir toutes les méthodes</a></section></main>';
    site_footer();
    exit;
}

// Fiches voisines : meme domaine, pour continuer la lecture.
$siblings = array_values(array_filter(
    get_methods(false),
    static fn($m) => ($m['domain'] ?? '') === ($method['domain'] ?? '') && ($m['slug'] ?? '') !== ($method['slug'] ?? '')
));
$siblings = array_slice($siblings, 0, 3);

// Longueur du contenu : sert a n'activer le confort de lecture que sur les
// fiches vraiment longues (barre de progression, retour en haut).
$content = (string)($method['content'] ?? '');
$plain = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');
$wordCount = $plain === '' ? 0 : count(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY));
$readingMinutes = max(1, (int)round($wordCount / 200));
$sectionCount = preg_match_all('/<h2\b/i', $content);
$isLong = $wordCount > 900;

$description = trim((string)($method['summary'] ?? ''));
page_head((string)$method['title'], $description !== '' ? $description : 'Fiche méthode', ['accent' => (string)($method['accent'] ?? '')]);
site_header();
?>
<?php if ($isLong): ?>
<div class="reading-progress" aria-hidden="true"><span data-reading-progress></span></div>
<?php endif; ?>

<main id="main-content" class="page-container student-page method-detail" style="--method-accent:<?= h($method['accent'] ?: '#2563eb') ?>">
  <a class="nav-link" href="<?= h(base_path('methodes.php')) ?>">← Toutes les méthodes</a>

  <p class="method-meta mt-4">
    <span><?= number_format($wordCount, 0, ',', ' ') ?> mots</span>
    <span aria-hidden="true">·</span>
    <span>≈ <?= $readingMinutes ?> min de lecture</span>
    <?php if ($sectionCount): ?>
      <span aria-hidden="true">·</span>
      <span><?= (int)$sectionCount ?> sections</span>
    <?php endif; ?>
  </p>

  <article class="method-sheet mt-5">
    <?= $content ?>
  </article>

  <?php if ($isLong): ?>
    <p class="reading-foot">
      <button type="button" class="btn-secondary" data-reading-top>↑ Revenir en haut de la fiche</button>
    </p>
  <?php endif; ?>

  <?php if ($siblings): ?>
    <section class="method-siblings mt-12" aria-labelledby="method-siblings-title">
      <h2 id="method-siblings-title" class="section-title">Dans le même domaine</h2>
      <div class="methods-grid mt-5">
        <?php foreach ($siblings as $sibling): ?>
          <a class="method-card" href="<?= h(base_path('methode.php?slug=' . urlencode((string)$sibling['slug']))) ?>" style="--method-accent:<?= h($sibling['accent'] ?: '#2563eb') ?>">
            <?php if (!empty($sibling['icon'])): ?><span class="method-icon" aria-hidden="true"><?= h($sibling['icon']) ?></span><?php endif; ?>
            <div>
              <span class="method-tag"><?= h($sibling['domain']) ?></span>
              <h3><?= h($sibling['title']) ?></h3>
              <p class="muted"><?= h($sibling['summary']) ?></p>
            </div>
            <span class="method-open">Ouvrir <span aria-hidden="true">→</span></span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>
<?php if ($isLong): ?><script src="<?= h(base_path('assets/js/reading.js?v=94')) ?>" defer></script><?php endif; ?>
<script src="<?= h(base_path('assets/js/methode-sections.js?v=94')) ?>" defer></script>
<?php site_footer(); ?>
