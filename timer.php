<?php
// Le minuteur fait desormais partie de l'espace « Outils de classe ».
require_once __DIR__ . '/includes/repository.php';
$target = base_path('outils.php') . '#minuteur';
if (!headers_sent()) {
    header('Location: ' . $target, true, 302);
}
page_head('Minuteur de classe', 'Minuteur et outils de classe.', ['math' => false, 'robots' => false]);
site_header();
?>
<main id="main-content" class="page-container student-page">
  <section class="student-empty mt-8">
    <h1>Le minuteur a déménagé</h1>
    <p>Le minuteur, la formation de groupes, le tirage au sort et les consignes sont maintenant réunis dans les outils de classe.</p>
    <a class="btn-primary" href="<?= h(base_path('outils.php#minuteur')) ?>">Ouvrir les outils de classe</a>
  </section>
</main>
<?php site_footer(); ?>
