<?php
/**
 * Régénère la table de localisation `data/geoip-country.bin`.
 *
 *   php tools/build-geoip.php
 *
 * Le script télécharge le jeu de données public GeoLite2 Country (IPv4, licence
 * CC BY-SA 4.0, mis à jour quotidiennement), puis le condense en une table de
 * 65 536 entrées : un code pays par bloc /16. Résultat : 128 Ko et une
 * résolution instantanée, sans appel réseau au moment de la visite.
 *
 * À relancer une à deux fois par an pour rester à jour.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("À lancer en ligne de commande : php tools/build-geoip.php\n");
}

$source = 'https://raw.githubusercontent.com/sapics/ip-location-db/main/geolite2-country/geolite2-country-ipv4.csv';
$temporaire = sys_get_temp_dir() . '/geoip-country.csv';
$sortie = __DIR__ . '/../data/geoip-country.bin';

echo "Source : {$source}\n";
echo "Téléchargement… ";
$contenu = @file_get_contents($source);
if ($contenu === false || strlen($contenu) < 100000) {
    fwrite(STDERR, "échec du téléchargement. Vérifie la connexion réseau.\n");
    exit(1);
}
file_put_contents($temporaire, $contenu);
printf("%.1f Mo\n", strlen($contenu) / 1048576);

$table = str_repeat("\0\0", 65536);
$lignes = 0;
$remplis = 0;

$poignee = fopen($temporaire, 'rb');
if (!$poignee) {
    fwrite(STDERR, "Impossible de lire le fichier temporaire.\n");
    exit(1);
}
while (($ligne = fgets($poignee)) !== false) {
    $parties = explode(',', trim($ligne));
    if (count($parties) < 3) continue;
    $debut = ip2long($parties[0]);
    $fin = ip2long($parties[1]);
    $code = strtoupper(trim($parties[2]));
    if ($debut === false || $fin === false || strlen($code) !== 2 || !ctype_alpha($code)) continue;
    $blocDebut = ($debut >> 16) & 0xFFFF;
    $blocFin = ($fin >> 16) & 0xFFFF;
    if ($blocFin < $blocDebut) continue;
    for ($bloc = $blocDebut; $bloc <= $blocFin; $bloc++) {
        $offset = $bloc * 2;
        if ($table[$offset] === "\0" && $table[$offset + 1] === "\0") $remplis++;
        // On écrit seulement les deux premiers caractères (bloc /16).
        $table[$offset] = $code[0];
        $table[$offset + 1] = $code[1];
    }
    $lignes++;
}
fclose($poignee);
@unlink($temporaire);

if (!is_dir(dirname($sortie))) mkdir(dirname($sortie), 0755, true);
file_put_contents($sortie, $table);

echo "Lignes lues      : {$lignes}\n";
echo "Blocs renseignés : {$remplis} / 65536\n";
echo 'Fichier écrit    : ' . $sortie . ' (' . filesize($sortie) . " octets)\n";
echo "Terminé.\n";
