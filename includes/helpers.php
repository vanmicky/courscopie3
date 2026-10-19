<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/icons.php';

/**
 * Resume affichable d'un chapitre.
 *
 * Les chapitres importes du site statique portaient tous le meme texte de
 * remplissage (« Contenu repris du site statique… »). Il n'apporte rien a
 * l'eleve : on ne l'affiche pas, meme s'il reste en base.
 */
function chapter_summary(?string $summary): string {
    $texte = trim((string)$summary);
    if ($texte === '') return '';
    if (stripos($texte, 'repris du site statique') !== false) return '';
    return $texte;
}

function h(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function base_path(string $path = ''): string {
    $path = ltrim($path, '/');
    $base = rtrim((string)(app_config()['base_url'] ?? ''), '/');
    if ($base !== '') {
        return $path === '' ? $base . '/' : $base . '/' . $path;
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $directory = trim(dirname($script), '/.');
    $prefix = basename($directory) === 'admin' ? '../' : './';
    return $path === '' ? $prefix : $prefix . $path;
}
function current_page(): string { return basename($_SERVER['SCRIPT_NAME'] ?? ''); }
function format_date_fr(?string $date): string {
    if (!$date) return 'Sans date';
    $value = DateTime::createFromFormat('Y-m-d', substr($date, 0, 10));
    return $value ? $value->format('d-m-Y') : $date;
}

function admin_preview_active(): bool {
    if (($_GET['preview'] ?? '') !== '1') return false;
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(['read_and_close' => true]);
    $adminId = (int)($_SESSION['admin_id'] ?? 0);
    if (!$adminId || !($pdo = db())) return false;
    try {
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE id=? AND is_active=1');
        $stmt->execute([$adminId]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function preview_suffix(bool $preview): string { return $preview ? '&preview=1' : ''; }

function level_exam_space(array $level): ?array {
    $identity = strtolower((string)($level['slug'] ?? '') . ' ' . (string)($level['short'] ?? '') . ' ' . (string)($level['name'] ?? ''));
    if (strpos($identity, 'troisieme') !== false || preg_match('/(^|\s)3e(\s|$)/', $identity)) {
        return ['type' => 'brevet', 'label' => 'Espace Brevet', 'eyebrow' => 'Objectif Brevet', 'description' => 'Réviser tous les thèmes de Troisième avec des exercices corrigés.'];
    }
    if (strpos($identity, 'premiere') !== false || strpos($identity, '1re') !== false) {
        return ['type' => 'bac', 'label' => 'Espace Bac', 'eyebrow' => 'Objectif Bac', 'description' => 'S’entraîner sur les exercices type Bac de tous les chapitres.'];
    }
    return null;
}

function admin_preview_banner(string $editUrl): void { ?>
<aside class="preview-banner" role="status">
  <strong>Prévisualisation élève</strong>
  <span>Les brouillons sont visibles uniquement dans votre session administrateur.</span>
  <a href="<?= h($editUrl) ?>">Revenir à l’édition</a>
</aside>
<?php }
function render_inline_markup(string $text): string {
    $safe = h($text);
    $safe = preg_replace_callback('/!\[([^\]]*)\]\((https?:\/\/[^)\s]+|[a-zA-Z0-9_\/.?=&amp;%-]+)\)/', static function ($match) {
        $url = html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');
        if (!preg_match('/^https?:\/\//i', $url)) $url = base_path($url);
        return '<img class="content-inline-image" src="' . h($url) . '" alt="' . $match[1] . '" loading="lazy">';
    }, $safe);
    $safe = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^)\s]+|[a-zA-Z0-9_\/.?=&amp;%-]+)\)/', static function ($match) {
        $url = html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');
        if (!preg_match('/^https?:\/\//i', $url)) $url = base_path($url);
        return '<a href="' . h($url) . '" target="_blank" rel="noopener">' . $match[1] . '</a>';
    }, $safe);
    $safe = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $safe);
    $safe = preg_replace('/`([^`]+)`/', '<code>$1</code>', $safe);
    return $safe;
}

