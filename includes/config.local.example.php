<?php
// Modele de configuration pour tester le site en local.
//
// 1. Copiez ce fichier en includes/config.local.php
// 2. Adaptez les identifiants ci-dessous
// 3. Importez database/schema.sql (et database/seed.sql) dans cette base
//
// includes/config.local.php n'est pas versionne (voir .gitignore) et n'a aucun effet
// sur l'hebergement : includes/config.php reste la reference en production.
return [
    // MAMP utilise le port 8889 ; un serveur MySQL classique utilise 3306.
    'db_host' => '127.0.0.1;port=8889',
    'db_name' => 'cours_local',
    'db_user' => 'root',
    'db_pass' => 'root',
    'base_url' => '',
    'site_name' => 'MG',
];
