<?php
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/../includes/analytics.php';

$periodes = [7 => '7 jours', 30 => '30 jours', 90 => '90 jours', 0 => 'Tout'];
$jours = (int)($_GET['periode'] ?? 30);
if (!array_key_exists($jours, $periodes)) $jours = 30;

$bilan = visites_bilan($jours);
$totalPays = max(1, array_sum(array_map(static fn($p) => (int)$p['n'], $bilan['pays'])));
$maxJour = 1;
foreach ($bilan['jours'] as $j) $maxJour = max($maxJour, (int)$j['n']);

// Série complète sur 14 jours (les jours sans visite doivent apparaître).
$serie = [];
$index = [];
foreach ($bilan['jours'] as $j) $index[(string)$j['jour']] = (int)$j['n'];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $serie[] = ['jour' => $date, 'n' => $index[$date] ?? 0];
}

page_head('Trafic du site', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>

  <section class="work-global-hero mt-5">
    <h1 class="page-title mt-4">Trafic du site</h1>
    <p class="muted">Nombre de visites et pays des visiteurs. Les robots d'indexation sont exclus du décompte.</p>
  </section>

  <nav class="trafic-periodes mt-6" aria-label="Période">
    <?php foreach ($periodes as $valeur => $libelle): ?>
      <a class="trafic-periode<?= $jours === $valeur ? ' is-active' : '' ?>" href="trafic.php?periode=<?= (int)$valeur ?>"><?= h($libelle) ?></a>
    <?php endforeach; ?>
  </nav>

  <section class="stat-grid mt-6" aria-label="Chiffres clés">
    <div class="stat-card"><span class="stat-value"><?= number_format($bilan['total'], 0, ',', ' ') ?></span><span class="stat-label">Visites au total</span></div>
    <div class="stat-card"><span class="stat-value"><?= number_format($bilan['aujourdhui'], 0, ',', ' ') ?></span><span class="stat-label">Aujourd'hui</span></div>
    <div class="stat-card"><span class="stat-value"><?= number_format($bilan['periode'], 0, ',', ' ') ?></span><span class="stat-label">Sur <?= h(mb_strtolower($periodes[$jours])) ?></span></div>
    <div class="stat-card"><span class="stat-value"><?= number_format($bilan['uniques'], 0, ',', ' ') ?></span><span class="stat-label">Visiteurs différents</span></div>
  </section>

  <section class="card mt-6 trafic-bloc">
    <h2 class="section-title">14 derniers jours</h2>
    <div class="trafic-chart" role="img" aria-label="Visites par jour sur les 14 derniers jours">
      <?php foreach ($serie as $point): $hauteur = (int)round(($point['n'] / $maxJour) * 100); ?>
        <div class="trafic-col" title="<?= h(date('d/m', strtotime($point['jour']))) ?> : <?= (int)$point['n'] ?> visite<?= $point['n'] > 1 ? 's' : '' ?>">
          <span class="trafic-bar" style="height:<?= max(2, $hauteur) ?>%"></span>
          <span class="trafic-jour"><?= h(date('d/m', strtotime($point['jour']))) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <div class="trafic-colonnes mt-6">
    <section class="card trafic-bloc">
      <h2 class="section-title">Pays des visiteurs</h2>
      <?php if (!$bilan['pays']): ?>
        <p class="muted">Aucune visite enregistrée sur cette période.</p>
      <?php else: ?>
        <table class="trafic-table">
          <thead><tr><th>Pays</th><th class="trafic-num">Visites</th><th class="trafic-num">Part</th></tr></thead>
          <tbody>
          <?php foreach ($bilan['pays'] as $ligne): $part = (int)round(((int)$ligne['n'] / $totalPays) * 100); ?>
            <tr>
              <td><span class="trafic-drapeau" aria-hidden="true"><?= geoip_drapeau((string)$ligne['country']) ?></span> <?= h(geoip_nom((string)$ligne['country'])) ?></td>
              <td class="trafic-num"><?= number_format((int)$ligne['n'], 0, ',', ' ') ?></td>
              <td class="trafic-num"><span class="trafic-part"><span style="width:<?= $part ?>%"></span></span><?= $part ?> %</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <section class="card trafic-bloc">
      <h2 class="section-title">Pages les plus vues</h2>
      <?php if (!$bilan['pages']): ?>
        <p class="muted">Aucune donnée sur cette période.</p>
      <?php else: ?>
        <table class="trafic-table">
          <thead><tr><th>Page</th><th class="trafic-num">Vues</th></tr></thead>
          <tbody>
          <?php foreach ($bilan['pages'] as $ligne): ?>
            <tr><td class="trafic-page"><?= h((string)$ligne['path']) ?></td><td class="trafic-num"><?= number_format((int)$ligne['n'], 0, ',', ' ') ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </div>

  <?php if ($bilan['referants']): ?>
  <section class="card mt-6 trafic-bloc">
    <h2 class="section-title">Sites d'origine</h2>
    <table class="trafic-table">
      <thead><tr><th>Site</th><th class="trafic-num">Visites</th></tr></thead>
      <tbody>
      <?php foreach ($bilan['referants'] as $ligne): ?>
        <tr><td><?= h((string)$ligne['referrer']) ?></td><td class="trafic-num"><?= number_format((int)$ligne['n'], 0, ',', ' ') ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
  <?php endif; ?>

  <p class="muted trafic-note">Les adresses IP ne sont pas conservées : seule une empreinte anonyme permet de compter les visiteurs différents. La localisation s'appuie sur une table de pays embarquée (aucun appel à un service externe).</p>
</main>
</body>
</html>
