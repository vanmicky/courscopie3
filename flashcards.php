<?php
require_once __DIR__ . '/includes/repository.php';

$slug = trim($_GET['slug'] ?? '');
$levels = get_levels();

if ($slug !== '') {
$preview = admin_preview_active();
$deck = get_flashcard_deck($slug, $preview);
    if (!$deck) {
        http_response_code(404);
        $deck = ['title' => 'Deck introuvable'];
    }
    $cards = !empty($deck['id']) ? get_flashcards((int)$deck['id']) : [];
    $flashData = array_map(static function (array $card): array {
        $image = (string)($card['image_path'] ?? '');
        $imageVerso = (string)($card['image_back_path'] ?? '');
        return [
            'type' => 'Question',
            'front' => render_text($card['front_text'] ?? ''),
            'back' => render_text($card['back_text'] ?? ''),
            'image' => $image !== '' ? base_path($image) : '',
            'imageWidth' => (int)($card['image_width'] ?? 40),
            'imageBack' => $imageVerso !== '' ? base_path($imageVerso) : '',
            'imageBackWidth' => (int)($card['image_back_width'] ?? 40),
        ];
    }, $cards);
    page_head($deck['title'] ?? 'Flashcards', '', ['accent' => (string)($deck['accent'] ?? '')]);
    site_header();
    if ($preview && !empty($deck['id'])) admin_preview_banner(base_path('admin/chapitre.php?chapter_id=' . (int)$deck['chapter_id'] . '&tab=flashcards&edit=' . (int)$deck['id']));
    ?>
    <main class="page-container student-page" style="--accent:<?= h($deck['accent'] ?? '#2563eb') ?>">
      <a class="nav-link" href="<?= h(base_path('flashcards.php')) ?>">← Toutes les flashcards</a>
      <section class="work-global-hero mt-5">
        <span class="badge"><?= h(($deck['level_short'] ?? '') . (!empty($deck['chapter_title']) ? ' · ' . $deck['chapter_title'] : '')) ?></span>
        <h1 class="page-title mt-4"><?= h($deck['title']) ?></h1>
        <p class="lib-hero-copy"><?= count($cards) ?> carte<?= count($cards) > 1 ? 's' : '' ?> de révision. Retourne la carte, réponds mentalement, puis évalue-toi honnêtement.</p>
      </section>
      <?php if ($cards): ?>
        <section class="flashcard-study-shell mt-8" data-interactive-flashcards aria-label="Révision des flashcards">
          <script type="application/json" class="flash-data"><?= json_encode($flashData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
        </section>
        <noscript><p class="work-empty mt-8">JavaScript doit être activé pour utiliser le mode de révision.</p></noscript>
      <?php else: ?>
        <div class="student-empty mt-8">
          <h2>Aucune carte publiée</h2>
          <p>Cette série de flashcards est encore vide.</p>
          <a class="btn-secondary" href="<?= h(base_path('flashcards.php')) ?>">Voir les autres séries</a>
        </div>
      <?php endif; ?>
    </main>
    <?php if ($cards): ?><script src="<?= h(base_path('assets/js/interactive-flashcards.js?v=124')) ?>" defer></script><?php endif; ?>
    <?php site_footer(); exit;
}

$filters = ['level' => trim($_GET['level'] ?? ''), 'q' => trim($_GET['q'] ?? '')];
$decks = get_flashcard_decks($filters);
$hasFilters = $filters['level'] !== '' || $filters['q'] !== '';
page_head('Flashcards');
site_header();
?>
<main class="page-container student-page activity-library-page">
  <a class="nav-link" href="<?= h(base_path('index.php')) ?>">← Accueil</a>
  <section class="work-global-hero mt-5">
    <span class="badge">Mémorisation</span>
    <h1 class="page-title mt-4">Choisir un chapitre</h1>
    <p class="lib-hero-copy">Sélectionne un chapitre, retourne chaque carte puis indique si tu as réussi.</p>
    <p class="library-count"><?= count($decks) ?> série<?= count($decks) > 1 ? 's' : '' ?> de cartes</p>
  </section>
  <form method="get" class="work-filter-panel mt-8">
    <div class="work-filter-grid">
      <label>Niveau<select name="level"><option value="">Tous</option><?php foreach ($levels as $level): ?><option value="<?= h($level['slug']) ?>" <?= ($filters['level'] === $level['slug']) ? 'selected' : '' ?>><?= h($level['short'] . ' — ' . $level['name']) ?></option><?php endforeach; ?></select></label>
      <label>Recherche<input type="search" name="q" value="<?= h($filters['q']) ?>" placeholder="Notion, chapitre…"></label>
      <div class="filter-submit"><button class="btn-primary" type="submit">Filtrer</button></div>
    </div>
    <?php if ($hasFilters): ?><a class="filter-reset" href="<?= h(base_path('flashcards.php')) ?>">Réinitialiser les filtres</a><?php endif; ?>
  </form>
  <section class="chapter-grid mt-8" aria-labelledby="deck-list-title">
    <h2 id="deck-list-title" class="sr-only">Liste des séries de flashcards</h2>
    <?php foreach ($decks as $deck): ?>
      <article class="chapter-tile" data-card style="--card-accent:<?= h($deck['accent']) ?>">
        <span class="chapter-number"><?= h($deck['level_short']) ?> · <?= (int)$deck['card_count'] ?> carte<?= (int)$deck['card_count'] > 1 ? 's' : '' ?></span>
        <h3><?= h($deck['title']) ?></h3>
        <p><?= h($deck['chapter_title'] ?: 'Révision générale') ?></p>
        <div class="chapter-actions"><a href="<?= h(base_path('flashcards.php?slug=' . $deck['slug'])) ?>">Réviser ce chapitre</a></div>
      </article>
    <?php endforeach; ?>
  </section>
  <?php if (!$decks): ?>
    <div class="student-empty mt-8">
      <h2>Aucune série de flashcards</h2>
      <p><?= $hasFilters ? 'Aucune série ne correspond à ces filtres.' : 'Les séries seront publiées au fil des chapitres.' ?></p>
      <?php if ($hasFilters): ?><a class="btn-secondary" href="<?= h(base_path('flashcards.php')) ?>">Voir toutes les séries</a><?php endif; ?>
    </div>
  <?php endif; ?>
</main>
<?php site_footer(); ?>
