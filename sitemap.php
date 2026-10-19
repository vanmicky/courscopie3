<?php
require_once __DIR__ . '/includes/repository.php';

header('Content-Type: application/xml; charset=utf-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = $scheme . '://' . $host . rtrim(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/';
$base = str_replace('/./', '/', $base);

/** @var array<int,array{loc:string,lastmod:?string,priority:string,changefreq:string}> $urls */
$urls = [];

$add = static function (string $path, ?string $lastmod = null, string $priority = '0.6', string $changefreq = 'monthly') use (&$urls, $base): void {
    $urls[] = ['loc' => $base . ltrim($path, '/'), 'lastmod' => $lastmod, 'priority' => $priority, 'changefreq' => $changefreq];
};

$add('index.php', null, '1.0', 'weekly');
$add('travail-a-faire.php', null, '0.9', 'weekly');
$add('qcm.php', null, '0.8', 'weekly');
$add('flashcards.php', null, '0.8', 'weekly');
$add('exercices.php', null, '0.8', 'weekly');
$add('methodes.php', null, '0.8', 'monthly');
$add('outils.php', null, '0.5', 'monthly');
$add('timer.php', null, '0.3', 'yearly');

foreach (get_levels() as $level) {
    $add('niveau.php?slug=' . urlencode((string)$level['slug']), $level['updated_at'] ?? null, '0.9', 'weekly');
}
foreach (get_chapters() as $chapter) {
    $add('chapitre.php?slug=' . urlencode((string)$chapter['slug']), $chapter['updated_at'] ?? null, '0.8', 'monthly');
}
foreach (get_qcms() as $qcm) {
    $add('qcm.php?slug=' . urlencode((string)$qcm['slug']), $qcm['updated_at'] ?? null, '0.7', 'monthly');
}
foreach (get_flashcard_decks() as $deck) {
    $add('flashcards.php?slug=' . urlencode((string)$deck['slug']), $deck['updated_at'] ?? null, '0.6', 'monthly');
}
foreach (get_methods() as $method) {
    $add('methode.php?slug=' . urlencode((string)$method['slug']), $method['updated_at'] ?? null, '0.6', 'yearly');
}

$esc = static fn(string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
    echo "  <url>\n";
    echo '    <loc>' . $esc($url['loc']) . "</loc>\n";
    if (!empty($url['lastmod'])) {
        $timestamp = strtotime((string)$url['lastmod']);
        if ($timestamp) echo '    <lastmod>' . date('Y-m-d', $timestamp) . "</lastmod>\n";
    }
    echo '    <changefreq>' . $esc($url['changefreq']) . "</changefreq>\n";
    echo '    <priority>' . $esc($url['priority']) . "</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>\n";
