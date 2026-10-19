<?php
require_once __DIR__ . '/includes/repository.php';

header('Content-Type: text/plain; charset=utf-8');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$root = $scheme . '://' . $host . rtrim(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$root = rtrim($root, '/') . '/';

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /uploads/\n";
echo "Disallow: /database/\n";
echo "Disallow: /*?edit=\n";
echo "Disallow: /*?preview=1\n";
echo "\n";
echo "Sitemap: " . $root . "sitemap.xml\n";