function render_text(?string $text): string {
    $lines = preg_split('/\R/', trim((string)$text));
    if (!$lines || ($lines === [''])) return '';
    $html = [];
    foreach ($lines as $line) {
        if (preg_match('/^##\s+(.+)$/', $line, $match)) {
            $html[] = '<span class="content-heading">' . render_inline_markup($match[1]) . '</span>';
        } elseif (preg_match('/^#\s+(.+)$/', $line, $match)) {
            $html[] = '<span class="content-heading content-heading-large">' . render_inline_markup($match[1]) . '</span>';
        } elseif (preg_match('/^-\s+(.+)$/', $line, $match)) {
            $html[] = '<span class="content-list-item">' . render_inline_markup($match[1]) . '</span>';
        } elseif (preg_match('/^>\s*(.*)$/', $line, $match)) {
            $html[] = '<span class="content-callout">' . render_inline_markup($match[1]) . '</span>';
        } elseif (trim($line) === '') {
            $html[] = '<span class="content-spacer" aria-hidden="true"></span>';
        } else {
            $html[] = '<span class="content-line">' . render_inline_markup($line) . '</span>';
        }
    }
    return implode('', $html);
}

function latex_help_panel(): void { ?>
<details class="admin-latex-help">
  <summary>Repères LaTeX</summary>
  <div class="admin-latex-examples">
    <span><code>$2x+5=17$</code><b>\(2x+5=17\)</b></span>
    <span><code>$$E=mc^2$$</code><b>\[E=mc^2\]</b></span>
    <span><code>$\frac{a}{b}$</code><b>\(\frac{a}{b}\)</b></span>
    <span><code>$x^2$</code><b>\(x^2\)</b></span>
    <span><code>$\sqrt{25}$</code><b>\(\sqrt{25}\)</b></span>
  </div>
</details>
<?php }

function nav_levels(): array {
    if ($pdo = db()) {
        try {
            return $pdo->query('SELECT slug, short, name, accent FROM levels WHERE is_published=1 ORDER BY sort_order,id')->fetchAll();
        } catch (Throwable $e) {}
    }
    $seed = __DIR__ . '/seed-levels.php';
    if (is_file($seed)) return require $seed;
    return [];
}

/**
 * En-tete HTML commun.
 *
 * @param array $options {
 *     @type bool $math   Charger KaTeX (formules LaTeX). true par defaut : ne passer
 *                        false que sur une page dont le contenu ne comporte aucun
 *                        delimiteur $...$, \(...\) ou $$...$$ (gain ~270 Ko).
 *     @type bool   $robots Autoriser l'indexation (false sur les pages d'administration).
 *     @type string $accent Couleur de la matiere (colore le logo, les survols, les pastilles).
 *     @type string $body   Classe de page imposee (« page-home » ou « page-inner »).
 *                          Par defaut elle est deduite du script courant : le motif de
 *                          fond ne s'applique qu'aux pages interieures.
 * }
 */
