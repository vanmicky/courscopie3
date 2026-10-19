<?php
/**
 * Diagnostic de déploiement.
 *
 * Ouvre cette page dans le navigateur après avoir envoyé le site :
 *
 *   https://ton-domaine.fr/diag.php
 *
 * Elle vérifie la configuration, les fichiers, les droits et l'accès HTTP aux
 * ressources, puis indique quoi corriger.
 *
 * ⚠️ SUPPRIME CE FICHIER UNE FOIS LE SITE EN LIGNE.
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$resultats = [];
function test(string $titre, bool $ok, string $detail = '', string $conseil = ''): void {
    global $resultats;
    $resultats[] = ['titre' => $titre, 'ok' => $ok, 'detail' => $detail, 'conseil' => $conseil];
}

$racine = __DIR__;

/* ------------------------------------------------------------------ */
/* 1. Environnement PHP                                               */
/* ------------------------------------------------------------------ */
test('PHP 8.0 ou supérieur', version_compare(PHP_VERSION, '8.0', '>='), PHP_VERSION, 'Demande PHP 8.1 ou 8.2 à l’hébergeur.');
foreach (['pdo_mysql' => 'accès à la base', 'mbstring' => 'textes accentués', 'fileinfo' => 'types de fichiers'] as $ext => $role) {
    test('Extension ' . $ext, extension_loaded($ext), $role, 'Active l’extension ' . $ext . ' dans le panneau de l’hébergeur.');
}

/* ------------------------------------------------------------------ */
/* 2. Configuration                                                   */
/* ------------------------------------------------------------------ */
$configLocal = $racine . '/includes/config.local.php';
$config = is_file($configLocal) ? (require $configLocal) : (is_file($racine . '/includes/config.php') ? require $racine . '/includes/config.php' : []);
$source = is_file($configLocal) ? 'config.local.php' : 'config.php';
test('Fichier de configuration trouvé', !empty($config), 'source : ' . $source);
test('Nom de la base renseigné', !empty($config['db_name']) && $config['db_name'] !== 'mysql', (string)($config['db_name'] ?? '—'), 'Édite includes/config.php : db_name = nom de ta base.');

$baseUrl = (string)($config['base_url'] ?? '');
test('base_url', true, $baseUrl === '' ? 'vide (chemins relatifs : recommandé)' : $baseUrl);

/* ------------------------------------------------------------------ */
/* 3. Fichiers indispensables                                         */
/* ------------------------------------------------------------------ */
$fichiers = [
    '.htaccess' => 'réécriture, cache, sécurité',
    'index.php' => 'page d’accueil',
    'dist/output.css' => 'TOUTE la mise en forme du site',
    'assets/js/main.js' => 'interactions',
    'includes/db.php' => 'connexion base',
    'includes/helpers.php' => 'gabarit',
    'assets/vendor/katex/katex.min.css' => 'formules mathématiques',
    'data/geoip-country.bin' => 'pays des visiteurs',
];
foreach ($fichiers as $chemin => $role) {
    $complet = $racine . '/' . $chemin;
    $existe = is_file($complet);
    $taille = $existe ? filesize($complet) : 0;
    test(
        $chemin,
        $existe && $taille > 0,
        $existe ? number_format($taille, 0, ',', ' ') . ' octets' : 'ABSENT',
        'Envoie le fichier manquant (il est dans site-a-envoyer.zip).'
    );
}

/* ------------------------------------------------------------------ */
/* 4. CSS : le fichier est-il servi correctement ?                    */
/* ------------------------------------------------------------------ */
$css = $racine . '/dist/output.css';
if (is_file($css)) {
    $lecture = @file_get_contents($css, false, null, 0, 200);
    test('CSS lisible par PHP', $lecture !== false && $lecture !== '', 'lecture ' . ($lecture === false ? 'impossible' : 'ok'), 'Vérifie les droits : chmod 644 sur les fichiers, 755 sur les dossiers.');
    test('CSS non vide', filesize($css) > 50000, number_format(filesize($css), 0, ',', ' ') . ' octets', 'Le fichier est probablement tronqué : renvoie-le.');
    $droits = substr(sprintf('%o', fileperms($css)), -4);
    test('Droits du CSS', is_readable($css), $droits, 'Les fichiers doivent être lisibles par le serveur web (644).');
}

