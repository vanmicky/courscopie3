<?php
/**
 * Audit complet du site : pages, liens internes, ressources et erreurs PHP.
 *
 *   php tools/audit.php [url-de-base]
 *
 * Par défaut : http://localhost:8888/cours
 *
 * Ce script ne modifie rien. Il visite les pages publiques (déduites de la base
 * de données), suit les liens internes trouvés, vérifie que les ressources
 * (CSS, JS, images) répondent, et signale toute erreur PHP visible.
 *
 * Sortie : rapport lisible + code de sortie 1 si un problème est trouvé.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("À lancer en ligne de commande : php tools/audit.php\n");
}

require_once __DIR__ . '/../includes/repository.php';

$base = rtrim($argv[1] ?? 'http://localhost:8888/cours', '/');
echo "Audit de {$base}\n";
echo str_repeat('─', 60) . PHP_EOL;

/* -------------------------------------------------------------------------
   Récupération d'une URL
   ------------------------------------------------------------------------- */
function recuperer(string $url): array {
    $options = [
        'http' => ['method' => 'GET', 'timeout' => 15, 'ignore_errors' => true, 'follow_location' => 1, 'max_redirects' => 5],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ];
    $contexte = stream_context_create($options);
    $debut = microtime(true);
    $corps = @file_get_contents($url, false, $contexte);
    $duree = (int)round((microtime(true) - $debut) * 1000);
    $entetes = $http_response_header ?? [];
    $statut = 0;
    $type = '';
    foreach ($entetes as $entete) {
        if (preg_match('~^HTTP/\S+\s+(\d{3})~', $entete, $m)) $statut = (int)$m[1];
        if (stripos($entete, 'content-type:') === 0) $type = trim(substr($entete, 13));
    }
    return ['statut' => $statut, 'corps' => (string)$corps, 'type' => $type, 'ms' => $duree];
}

/* -------------------------------------------------------------------------
   Liste des pages à visiter
   ------------------------------------------------------------------------- */
$pages = ['/', '/index.php', '/methodes.php', '/outils.php', '/travail-a-faire.php', '/exercices.php', '/qcm.php', '/flashcards.php', '/404.php', '/offline.php'];

$pdo = db();
if ($pdo) {
    foreach ($pdo->query('SELECT slug FROM levels WHERE is_published=1') as $r) $pages[] = '/niveau.php?slug=' . urlencode($r['slug']);
    foreach ($pdo->query('SELECT slug FROM chapters WHERE is_published=1 ORDER BY id LIMIT 40') as $r) $pages[] = '/chapitre.php?slug=' . urlencode($r['slug']);
    foreach ($pdo->query('SELECT slug FROM methods LIMIT 20') as $r) $pages[] = '/methode.php?slug=' . urlencode($r['slug']);
    foreach ($pdo->query('SELECT slug FROM qcms LIMIT 10') as $r) $pages[] = '/qcm.php?slug=' . urlencode($r['slug']);
    foreach ($pdo->query('SELECT slug FROM flashcard_decks LIMIT 10') as $r) $pages[] = '/flashcards.php?slug=' . urlencode($r['slug']);
}

$pages = array_values(array_unique($pages));
echo 'Pages publiques à vérifier : ' . count($pages) . PHP_EOL . PHP_EOL;

$erreurs = [];
$lenteurs = [];
$liens = [];
$ressources = [];
$ok = 0;

