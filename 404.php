<?php
require_once __DIR__ . '/includes/repository.php';

http_response_code(404);
$levels = get_levels();
page_head('Page introuvable', 'Cette page n’existe pas ou a été déplacée.', ['math' => false, 'robots' => false]);
site_header();
?>
<main id="main-content" class="page-container student-page">
  <section class="notfound">
    <p class="notfound-code" aria-hidden="true">404</p>
    <h1 class="page-title">Cette page n’existe pas</h1>
    <p class="notfound-copy">Le lien est peut-être ancien, ou l’adresse contient une faute de frappe. Voici par où continuer :</p>

    <div class="notfound-actions">
      <a class="btn-primary" href="<?= h(base_path('index.php')) ?>">Retour à l’accueil</a>
      <a class="btn-secondary" href="<?= h(base_path('methodes.php')) ?>">Fiches méthodes</a>
      <a class="btn-secondary" href="<?= h(base_path('travail-a-faire.php')) ?>">Travail à faire</a>
    </div>

    <h2 class="notfound-heading">Accès direct à un niveau</h2>
    <ul class="notfound-levels">
      <?php foreach ($levels as $level): ?>
        <li><a href="<?= h(base_path('niveau.php?slug=' . $level['slug'])) ?>" style="--accent:<?= h($level['accent']) ?>"><strong><?= h($level['short']) ?></strong><span><?= h($level['name']) ?></span></a></li>
      <?php endforeach; ?>
    </ul>

    <p class="notfound-help">Besoin d’un document précis ? Utilise la recherche de l’accueil ou passe par le chapitre correspondant.</p>
  </section>
</main>
<?php site_footer(); ?>