/* ------------------------------------------------------------------ */
/* 5. Accès HTTP réel aux ressources                                  */
/* ------------------------------------------------------------------ */
$protocole = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$hote = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dossier = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$racineUrl = $protocole . '://' . $hote . ($dossier === '' ? '' : $dossier);

function verifierUrl(string $url): array {
    $options = ['http' => ['method' => 'GET', 'timeout' => 10, 'ignore_errors' => true]];
    $corps = @file_get_contents($url, false, stream_context_create($options));
    $entetes = $http_response_header ?? [];
    $statut = 0; $type = '';
    foreach ($entetes as $e) {
        if (preg_match('~^HTTP/\S+\s+(\d{3})~', $e, $m)) $statut = (int)$m[1];
        if (stripos($e, 'content-type:') === 0) $type = trim(substr($e, 13));
    }
    return ['statut' => $statut, 'type' => $type, 'taille' => strlen((string)$corps)];
}

$urlCss = $racineUrl . '/dist/output.css';
$http = verifierUrl($urlCss);
$typeOk = stripos($http['type'], 'css') !== false;
test('CSS servi en HTTP', $http['statut'] === 200, 'HTTP ' . $http['statut'] . ' — ' . $urlCss, 'Un 403 = droits du fichier. Un 404 = fichier absent ou mauvais dossier.');
test('Type MIME du CSS', $typeOk, $http['type'] ?: 'inconnu', 'Le serveur doit répondre text/css. Ajoute « AddType text/css .css » dans .htaccess si besoin.');
test('Taille du CSS servi', $http['taille'] > 50000, number_format($http['taille'], 0, ',', ' ') . ' octets reçus', 'Le serveur renvoie un fichier vide ou une page d’erreur.');
/* La vérification décisive : l'URL du CSS telle que la page l'écrit. */
$pageAccueil = verifierUrl($racineUrl . '/index.php');
$corpsAccueil = @file_get_contents($racineUrl . '/index.php', false, stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]));
$lienCss = '';
if (is_string($corpsAccueil) && preg_match('~<link[^>]+rel=["\']stylesheet["\'][^>]+href=["\']([^"\']+)["\']~i', $corpsAccueil, $m)) {
    $lienCss = $m[1];
}
if ($lienCss !== '') {
    $urlExacte = (str_starts_with($lienCss, 'http'))
        ? $lienCss
        : preg_replace('~/[^/]*$~', '/', $racineUrl . '/') . preg_replace('~^\./~', '', $lienCss);
    $cssExact = verifierUrl($urlExacte);
    test(
        'URL du CSS demandée par la page',
        $cssExact['statut'] === 200 && $cssExact['taille'] > 50000,
        $lienCss . ' → HTTP ' . $cssExact['statut'] . ', ' . number_format($cssExact['taille'], 0, ',', ' ') . ' octets',
        'C’est cette adresse que le navigateur appelle. Un 404 signifie que le dossier dist/ n’est pas au bon endroit.'
    );
} else {
    test('URL du CSS dans la page', false, 'aucun <link rel="stylesheet"> trouvé', 'La page d’accueil ne contient pas de lien CSS : vérifie index.php.');
}

