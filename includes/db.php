<?php
function app_config(): array {
    static $config = null;
    if ($config !== null) return $config;
    // config.local.php (non versionne) permet de tester en local sans toucher
    // aux identifiants de production definis dans config.php.
    $local = __DIR__ . '/config.local.php';
    if (file_exists($local)) {
        $config = require $local;
        return $config;
    }
    $file = __DIR__ . '/config.php';
    $config = file_exists($file) ? require $file : require __DIR__ . '/config.example.php';
    return $config;
}

function db(): ?PDO {
    static $pdo = false;
    if ($pdo !== false) return $pdo;
    $config = app_config();
    if (empty($config['db_name']) || empty($config['db_user'])) {
        $pdo = null;
        return null;
    }
    try {
        // Le port est optionnel : MAMP utilise 8889, un hébergement mutualisé
        // laisse en général 3306 par défaut.
        $port = $config['db_port'] ?? null;
        $dsn = 'mysql:host=' . $config['db_host']
             . ($port ? ';port=' . (int)$port : '')
             . ';dbname=' . $config['db_name'] . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (Throwable $e) {
        $pdo = null;
        return null;
    }
}

function db_ready(): bool { return db() instanceof PDO; }
