<?php
/**
 * Prépare une archive ZIP prête à envoyer sur l'hébergement.
 *
 *   php tools/build-deploy.php
 *
 * Produit `site-a-envoyer.zip` à la racine du projet, contenant uniquement les
 * fichiers utiles en production :
 *   • tous les .php de la racine ;
 *   • .htaccess, favicon.ico, manifest.webmanifest ;
 *   • assets/, dist/, includes/, data/.
 *
 * Exclus volontairement :
 *   • apercus/ (captures), src/ (sources CSS), tools/ (maintenance),
 *     database/ (scripts SQL), tous les .md ;
 *   • includes/config.local.php (identifiants locaux : ne doit jamais partir) ;
 *   • les fichiers d'exemple de configuration.
 *
 * Intérêt : un seul envoi FTP au lieu d'une centaine, ce qui évite les
 * dépassements de connexions simultanées des hébergeurs mutualisés.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("À lancer en ligne de commande : php tools/build-deploy.php\n");
}

$racine = realpath(__DIR__ . '/..');

/* Option --sans-config : on retire includes/config.php de l'archive.
   Indispensable pour une MISE A JOUR : sinon les identifiants MySQL du serveur
   seraient remplaces par ceux d'exemple, et le site perdrait sa base. */
$sansConfig = in_array('--sans-config', $argv ?? [], true);
$sortie = $racine . ($sansConfig ? '/site-complet.zip' : '/site-a-envoyer.zip');

/** Fichiers à ne jamais inclure, où qu'ils soient. */
$exclus = [
    'config.local.php',
    'config.example.php',
    'config.local.example.php',
    '.DS_Store',
    '.gitignore',
];
if ($sansConfig) $exclus[] = 'config.php';

/** Extensions inutiles en production. */
$extensionsExclues = ['md', 'sql', 'zip', 'log', 'bak'];

$dossiers = ['assets', 'dist', 'includes', 'data'];

$zip = new ZipArchive();
if (file_exists($sortie)) unlink($sortie);
if ($zip->open($sortie, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Impossible de créer l'archive.\n");
    exit(1);
}

$ajoutes = 0;
$ignores = 0;

$doitInclure = static function (string $chemin) use ($exclus, $extensionsExclues): bool {
    $nom = basename($chemin);
    if (in_array($nom, $exclus, true)) return false;
    $ext = strtolower(pathinfo($nom, PATHINFO_EXTENSION));
    if (in_array($ext, $extensionsExclues, true)) return false;
    return true;
};

/* 1. Racine : uniquement les fichiers utiles. */
foreach (scandir($racine) as $entree) {
    if ($entree === '.' || $entree === '..') continue;
    $chemin = $racine . '/' . $entree;
    if (!is_file($chemin)) continue;
    if (!$doitInclure($chemin)) { $ignores++; continue; }
    $ext = strtolower(pathinfo($entree, PATHINFO_EXTENSION));
    // .js ajouté : service-worker.js vit à la racine et doit partir avec le site.
    $autorise = in_array($ext, ['php', 'ico', 'webmanifest', 'json', 'js', 'txt'], true) || $entree === '.htaccess';
    if (!$autorise) { $ignores++; continue; }
    $zip->addFile($chemin, $entree);
    $ajoutes++;
}

/* 2. Dossiers applicatifs. */
foreach ($dossiers as $dossier) {
    $base = $racine . '/' . $dossier;
    if (!is_dir($base)) continue;
    $iterateur = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterateur as $fichier) {
        if (!$fichier->isFile()) continue;
        $chemin = $fichier->getPathname();
        if (!$doitInclure($chemin)) { $ignores++; continue; }
        $zip->addFile($chemin, str_replace($racine . '/', '', $chemin));
        $ajoutes++;
    }
}

$zip->close();

printf("Archive   : %s\n", basename($sortie));
printf("Fichiers  : %d inclus, %d ignorés\n", $ajoutes, $ignores);
printf("Taille    : %.0f Ko\n", filesize($sortie) / 1024);
echo $sansConfig
    ? "Mise à jour : includes/config.php N'EST PAS dans l'archive (identifiants préservés).\n"
    : "Installation neuve : renseigne includes/config.php après extraction.\n";
