<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = db();
$uploadsDir = dirname(__DIR__) . '/uploads';

function system_status(bool $ok, string $good = 'Opérationnel', string $bad = 'À corriger'): array {
    return ['ok' => $ok, 'label' => $ok ? $good : $bad];
}

$databaseStatus = system_status($pdo instanceof PDO, 'Connectée', 'Non connectée');
$pdoMysqlStatus = system_status(extension_loaded('pdo_mysql'), 'Disponible', 'Extension absente');
$zipStatus = system_status(class_exists('ZipArchive'), 'Disponible', 'Extension absente');
$uploadsStatus = system_status(is_dir($uploadsDir), 'Trouvé', 'Dossier absent');
$writeStatus = system_status(is_dir($uploadsDir) && is_writable($uploadsDir), 'Autorisé', 'Écriture refusée');
$configStatus = system_status(
    is_file(dirname(__DIR__) . '/includes/config.php') && !empty(app_config()['db_name']) && !empty(app_config()['db_user']),
    'Renseignée',
    'Incomplète'
);

$mysqlVersion = 'Indisponible';
$tableCount = 0;
$v64Schema = admin_ensure_v69_schema();
$resourceSchemaStatus = system_status($v64Schema['ok'], 'Disponible', 'Mise à niveau requise');
if ($pdo) {
    try {
        $mysqlVersion = (string)$pdo->query('SELECT VERSION()')->fetchColumn();
        $tableCount = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'")->fetchColumn();
    } catch (Throwable $e) {}
}

$checks = [
    ['title' => 'Connexion MySQL', 'detail' => $mysqlVersion, 'status' => $databaseStatus],
    ['title' => 'Pilote PHP MySQL', 'detail' => 'Nécessaire pour lire les contenus', 'status' => $pdoMysqlStatus],
    ['title' => 'Configuration', 'detail' => 'Paramètres de la base de données', 'status' => $configStatus],
    ['title' => 'Dossier uploads', 'detail' => $uploadsDir, 'status' => $uploadsStatus],
    ['title' => 'Envoi de fichiers', 'detail' => 'Autorisation d’écriture dans uploads', 'status' => $writeStatus],
    ['title' => 'Archives ZIP', 'detail' => 'Sauvegarde complète et fichiers', 'status' => $zipStatus],
    ['title' => 'Structure éditoriale', 'detail' => 'Pilotage, publication groupée et ordre manuel', 'status' => $resourceSchemaStatus],
];

$allRequiredOk = $databaseStatus['ok'] && $pdoMysqlStatus['ok'] && $configStatus['ok'] && $uploadsStatus['ok'] && $writeStatus['ok'] && $resourceSchemaStatus['ok'];

page_head('Admin - État du système', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
    <a class="btn-secondary" href="sauvegarde.php">Sauvegardes</a>
  </div>

  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin · V73</span>
    <h1 class="page-title mt-4">État du système</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Vérifiez en un coup d’œil si l’hébergement peut faire fonctionner le site, les fichiers et les sauvegardes.</p>
  </section>

  <section class="system-summary <?= $allRequiredOk ? 'is-ready' : 'needs-attention' ?> mt-8" role="status">
    <span class="system-summary-icon" aria-hidden="true"><?= $allRequiredOk ? '✓' : '!' ?></span>
    <div>
      <h2><?= $allRequiredOk ? 'Le site est prêt' : 'Une vérification est nécessaire' ?></h2>
      <p><?= $allRequiredOk ? 'Les fonctions essentielles sont disponibles sur cet hébergement.' : 'Consultez les éléments signalés ci-dessous avant la mise en ligne.' ?></p>
    </div>
  </section>

  <section class="system-check-grid mt-8">
    <?php foreach ($checks as $check): ?>
      <article class="system-check card p-5">
        <div class="flex items-start justify-between gap-3">
          <h2><?= h($check['title']) ?></h2>
          <span class="status-pill <?= $check['status']['ok'] ? 'is-success' : 'is-error' ?>"><?= h($check['status']['label']) ?></span>
        </div>
        <p><?= h($check['detail']) ?></p>
      </article>
    <?php endforeach; ?>
  </section>

  <section class="card mt-8 p-6">
    <h2 class="section-title">Informations utiles</h2>
    <div class="system-info-grid mt-5">
      <div><span>Version PHP</span><strong><?= h(PHP_VERSION) ?></strong></div>
      <div><span>Version MySQL</span><strong><?= h($mysqlVersion) ?></strong></div>
      <div><span>Tables détectées</span><strong><?= (int)$tableCount ?></strong></div>
      <div><span>Taille maximale d’un fichier</span><strong><?= h((string)ini_get('upload_max_filesize')) ?></strong></div>
      <div><span>Taille maximale d’un formulaire</span><strong><?= h((string)ini_get('post_max_size')) ?></strong></div>
      <div><span>Mémoire PHP</span><strong><?= h((string)ini_get('memory_limit')) ?></strong></div>
    </div>
  </section>

  <?php if (!$zipStatus['ok']): ?>
    <section class="notice-warning mt-8">
      <strong>ZipArchive n’est pas indispensable au site public.</strong>
      <p>Les exports JSON restent disponibles. Demandez à l’hébergeur d’activer l’extension ZIP pour télécharger une sauvegarde complète.</p>
    </section>
  <?php endif; ?>
  <?php if (!$resourceSchemaStatus['ok']): ?>
    <section class="notice-warning mt-8"><strong>La structure éditoriale n'est pas encore disponible.</strong><p><?= h($v64Schema['message']) ?></p></section>
  <?php endif; ?>
</main>
</body>
</html>