$erreursPhp = is_string($corpsAccueil) && preg_match('/(Fatal error|Parse error|Warning:|Notice:)/i', $corpsAccueil);
test('La page d’accueil s’affiche sans erreur PHP', !$erreursPhp, $erreursPhp ? 'erreur PHP détectée' : 'ok', 'Active l’affichage des erreurs pour identifier la ligne concernée.');
/* Batterie de fichiers statiques : mode sur le disque + réponse HTTP. */
$statiques = [
    'dist/output.css' => 'text/css',
    'assets/js/main.js' => 'javascript',
    'assets/js/mobile-menu.js' => 'javascript',
    'assets/fonts/inter-var-latin.woff2' => 'font',
    'assets/vendor/katex/katex.min.css' => 'text/css',
    'data/geoip-country.bin' => 'octet-stream',
];
foreach ($statiques as $chemin => $typeAttendu) {
    $complet = $racine . '/' . $chemin;
    if (!is_file($complet)) {
        test('Statique : ' . $chemin, false, 'ABSENT du serveur', 'Le fichier n’a pas été envoyé : renvoie l’archive et vérifie qu’elle se décompresse à la racine.');
        continue;
    }
    $mode = substr(sprintf('%o', fileperms($complet)), -4);
    $lisible = is_readable($complet);
    $reponse = verifierUrl($racineUrl . '/' . $chemin);
    $ok = $reponse['statut'] === 200 && $reponse['taille'] > 0 && stripos($reponse['type'], 'html') === false;
    $detail = 'mode ' . $mode . ' — HTTP ' . $reponse['statut'] . ' — ' . ($reponse['type'] ?: 'type inconnu') . ' — ' . number_format($reponse['taille'], 0, ',', ' ') . ' o';
    $conseil = '';
    if (!$lisible || $reponse['statut'] === 403) {
        $conseil = 'Droits refusés : les fichiers doivent être en 644 et les dossiers en 755. Utilise l’outil « Permissions » du gestionnaire de fichiers.';
    } elseif ($reponse['statut'] === 404) {
        $conseil = 'Fichier absent à cette adresse : vérifie que l’archive a été extraite à la racine du site.';
    } elseif (stripos($reponse['type'], 'html') !== false) {
        $conseil = 'Le serveur renvoie une page HTML au lieu du fichier : problème de droits ou de réécriture.';
    }
    test('Statique : ' . $chemin, $ok, $detail, $conseil);
}

/* ------------------------------------------------------------------ */
/* 6. Écriture                                                        */
/* ------------------------------------------------------------------ */
$essai = $racine . '/assets/.test-ecriture';
$ecrit = @file_put_contents($essai, 'ok');
@unlink($essai);
test('Dossier assets/ accessible en écriture', $ecrit !== false, $ecrit !== false ? 'ok' : 'refusé', 'Nécessaire pour envoyer des documents (chmod 755).');

/* ------------------------------------------------------------------ */
/* 7. Base de données                                                 */
/* ------------------------------------------------------------------ */
try {
    require_once $racine . '/includes/db.php';
    $pdo = db();
    if ($pdo) {
        test('Connexion MySQL', true, (string)($config['db_name'] ?? ''));
        $tables = (int)$pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
        test('Tables installées', $tables >= 20, $tables . ' tables', 'Importe database/schema.sql dans phpMyAdmin.');
        $attendu = ['levels', 'subjects', 'chapters', 'admins', 'visits'];
        foreach ($attendu as $table) {
            $ok = (bool)$pdo->query("SHOW TABLES LIKE " . $pdo->quote($table))->fetch();
            test('Table ' . $table, $ok, $ok ? 'présente' : 'ABSENTE', 'Importe database/schema.sql.');
        }
        $admins = 0;
        try { $admins = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn(); } catch (Throwable $e) {}
        test('Compte administrateur', $admins > 0, $admins . ' compte(s)', 'Ouvre /admin/setup.php pour créer ton compte.');
    } else {
        test('Connexion MySQL', false, 'échec', 'Vérifie db_host, db_name, db_user, db_pass dans includes/config.php.');
    }
} catch (Throwable $e) {
    test('Connexion MySQL', false, $e->getMessage(), 'Vérifie les identifiants de includes/config.php.');
}

