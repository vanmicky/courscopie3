<?php
/**
 * Point d'entrée de l'espace enseignant.
 *
 * Ouvrir /admin/ sans fichier index renvoyait une erreur de répertoire. On
 * redirige maintenant : tableau de bord si une session (ou un jeton
 * « se souvenir de moi ») est valide, page de connexion sinon.
 */
require_once __DIR__ . '/auth.php';

header('Location: ' . (current_admin() ? 'dashboard.php' : 'login.php'));
exit;
