<?php
require_once __DIR__ . '/includes/repository.php';
page_head('Hors connexion', 'Les pages déjà visitées restent disponibles hors connexion.', ['math' => false, 'robots' => false]);
site_header();
?>
<main class="page-container student-page" id="main-content">
  <section class="student-empty mt-8 notfound">
    <p class="notfound-code" aria-hidden="true">⌁</p>
    <h1>Mode hors connexion</h1>
    <p>Tu n’as plus de réseau. Les pages déjà consultées restent accessibles ; les nouvelles ressources se chargeront au retour de la connexion.</p>
    <div class="notfound-actions">
      <a class="btn-primary" href="<?= h(base_path('index.php')) ?>">Retour à l’accueil</a>
      <a class="btn-secondary" href="<?= h(base_path('methodes.php')) ?>">Fiches méthodes</a>
      <a class="btn-secondary" href="<?= h(base_path('travail-a-faire.php')) ?>">Travail à faire</a>
    </div>
  </section>
</main>
<?php site_footer(); ?>