function page_head(string $title, string $description = '', array $options = []): void {
    $site = app_config()['site_name'] ?? 'MG';
    $full = $title === $site ? $site : $title . ' - ' . $site;
    $script = basename(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    $isAdmin = strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/admin/') !== false;
    $math = $options['math'] ?? true;
    $robots = $options['robots'] ?? !$isAdmin;
    $isHome = $options['home'] ?? ($script === 'index.php');
    $bodyClasses = [];
    if ($isAdmin) $bodyClasses[] = 'admin-area';
    $bodyClasses[] = $isHome ? 'page-home' : 'page-inner';
    // Classes et attributs supplementaires (mode projection des outils).
    foreach ((array)($options['body_class'] ?? []) as $classe) {
        if ($classe !== '') $bodyClasses[] = (string)$classe;
    }

    // Couleur de la matiere en cours : elle colore le logo, les survols du menu
    // et les pastilles. Deduite de l'option, sinon du niveau passe en URL.
    $accent = (string)($options['accent'] ?? '');
    if ($accent === '' && isset($_GET['level'])) {
        foreach (nav_levels() as $niveau) {
            if (($niveau['slug'] ?? '') === (string)$_GET['level']) { $accent = (string)($niveau['accent'] ?? ''); break; }
        }
    }
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($full) . '</title><meta name="description" content="' . h($description ?: $site) . '">';
    if (!$robots) echo '<meta name="robots" content="noindex, nofollow">';
    echo '<meta name="color-scheme" content="light dark"><meta name="theme-color" content="#f4f4f5" media="(prefers-color-scheme: light)"><meta name="theme-color" content="#0b0b0c" media="(prefers-color-scheme: dark)">';
    echo '<link rel="icon" href="' . h(base_path('favicon.ico')) . '" sizes="any">';
    echo '<link rel="apple-touch-icon" href="' . h(base_path('assets/img/icons/apple-touch-icon.png')) . '">';
    echo '<link rel="manifest" href="' . h(base_path('manifest.webmanifest')) . '">';
    echo '<link rel="preload" href="' . h(base_path('assets/fonts/inter-var-latin.woff2')) . '" as="font" type="font/woff2" crossorigin>';
    echo '<link rel="preload" href="' . h(base_path('assets/fonts/krub-700-latin.woff2')) . '" as="font" type="font/woff2" crossorigin>';
    echo '<link rel="stylesheet" href="' . h(base_path('dist/output.css?v=124')) . '">';
    echo '<script src="' . h(base_path('assets/js/theme-init.js')) . '"></script>';
    if ($math) {
        // KaTeX est servi depuis le site : aucune dependance a un CDN externe.
        echo '<link rel="stylesheet" href="' . h(base_path('assets/vendor/katex/katex.min.css')) . '">';
        echo '<script defer src="' . h(base_path('assets/vendor/katex/katex.min.js')) . '"></script>';
        echo '<script defer src="' . h(base_path('assets/vendor/katex/auto-render.min.js')) . '"></script>';
        echo '<script defer src="' . h(base_path('assets/js/math.js')) . '"></script>';
    }
    $bodyAttrs = '';
    foreach ((array)($options['body_attrs'] ?? []) as $nom => $valeur) {
        if (preg_match('/^[a-z][a-z0-9-]*$/i', (string)$nom)) {
            $bodyAttrs .= ' ' . $nom . '="' . h((string)$valeur) . '"';
        }
    }
    echo '</head><body class="' . h(implode(' ', $bodyClasses)) . '"'
        . ($accent !== '' ? ' style="--accent:' . h($accent) . '"' : '')
        . $bodyAttrs . '>';
}

function site_header(): void {
$levels = nav_levels();
$script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$activeLevel = $script === 'niveau.php' ? (string)($_GET['slug'] ?? '') : (string)($_GET['level'] ?? '');
$isOutils = in_array($script, ['outils.php', 'timer.php'], true);
$isMethodes = in_array($script, ['methodes.php', 'methode.php'], true);
?>
<a class="skip-link" href="#main-content">Aller au contenu</a>
<header class="site-header">
  <div class="page-container site-header-inner">
    <a href="<?= h(base_path('index.php')) ?>" class="site-brand" aria-label="Accueil">
      <span class="logo-mark" aria-hidden="true">MG</span>
    </a>
    <nav class="desktop-nav" aria-label="Navigation principale">
      <?php foreach ($levels as $level): ?><a class="nav-link nav-level" href="<?= h(base_path('niveau.php?slug=' . $level['slug'])) ?>" style="--accent:<?= h($level['accent'] ?? '#2563eb') ?>" <?= $activeLevel === $level['slug'] ? 'aria-current="page"' : '' ?>><span class="nav-dot" aria-hidden="true"></span><?= h($level['short']) ?></a><?php endforeach; ?>
      <a class="nav-link" href="<?= h(base_path('methodes.php')) ?>" <?= $isMethodes ? 'aria-current="page"' : '' ?>><?= icon('book', 'nav-icon', 18) ?>Méthodes</a>
      <a class="nav-link" href="<?= h(base_path('outils.php')) ?>" <?= $isOutils ? 'aria-current="page"' : '' ?>><?= icon('tools', 'nav-icon', 18) ?>Outils de classe</a>
    </nav>
    <div class="header-actions">
      <button data-theme-toggle class="theme-btn" type="button" aria-pressed="false"><span data-theme-icon aria-hidden="true"><?= icon('moon', 'theme-icon', 18) ?></span><span class="theme-btn-label" data-theme-label>Thème sombre</span></button>
      <button class="menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" aria-label="Ouvrir le menu"><?= icon('menu', 'menu-toggle-icon', 22) ?></button>
    </div>
  </div>
</header>
<div class="mobile-menu-backdrop" data-menu-backdrop aria-hidden="true"></div>
<nav id="mobile-menu" class="mobile-nav" data-mobile-menu aria-label="Navigation mobile" aria-hidden="true">
  <div class="mobile-menu-head">
    <strong>Navigation</strong>
    <button type="button" class="mobile-menu-close" data-menu-close aria-label="Fermer le menu"><?= icon('close', '', 22) ?></button>
  </div>
  <?php foreach ($levels as $level): ?><a class="nav-link nav-level" href="<?= h(base_path('niveau.php?slug=' . $level['slug'])) ?>" style="--accent:<?= h($level['accent'] ?? '#2563eb') ?>" <?= $activeLevel === $level['slug'] ? 'aria-current="page"' : '' ?>><span class="nav-dot" aria-hidden="true"></span><?= h($level['short']) ?></a><?php endforeach; ?>
  <a class="nav-link" href="<?= h(base_path('methodes.php')) ?>"><?= icon('book', 'nav-icon', 16) ?>Méthodes</a>
  <a class="nav-link" href="<?= h(base_path('outils.php')) ?>"><?= icon('tools', 'nav-icon', 16) ?>Outils</a>
  <a class="nav-link nav-admin" href="<?= h(base_path('admin/login.php')) ?>"><?= icon('lock', 'nav-icon', 16) ?>Espace enseignant</a>
</nav>
<?php }

function site_footer(): void {
    // Enregistrement de la visite (les robots et l'administration sont exclus).
    require_once __DIR__ . '/analytics.php';
    visite_enregistrer(); ?>
<footer class="site-footer">
  <div class="page-container">
    <div class="site-footer-brand">
      <span class="logo-mark" aria-hidden="true">MG</span>
    </div>
    <nav class="site-footer-links" aria-label="Liens de pied de page">
      <a href="<?= h(base_path('index.php')) ?>"><?= icon('home', 'footer-icon', 16) ?>Niveaux</a>
      <a href="<?= h(base_path('travail-a-faire.php')) ?>"><?= icon('calendar', 'footer-icon', 16) ?>Travail à faire</a>
      <a href="<?= h(base_path('qcm.php')) ?>"><?= icon('check', 'footer-icon', 16) ?>QCM</a>
      <a href="<?= h(base_path('flashcards.php')) ?>"><?= icon('cards', 'footer-icon', 16) ?>Flashcards</a>
      <a href="<?= h(base_path('methodes.php')) ?>"><?= icon('book', 'footer-icon', 16) ?>Méthodes</a>
      <a href="<?= h(base_path('admin/login.php')) ?>"><?= icon('tools', 'footer-icon', 16) ?>Espace enseignant</a>
    </nav>
    <p class="site-footer-meta">M. Gedion © <?= date('Y') ?></p>
  </div>
</footer>
<?= icon_sprite() ?>
<script src="<?= h(base_path('assets/js/mobile-menu.js')) ?>"></script>
<script src="<?= h(base_path('assets/js/main.js?v=124')) ?>"></script>
<script src="<?= h(base_path('assets/js/register-sw.js')) ?>"></script>
</body></html>
<?php }

/**
 * Nettoie le HTML riche saisi dans l'administration (fiches methodes).
 * Retire les balises actives et les attributs d'evenement : le contenu
 * provient d'un administrateur, mais il est affiche tel quel sur le site.
 */
function sanitize_rich_html(string $html): string {
    $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
    $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|link|meta)\b[^>]*/?>#is', '', $html) ?? $html;
    $html = preg_replace('#\son[a-z]+\s*=\s*"[^"]*"#i', '', $html) ?? $html;
    $html = preg_replace("#\son[a-z]+\s*=\s*'[^']*'#i", '', $html) ?? $html;
    $html = preg_replace('#\son[a-z]+\s*=\s*[^\s>]+#i', '', $html) ?? $html;
    $html = preg_replace('#(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*#i', '$1=$2#', $html) ?? $html;
    return $html;
}