/* ------------------------------------------------------------------ */
/* 8. Configuration serveur                                           */
/* ------------------------------------------------------------------ */
test('HTTPS actif', $protocole === 'https', $protocole, 'Active le certificat SSL chez l’hébergeur.');
test('Page 404 personnalisée', is_file($racine . '/404.php'), '404.php', '');
test('Dossier data/ non listable', true, is_dir($racine . '/data') ? 'présent' : 'absent', 'Le .htaccess protège déjà l’accès direct.');

/* ------------------------------------------------------------------ */
/* Affichage                                                          */
/* ------------------------------------------------------------------ */
$echecs = array_filter($resultats, static fn($r) => !$r['ok']);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Diagnostic du déploiement</title>
<style>
  :root { color-scheme: light dark; }
  * { box-sizing: border-box; }
  body { margin: 0; padding: 2rem 1rem; font: 15px/1.55 -apple-system, "Segoe UI", Roboto, sans-serif; background: #f6f7f9; color: #14161a; }
  .wrap { max-width: 60rem; margin: 0 auto; }
  h1 { font-size: 1.5rem; margin: 0 0 .3rem; }
  .sub { color: #5b6270; margin: 0 0 1.5rem; }
  .bilan { padding: .9rem 1.1rem; border-radius: 10px; font-weight: 700; margin-bottom: 1.5rem; }
  .bilan.ok { background: #ecfdf5; border: 1px solid #86efac; color: #14532d; }
  .bilan.ko { background: #fef2f2; border: 1px solid #fca5a5; color: #7f1d1d; }
  table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e4e7ec; border-radius: 12px; overflow: hidden; }
  th, td { padding: .6rem .8rem; text-align: left; border-bottom: 1px solid #e4e7ec; vertical-align: top; }
  th { background: #f9fafb; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
  tr:last-child td { border-bottom: 0; }
  tr.ko { background: #fff7f7; }
  .etat { font-weight: 800; white-space: nowrap; }
  .ok .etat { color: #15803d; }
  .ko .etat { color: #b91c1c; }
  .aide { display: block; margin-top: .2rem; color: #b45309; font-size: .82rem; }
  code { font-family: ui-monospace, Menlo, monospace; font-size: .85em; background: #f2f4f7; padding: .1rem .3rem; border-radius: 4px; }
  .note { margin-top: 1.5rem; padding: .9rem 1.1rem; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 10px; color: #7c2d12; font-size: .9rem; }
  @media (prefers-color-scheme: dark) {
    body { background: #0b0c0f; color: #f3f4f6; }
    table { background: #131519; border-color: #242830; }
    th { background: #171a1f; color: #a8afbd; }
    td { border-color: #242830; }
    tr.ko { background: #1f1416; }
    code { background: #242830; }
  }
</style>
</head>
<body>
<div class="wrap">
  <h1>Diagnostic du déploiement</h1>
  <p class="sub">Adresse testée : <code><?= htmlspecialchars($racineUrl) ?></code></p>

  <p class="bilan <?= $echecs ? 'ko' : 'ok' ?>">
    <?= $echecs
        ? count($echecs) . ' point(s) à corriger — voir les lignes rouges ci-dessous.'
        : 'Tout est en ordre : le site est correctement déployé.' ?>
  </p>

  <table>
    <thead><tr><th>Vérification</th><th>Valeur</th></tr></thead>
    <tbody>
      <?php foreach ($resultats as $r): ?>
        <tr class="<?= $r['ok'] ? 'ok' : 'ko' ?>">
          <td>
            <span class="etat"><?= $r['ok'] ? '✓' : '✗' ?></span>
            <?= htmlspecialchars($r['titre']) ?>
            <?php if (!$r['ok'] && $r['conseil']): ?><span class="aide">→ <?= htmlspecialchars($r['conseil']) ?></span><?php endif; ?>
          </td>
          <td><?= htmlspecialchars($r['detail']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="note">
    <strong>Supprime ce fichier</strong> (<code>diag.php</code>) une fois le site en ligne :
    il révèle des informations sur ta configuration.
  </div>
</div>
</body>
</html>
