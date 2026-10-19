<?php
/**
 * Réinitialisation du mot de passe administrateur.
 *
 * À lancer en ligne de commande, depuis la racine du site :
 *
 *   php tools/reset-admin-password.php                      (demande les informations)
 *   php tools/reset-admin-password.php prof@exemple.fr
 *   php tools/reset-admin-password.php prof@exemple.fr --password="NouveauMotDePasse"
 *
 * Le script :
 *   • crée la table `admins` si elle n'existe pas encore ;
 *   • met à jour le mot de passe si l'adresse existe ;
 *   • crée le compte sinon ;
 *   • affiche le mot de passe à utiliser (ou celui qui a été généré).
 *
 * Utile quand le mot de passe est perdu : aucun accès à l'administration n'est
 * nécessaire puisqu'on passe par la base de données.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Ce script se lance en ligne de commande : php tools/reset-admin-password.php\n");
}

require_once __DIR__ . '/../includes/repository.php';

/* Analyse manuelle : getopt() s'arrête au premier argument positionnel, donc
   `php tools/... email --password=xxx` perdait l'option. */
$email = '';
$motDePasse = '';
$nom = 'M. Gedion';
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--password=')) {
        $motDePasse = substr($argument, strlen('--password='));
    } elseif (str_starts_with($argument, '--name=')) {
        $nom = substr($argument, strlen('--name='));
    } elseif ($argument !== '' && $argument[0] !== '-') {
        $email = $email === '' ? $argument : $email;
    }
}

/** Lit une saisie au clavier (sans écho quand c'est disponible). */
function demander(string $question, bool $cache = false): string
{
    fwrite(STDOUT, $question);
    if ($cache && DIRECTORY_SEPARATOR !== '\\' && function_exists('shell_exec')) {
        @shell_exec('stty -echo');
        $valeur = trim((string)fgets(STDIN));
        @shell_exec('stty echo');
        fwrite(STDOUT, "\n");
        return $valeur;
    }
    return trim((string)fgets(STDIN));
}

/** Mot de passe lisible mais solide. */
function generer_mot_de_passe(int $longueur = 16): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $sortie = '';
    for ($i = 0; $i < $longueur; $i++) {
        $sortie .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $sortie;
}

if ($email === '') {
    $email = demander('Adresse email de l’administrateur : ');
}
$email = strtolower(trim($email));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Adresse email invalide : « {$email} »\n");
    exit(1);
}

if ($motDePasse === '') {
    $motDePasse = generer_mot_de_passe();
    fwrite(STDOUT, "Mot de passe généré automatiquement.\n");
}

if (strlen($motDePasse) < 10) {
    fwrite(STDERR, "Le mot de passe doit contenir au moins 10 caractères.\n");
    exit(1);
}

$pdo = db();
if (!$pdo) {
    fwrite(STDERR, "Connexion MySQL impossible. Vérifiez includes/config.php (ou includes/config.local.php).\n");
    exit(1);
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(120) NOT NULL DEFAULT 'Admin',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    fwrite(STDERR, "Impossible de préparer la table admins : " . $e->getMessage() . "\n");
    exit(1);
}

$hash = password_hash($motDePasse, PASSWORD_DEFAULT);

try {
    $recherche = $pdo->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
    $recherche->execute([$email]);
    $existant = $recherche->fetch();

    if ($existant) {
        $maj = $pdo->prepare('UPDATE admins SET password_hash = ?, is_active = 1 WHERE id = ?');
        $maj->execute([$hash, $existant['id']]);
        fwrite(STDOUT, "\nMot de passe mis à jour pour {$email}.\n");
    } else {
        $ajout = $pdo->prepare('INSERT INTO admins(email, password_hash, name, is_active) VALUES(?,?,?,1)');
        $ajout->execute([$email, $hash, $nom]);
        fwrite(STDOUT, "\nCompte administrateur créé pour {$email}.\n");
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Échec de l’enregistrement : " . $e->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, "Mot de passe   : {$motDePasse}\n");
fwrite(STDOUT, "Connexion      : /admin/login.php\n");
fwrite(STDOUT, "Notez ce mot de passe : il ne sera plus affiché.\n");