/**
 * Image responsive : sert du WebP (800 px et 1200 px) quand il existe,
 * avec le JPEG d'origine en repli. Evite de charger 400 Ko sur un telephone.
 *
 * @param string $image  chemin relatif a la racine du site (ex : assets/img/levels/3e.jpeg)
 * @param string $alt    texte alternatif
 * @param string $class  classes CSS appliquees a la balise <img>
 * @param string $sizes  attribut sizes (largeur d'affichage selon le viewport)
 * @param bool   $eager  true pour l'image principale (chargement immediat)
 */
function responsive_picture(string $image, string $alt, string $class, string $sizes = '100vw', bool $eager = false): string {
    $image = ltrim($image, '/');
    $extension = strtolower((string)pathinfo($image, PATHINFO_EXTENSION));
    $loading = $eager ? 'eager' : 'lazy';
    $fetchPriority = $eager ? ' fetchpriority="high"' : '';

    $sources = '';
    if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
        $stem = substr($image, 0, -(strlen($extension) + 1));
        $candidates = [];
        foreach ([800, 1200] as $width) {
            $file = $stem . '-' . $width . '.webp';
            if (is_file(__DIR__ . '/../' . $file)) {
                $candidates[] = h(base_path($file)) . ' ' . $width . 'w';
            }
        }
        if ($candidates) {
            $sources = '<source type="image/webp" srcset="' . implode(', ', $candidates) . '" sizes="' . h($sizes) . '">';
        }
    }

    return '<picture>' . $sources
        . '<img class="' . h($class) . '" src="' . h(base_path($image)) . '" alt="' . h($alt) . '"'
        . ' sizes="' . h($sizes) . '" loading="' . $loading . '" decoding="async"' . $fetchPriority . '>'
        . '</picture>';
}
