<?php
require_once __DIR__ . '/includes/repository.php';

$filters = [
    'q' => trim((string)($_GET['q'] ?? '')),
    'domain' => trim((string)($_GET['domain'] ?? '')),
];
$methods = get_methods(false, $filters['q']);
if ($filters['domain'] !== '') {
    $methods = array_values(array_filter($methods, static fn($m) => ($m['domain'] ?? '') === $filters['domain']));
}
$domains = method_domains();
$hasFilters = $filters['q'] !== '' || $filters['domain'] !== '';

page_head('Méthodes de travail', 'Fiches méthodes : apprendre à apprendre, lire un graphique, convertir des unités, rédiger une réponse scientifique…');
site_header();
?>
<main id="main-content" class="page-container student-page methods-library-page">
  <a class="nav-link" href="<?= h(base_path('index.php')) ?>">← Accueil</a>

  <section class="work-global-hero mt-5">
    <span class="badge">Boîte à outils</span>
    <h1 class="page-title mt-4">Méthodes de travail</h1>
    <p class="lib-hero-copy">Des fiches courtes et réutilisables : comment apprendre, lire un graphique, convertir des unités, rédiger une réponse scientifique, utiliser Python…</p>
    <p class="library-count"><?= count($methods) ?> fiche<?= count($methods) > 1 ? 's' : '' ?> <?= $hasFilters ? 'correspondant aux filtres' : 'disponibles' ?></p>
  </section>

  <form method="get" class="work-filter-panel mt-8">
    <div class="work-filter-grid">
      <label>Rechercher
        <input type="search" name="q" value="<?= h($filters['q']) ?>" placeholder="Graphique, unités, formule, Python…">
      </label>
      <label>Domaine
        <select name="domain">
          <option value="">Tous</option>
          <?php foreach ($domains as $domain): ?>
            <option value="<?= h($domain) ?>" <?= $filters['domain'] === $domain ? 'selected' : '' ?>><?= h($domain) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="filter-submit"><button class="btn-primary" type="submit">Filtrer</button></div>
    </div>
    <?php if ($hasFilters): ?><a class="filter-reset" href="<?= h(base_path('methodes.php')) ?>">Réinitialiser les filtres</a><?php endif; ?>
  </form>

  <section class="methods-grid mt-8">
    <?php foreach ($methods as $method): ?>
      <a class="method-card" href="<?= h(base_path('methode.php?slug=' . urlencode((string)$method['slug']))) ?>"
         style="--method-accent:<?= h($method['accent'] ?: '#2563eb') ?>">
        <?php if (!empty($method['icon'])): ?><span class="method-icon" aria-hidden="true"><?= h($method['icon']) ?></span><?php endif; ?>
        <div>
          <span class="method-tag"><?= h($method['domain'] ?: 'Méthode générale') ?></span>
          <h2><?= h($method['title']) ?></h2>
          <p class="muted"><?= h($method['summary']) ?></p>
          <?php if (!empty($method['levels'])): ?><p class="method-levels"><?= h($method['levels']) ?></p><?php endif; ?>
        </div>
        <span class="method-open">Ouvrir la méthode <span aria-hidden="true">→</span></span>
      </a>
    <?php endforeach; ?>
  </section>

  <?php if (!$methods): ?>
    <div class="student-empty mt-8">
      <h2>Aucune fiche ne correspond</h2>
      <p><?= $hasFilters ? 'Essayez un autre mot-clé ou retirez les filtres.' : 'Les fiches méthodes seront publiées prochainement.' ?></p>
      <?php if ($hasFilters): ?><a class="btn-secondary" href="<?= h(base_path('methodes.php')) ?>">Voir toutes les fiches</a><?php endif; ?>
    </div>
  <?php endif; ?>
</main>
<?php site_footer(); ?>
