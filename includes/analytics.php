<?php
/**
 * Statistiques de fréquentation.
 *
 * Chaque page publique enregistre une visite : date, pays (résolu en local,
 * sans appel externe), page, site d'origine, et une empreinte anonyme du
 * visiteur (SHA-256 de l'IP salée — l'adresse IP n'est jamais stockée).
 *
 * Les robots, les prévisualisations et l'administration ne sont pas comptés.
 */

declare(strict_types=1);

require_once __DIR__ . '/geoip.php';

/* Sel fixe : il rend l'empreinte stable dans le temps sans conserver l'IP.
   Le changer remet les compteurs de visiteurs uniques à zéro. */
const VISITES_SEL = 'mg-cours-2026';

function visites_schema(): void {
    static $fait = false;
    if ($fait) return;
    $fait = true;
    if (!function_exists('db')) {
        require_once __DIR__ . '/repository.php';
    }
    $pdo = db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `visits` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `visited_at` DATETIME NOT NULL,
            `visited_day` DATE NOT NULL,
            `country` VARCHAR(8) NOT NULL DEFAULT 'ZZ',
            `path` VARCHAR(160) NOT NULL DEFAULT '',
            `referrer` VARCHAR(160) NOT NULL DEFAULT '',
            `visitor` CHAR(64) NOT NULL DEFAULT '',
            `is_bot` TINYINT(1) NOT NULL DEFAULT 0,
            KEY `idx_visits_day` (`visited_day`),
            KEY `idx_visits_country` (`country`),
            KEY `idx_visits_bot` (`is_bot`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        error_log('Visits schema: ' . $e->getMessage());
    }
}

function visites_est_robot(string $ua): bool {
    if ($ua === '') return true;
    return (bool)preg_match(
        '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|embedly|quora|pinterest|vkshare|w3c_validator|headlesschrome|lighthouse|pingdom|uptime|monitor|curl\/|wget\/|python-requests|go-http-client|java\/|okhttp|scrapy/i',
        $ua
    );
}

/** Enregistre la visite en cours. Sans effet hors page publique. */
function visite_enregistrer(): void {
    static $deja = false;
    if ($deja) return;
    $deja = true;

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (str_contains($script, '/admin/')) return;
    if (!empty($_GET['preview'])) return;
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return;

    $pdo = db();
    if (!$pdo) return;
    visites_schema();

    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $pays = geoip_code($ip);

    $chemin = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $chemin = preg_replace('/\?.*$/', '', $chemin);
    $chemin = substr($chemin, 0, 160);

    $referant = '';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $hote = parse_url((string)$_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        if ($hote) {
            $interne = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), PHP_URL_HOST);
            $referant = $hote === $interne ? '' : substr((string)$hote, 0, 160);
        }
    }

    try {
        $pdo->prepare('INSERT INTO visits(visited_at, visited_day, country, path, referrer, visitor, is_bot) VALUES(NOW(), CURDATE(), ?, ?, ?, ?, ?)')
            ->execute([$pays, $chemin, $referant, hash('sha256', $ip . VISITES_SEL), visites_est_robot($ua) ? 1 : 0]);
        // Purge occasionnelle : on garde un an d'historique.
        if (random_int(1, 200) === 1) {
            $pdo->exec('DELETE FROM visits WHERE visited_day < DATE_SUB(CURDATE(), INTERVAL 365 DAY)');
        }
    } catch (Throwable $e) {
        error_log('Visits insert: ' . $e->getMessage());
    }
}

/** Bilan sur une période (en jours ; 0 = tout l'historique). */
function visites_bilan(int $jours = 30): array {
    $pdo = db();
    if (!$pdo) return ['total' => 0, 'aujourdhui' => 0, 'periode' => 0, 'uniques' => 0, 'pays' => [], 'pages' => [], 'jours' => [], 'referants' => []];
    visites_schema();

    $filtre = $jours > 0 ? ' AND visited_day >= DATE_SUB(CURDATE(), INTERVAL ' . (int)$jours . ' DAY)' : '';
    $humains = ' AND is_bot = 0';
    $vide = static function (Throwable $e) { return []; };

    try {
        $total = (int)$pdo->query('SELECT COUNT(*) FROM visits WHERE is_bot = 0')->fetchColumn();
        $aujourdhui = (int)$pdo->query('SELECT COUNT(*) FROM visits WHERE is_bot = 0 AND visited_day = CURDATE()')->fetchColumn();
        $periode = (int)$pdo->query('SELECT COUNT(*) FROM visits WHERE 1=1' . $humains . $filtre)->fetchColumn();
        $uniques = (int)$pdo->query('SELECT COUNT(DISTINCT visitor) FROM visits WHERE 1=1' . $humains . $filtre)->fetchColumn();

        $pays = $pdo->query('SELECT country, COUNT(*) AS n FROM visits WHERE 1=1' . $humains . $filtre . ' GROUP BY country ORDER BY n DESC LIMIT 15')->fetchAll();

        $pages = $pdo->query('SELECT path, COUNT(*) AS n FROM visits WHERE 1=1' . $humains . $filtre . " GROUP BY path ORDER BY n DESC LIMIT 10")->fetchAll();

        $referants = $pdo->query("SELECT referrer, COUNT(*) AS n FROM visits WHERE referrer <> ''" . $humains . $filtre . ' GROUP BY referrer ORDER BY n DESC LIMIT 8')->fetchAll();

        $jours = $pdo->query('SELECT visited_day AS jour, COUNT(*) AS n FROM visits WHERE visited_day >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)' . $humains . ' GROUP BY visited_day ORDER BY visited_day')->fetchAll();
    } catch (Throwable $e) {
        return ['total' => 0, 'aujourdhui' => 0, 'periode' => 0, 'uniques' => 0, 'pays' => [], 'pages' => [], 'jours' => [], 'referants' => []];
    }

    return [
        'total' => $total,
        'aujourdhui' => $aujourdhui,
        'periode' => $periode,
        'uniques' => $uniques,
        'pays' => $pays,
        'pages' => $pages,
        'jours' => $jours,
        'referants' => $referants,
    ];
}