foreach ($pages as $chemin) {
    $url = $base . $chemin;
    $reponse = recuperer($url);
    $statut = $reponse['statut'];
    $corps = $reponse['corps'];

    $probleme = '';
    if ($statut !== 200 && $chemin !== '/404.php') $probleme = 'HTTP ' . $statut;
    if (preg_match('/(Fatal error|Parse error|Warning:|Notice:|Deprecated:|Uncaught)/i', $corps, $m)) {
        $probleme = ($probleme ? $probleme . ' + ' : '') . 'PHP : ' . $m[0];
    }
    if ($reponse['ms'] > 2500) $lenteurs[] = sprintf('%s (%d ms)', $chemin, $reponse['ms']);

    if ($probleme !== '') {
        $erreurs[] = sprintf('%-52s %s', $chemin, $probleme);
        echo '  ✗ ' . $chemin . ' — ' . $probleme . PHP_EOL;
    } else {
        $ok++;
    }

    // Liens et ressources (uniquement pour les pages HTML valides)
    if ($statut === 200 && stripos($reponse['type'], 'html') !== false) {
        if (preg_match_all('/(?:href|src)="([^"]+)"/i', $corps, $trouves)) {
            foreach ($trouves[1] as $lien) {
                // Le HTML encode les esperluettes : &amp; doit redevenir &
                $lien = html_entity_decode($lien, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($lien === '' || $lien[0] === '#' || str_starts_with($lien, 'mailto:') || str_starts_with($lien, 'tel:')) continue;
                if (preg_match('~^https?://~i', $lien)) {
                    if (!str_starts_with($lien, $base)) continue;
                    $lien = substr($lien, strlen($base));
                }
                if (!str_starts_with($lien, '/') && !preg_match('~^https?:~', $lien)) {
                    $dossier = rtrim(str_replace('\\', '/', dirname(parse_url($chemin, PHP_URL_PATH) ?: '/')), '/');
                    $lien = ($dossier === '' ? '' : $dossier) . '/' . $lien;
                }
                $lien = preg_replace('~^\./~', '/', $lien);
                if (preg_match('~\.(css|js|woff2|webp|png|jpe?g|svg|ico|json|webmanifest)(\?|$)~i', $lien)) {
                    $ressources[$lien] = true;
                } else {
                    $liens[$lien] = true;
                }
            }
        }
    }
}

echo PHP_EOL . 'Pages conformes : ' . $ok . ' / ' . count($pages) . PHP_EOL;

/* -------------------------------------------------------------------------
   Liens internes
   ------------------------------------------------------------------------- */
echo PHP_EOL . 'Liens internes à vérifier : ' . count($liens) . PHP_EOL;
$liensCasses = [];
foreach (array_keys($liens) as $lien) {
    if (preg_match('~^https?:~', $lien)) continue;
    $reponse = recuperer($base . $lien);
    if ($reponse['statut'] >= 400) {
        $liensCasses[] = sprintf('%-52s HTTP %d', $lien, $reponse['statut']);
        echo '  ✗ ' . $lien . ' → HTTP ' . $reponse['statut'] . PHP_EOL;
    }
}
if (!$liensCasses) echo '  ✓ aucun lien cassé' . PHP_EOL;

/* -------------------------------------------------------------------------
   Ressources
   ------------------------------------------------------------------------- */
// Polices declarees dans le CSS de KaTeX : elles ne figurent pas dans le HTML.
$cssKatex = __DIR__ . '/../assets/vendor/katex/katex.min.css';
if (is_file($cssKatex)) {
    $contenuCss = (string)file_get_contents($cssKatex);
    if (preg_match_all('~url\((fonts/[^)]+)\)~', $contenuCss, $polices)) {
        foreach (array_unique($polices[1]) as $police) {
            $ressources['/assets/vendor/katex/' . $police] = true;
        }
    }
}

echo PHP_EOL . 'Ressources à vérifier : ' . count($ressources) . PHP_EOL;
$ressourcesCassees = [];
foreach (array_keys($ressources) as $ressource) {
    if (preg_match('~^https?:~', $ressource)) continue;
    $reponse = recuperer($base . $ressource);
    if ($reponse['statut'] >= 400) {
        $ressourcesCassees[] = sprintf('%-52s HTTP %d', $ressource, $reponse['statut']);
        echo '  ✗ ' . $ressource . ' → HTTP ' . $reponse['statut'] . PHP_EOL;
    }
}
if (!$ressourcesCassees) echo '  ✓ aucune ressource manquante' . PHP_EOL;

/* -------------------------------------------------------------------------
   Bilan
   ------------------------------------------------------------------------- */
echo PHP_EOL . str_repeat('─', 60) . PHP_EOL;
if ($lenteurs) {
    echo 'Pages lentes (> 2,5 s) :' . PHP_EOL;
    foreach ($lenteurs as $ligne) echo '  ! ' . $ligne . PHP_EOL;
}
$total = count($erreurs) + count($liensCasses) + count($ressourcesCassees);
echo $total === 0
    ? 'Aucun problème détecté.' . PHP_EOL
    : $total . ' problème(s) détecté(s).' . PHP_EOL;

exit($total === 0 ? 0 : 1);
