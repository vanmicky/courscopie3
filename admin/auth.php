<?php
require_once __DIR__ . '/../includes/repository.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 12,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
function admin_count(): int {
    $pdo = db(); if (!$pdo) return 0;
    try { return (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn(); } catch (Throwable $e) { return 0; }
}
/* --------------------------------------------------------------------------
   « Se souvenir de moi »
   --------------------------------------------------------------------------
   Jeton de 30 jours, stocké sous forme de haché. Le cookie ne contient que
   `selecteur:validateur` : le sélecteur sert à retrouver la ligne, le
   validateur est comparé à son empreinte SHA-256. Le validateur est renouvelé
   à chaque connexion automatique, donc un cookie volé ne sert qu'une fois.
   -------------------------------------------------------------------------- */
const ADMIN_REMEMBER_COOKIE = 'cours_admin_remember';
const ADMIN_REMEMBER_JOURS = 30;

function admin_remember_schema(): void {
    static $fait = false;
    if ($fait) return;
    $fait = true;
    $pdo = db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_remember (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            selector CHAR(32) NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_admin_remember_selector (selector),
            KEY idx_admin_remember_admin (admin_id),
            CONSTRAINT fk_admin_remember_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        error_log('Admin remember schema: ' . $e->getMessage());
    }
}

function admin_remember_params(int $expire): array {
    return [
        'expires' => $expire,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function admin_remember_login(int $adminId): void {
    admin_remember_schema();
    $pdo = db();
    if (!$pdo) return;
    $selecteur = bin2hex(random_bytes(16));
    $validateur = bin2hex(random_bytes(32));
    $expire = time() + ADMIN_REMEMBER_JOURS * 86400;
    try {
        $pdo->prepare('DELETE FROM admin_remember WHERE expires_at < NOW()')->execute();
        $pdo->prepare('INSERT INTO admin_remember(admin_id, selector, token_hash, expires_at) VALUES(?,?,?,?)')
            ->execute([$adminId, $selecteur, hash('sha256', $validateur), date('Y-m-d H:i:s', $expire)]);
    } catch (Throwable $e) {
        error_log('Admin remember insert: ' . $e->getMessage());
        return;
    }
    setcookie(ADMIN_REMEMBER_COOKIE, $selecteur . ':' . $validateur, admin_remember_params($expire));
    $_COOKIE[ADMIN_REMEMBER_COOKIE] = $selecteur . ':' . $validateur;
}

function admin_remember_oublier(): void {
    $valeur = (string)($_COOKIE[ADMIN_REMEMBER_COOKIE] ?? '');
    if ($valeur !== '' && ($pdo = db())) {
        $selecteur = explode(':', $valeur)[0];
        if (preg_match('/^[a-f0-9]{32}$/', $selecteur)) {
            try { $pdo->prepare('DELETE FROM admin_remember WHERE selector = ?')->execute([$selecteur]); } catch (Throwable $e) {}
        }
    }
    setcookie(ADMIN_REMEMBER_COOKIE, '', admin_remember_params(time() - 3600));
    unset($_COOKIE[ADMIN_REMEMBER_COOKIE]);
}

function admin_remember_tenter(): ?int {
    $valeur = (string)($_COOKIE[ADMIN_REMEMBER_COOKIE] ?? '');
    if (substr_count($valeur, ':') !== 1) return null;
    [$selecteur, $validateur] = explode(':', $valeur, 2);
    if (!preg_match('/^[a-f0-9]{32}$/', $selecteur) || !preg_match('/^[a-f0-9]{64}$/', $validateur)) return null;
    $pdo = db();
    if (!$pdo) return null;
    admin_remember_schema();
    try {
        $s = $pdo->prepare('SELECT id, admin_id, token_hash, expires_at FROM admin_remember WHERE selector = ? LIMIT 1');
        $s->execute([$selecteur]);
        $ligne = $s->fetch();
    } catch (Throwable $e) {
        return null;
    }
    if (!$ligne) return null;
    if (strtotime((string)$ligne['expires_at']) < time()) {
        try { $pdo->prepare('DELETE FROM admin_remember WHERE id = ?')->execute([$ligne['id']]); } catch (Throwable $e) {}
        return null;
    }
    if (!hash_equals((string)$ligne['token_hash'], hash('sha256', $validateur))) {
        // Jeton incohérent : on invalide toutes les sessions mémorisées du compte.
        try { $pdo->prepare('DELETE FROM admin_remember WHERE admin_id = ?')->execute([$ligne['admin_id']]); } catch (Throwable $e) {}
        setcookie(ADMIN_REMEMBER_COOKIE, '', admin_remember_params(time() - 3600));
        unset($_COOKIE[ADMIN_REMEMBER_COOKIE]);
        return null;
    }
    // Rotation : le validateur change à chaque usage.
    $nouveau = bin2hex(random_bytes(32));
    $expire = time() + ADMIN_REMEMBER_JOURS * 86400;
    try {
        $pdo->prepare('UPDATE admin_remember SET token_hash = ?, expires_at = ? WHERE id = ?')
            ->execute([hash('sha256', $nouveau), date('Y-m-d H:i:s', $expire), $ligne['id']]);
        setcookie(ADMIN_REMEMBER_COOKIE, $selecteur . ':' . $nouveau, admin_remember_params($expire));
        $_COOKIE[ADMIN_REMEMBER_COOKIE] = $selecteur . ':' . $nouveau;
    } catch (Throwable $e) {}
    return (int)$ligne['admin_id'];
}

function current_admin(): ?array {
    $pdo = db();
    if (!$pdo) return null;
    if (empty($_SESSION['admin_id'])) {
        $memorise = admin_remember_tenter();
        if ($memorise) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $memorise;
        }
    }
    if (empty($_SESSION['admin_id'])) return null;
    $s = $pdo->prepare('SELECT * FROM admins WHERE id=? AND is_active=1');
    $s->execute([$_SESSION['admin_id']]);
    $admin = $s->fetch() ?: null;
    if (!$admin) {
        unset($_SESSION['admin_id']);
        return null;
    }
    return $admin;
}
function require_admin(): array {
    $admin = current_admin();
    if (!$admin) { header('Location: login.php'); exit; }
    admin_ensure_v69_schema();
    return $admin;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? ''))) { http_response_code(403); exit('Jeton invalide.'); }
}

function admin_flash(string $type, string $message): void {
    $_SESSION['admin_flash'] = ['type' => $type === 'error' ? 'error' : 'success', 'message' => $message];
}

function admin_ensure_v64_schema(): array {
    static $result = null;
    if ($result !== null) return $result;
    $pdo = db();
    if (!$pdo) return $result = ['ok' => false, 'message' => 'Connexion MySQL indisponible.'];
    try {
        $column = $pdo->query("SHOW COLUMNS FROM `files` LIKE 'resource_type'")->fetch();
        if (!$column) {
            $pdo->exec("ALTER TABLE `files` ADD COLUMN `resource_type` VARCHAR(80) NOT NULL DEFAULT 'document' AFTER `label`");
        }
        $pdo->exec("CREATE TABLE IF NOT EXISTS `chapter_videos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `chapter_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `url` VARCHAR(500) NOT NULL,
            `description` TEXT,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_published` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_chapter_videos_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `chapter_skills` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `chapter_id` INT NOT NULL,
            `category` VARCHAR(40) NOT NULL DEFAULT 'Savoir',
            `label` VARCHAR(500) NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_published` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_chapter_skills_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $exerciseCategory = $pdo->query("SHOW COLUMNS FROM `exercises` LIKE 'category'")->fetch();
        if (!$exerciseCategory) {
            $pdo->exec("ALTER TABLE `exercises` ADD COLUMN `category` VARCHAR(40) NOT NULL DEFAULT 'ds' AFTER `difficulty`");
        }
        return $result = ['ok' => true, 'message' => 'Structure V65 disponible.'];
    } catch (Throwable $e) {
        error_log('V65 schema update error: ' . $e->getMessage());
        return $result = ['ok' => false, 'message' => 'Importez les migrations V64 puis database/migrate_v64_to_v65.sql dans phpMyAdmin.'];
    }
}

function admin_ensure_v69_schema(): array {
    static $result = null;
    if ($result !== null) return $result;
    $base = admin_ensure_v64_schema();
    if (!$base['ok']) return $result = $base;
    $pdo = db();
    try {
        foreach (['files', 'exercises', 'qcms', 'flashcard_decks'] as $table) {
            $column = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'sort_order'")->fetch();
            if (!$column) $pdo->exec("ALTER TABLE `$table` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0");
        }
        return $result = ['ok' => true, 'message' => 'Structure V69 disponible.'];
    } catch (Throwable $e) {
        error_log('V69 schema update error: ' . $e->getMessage());
        return $result = ['ok' => false, 'message' => 'Importez database/migrate_v68_to_v69.sql dans phpMyAdmin.'];
    }
}

function admin_unique_slug(PDO $pdo, string $table, string $slug): string {
    $allowed = ['chapters', 'qcms', 'flashcard_decks'];
    if (!in_array($table, $allowed, true)) throw new RuntimeException('Type de contenu non autorise.');
    $base = substr(trim($slug) ?: 'copie', 0, 165);
    $candidate = $base . '-copie';
    $index = 2;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM `' . $table . '` WHERE slug=?');
    while (true) {
        $stmt->execute([$candidate]);
        if ((int)$stmt->fetchColumn() === 0) return $candidate;
        $candidate = $base . '-copie-' . $index++;
    }
}

function admin_nav(): void {
    $current = basename($_SERVER['SCRIPT_NAME'] ?? 'dashboard.php');
    if ($current === 'chapitre.php') $current = 'chapitres.php';
    /* Cinq sections au lieu de seize entrées : on regroupe par tâche, et la
       section en cours est la seule dépliée. La recherche globale passe en haut
       du menu, sous forme de champ. */
    $sections = [
        ['titre' => 'Cours', 'icone' => 'book', 'liens' => [
            ['niveaux.php', 'Niveaux'],
            ['matieres.php', 'Matières'],
            ['chapitres.php', 'Programme'],
            ['ressources-chapitre.php', 'Vidéos et savoirs'],
            ['methodes.php', 'Fiches méthodes'],
        ]],
        ['titre' => 'Activités', 'icone' => 'pencil', 'liens' => [
            ['exercices.php', 'Exercices'],
            ['qcm.php', 'QCM'],
            ['flashcards.php', 'Flashcards'],
            ['assignments.php', 'Travail à faire'],
        ]],
        ['titre' => 'Résultats', 'icone' => 'chart', 'liens' => [
            ['resultats.php', 'Résultats des QCM'],
            ['pilotage.php', 'Pilotage pédagogique'],
            ['trafic.php', 'Trafic du site'],
        ]],
        ['titre' => 'Réglages', 'icone' => 'tools', 'liens' => [
            ['fichiers.php', 'Médiathèque'],
            ['sauvegarde.php', 'Sauvegardes'],
            ['systeme.php', 'État du système'],
        ]],
    ];
    $media = [];
    if ($pdo = db()) {
        try { $media = $pdo->query('SELECT id, label, original_name, path, mime_type FROM files ORDER BY created_at DESC, id DESC LIMIT 80')->fetchAll(); } catch (Throwable $e) {}
    }
    ?>
    <button class="admin-menu-toggle" type="button" data-admin-menu aria-controls="admin-sidebar" aria-expanded="false">Menu admin</button>
    <aside class="admin-sidebar" id="admin-sidebar" aria-label="Administration">
      <a class="admin-brand" href="dashboard.php"><span aria-hidden="true">MG</span><strong>Espace enseignant</strong></a>
      <form class="admin-nav-search" method="get" action="recherche.php" role="search">
        <label class="sr-only" for="admin-nav-q">Rechercher dans l’administration</label>
        <?= icon('search', 'admin-nav-search-icon', 16) ?>
        <input id="admin-nav-q" type="search" name="q" placeholder="Rechercher…" autocomplete="off">
      </form>
      <nav class="admin-nav" aria-label="Sections de l’administration">
        <a class="admin-nav-solo" href="dashboard.php" <?= $current === 'dashboard.php' ? 'aria-current="page"' : '' ?>><?= icon('home', 'admin-nav-icon', 16) ?>Tableau de bord</a>
        <?php foreach ($sections as $section): ?>
          <section class="admin-nav-section">
            <p class="admin-nav-label"><?= icon($section['icone'], 'admin-nav-icon', 14) ?><?= h($section['titre']) ?></p>
            <?php foreach ($section['liens'] as [$href, $label]): ?>
              <a href="<?= h($href) ?>" <?= $current === $href ? 'aria-current="page"' : '' ?>><?= h($label) ?></a>
            <?php endforeach; ?>
          </section>
        <?php endforeach; ?>
      </nav>
      <div class="admin-sidebar-footer">
        <button class="btn-secondary" type="button" data-admin-theme aria-pressed="false">Mode sombre</button>
        <a href="../index.php">Voir le site</a>
        <a href="logout.php">Déconnexion</a>
      </div>
    </aside>
    <div class="admin-menu-backdrop" data-admin-menu-close></div>
    <?php
      $flash = $_SESSION['admin_flash'] ?? null;
      unset($_SESSION['admin_flash']);
      if (!$flash && !empty($_GET['saved'])) $flash = ['type' => 'success', 'message' => 'Enregistrement réussi.'];
      if (!$flash && !empty($_GET['deleted'])) $flash = ['type' => 'success', 'message' => 'Suppression effectuée.'];
      if (!$flash && !empty($_GET['duplicate_error'])) $flash = ['type' => 'error', 'message' => 'La duplication a échoué.'];
    ?>
    <?php if ($flash): ?><div class="admin-toast is-<?= h($flash['type']) ?>" role="status" data-admin-toast><strong><?= $flash['type'] === 'error' ? 'Un problème est survenu' : 'Opération réussie' ?></strong><span><?= h($flash['message']) ?></span><button type="button" aria-label="Fermer" onclick="this.parentElement.remove()">×</button></div><?php endif; ?>
    <dialog class="media-dialog" data-media-dialog>
      <div class="media-dialog-heading"><div><span class="badge">Médiathèque</span><h2>Insérer un fichier</h2></div><button type="button" class="btn-secondary" data-media-close>Fermer</button></div>
      <input type="search" class="media-search" placeholder="Rechercher une image ou un document" data-media-search>
      <div class="media-picker-grid">
        <?php foreach ($media as $file): $label = $file['label'] ?: $file['original_name']; $isImage = strpos((string)$file['mime_type'], 'image/') === 0; ?>
          <button type="button" class="media-picker-item" data-media-item data-label="<?= h($label) ?>" data-path="<?= h($file['path']) ?>" data-kind="<?= $isImage ? 'image' : 'file' ?>">
            <span class="media-kind"><?= $isImage ? 'Image' : 'Fichier' ?></span><strong><?= h($label) ?></strong><small><?= h($file['original_name']) ?></small>
          </button>
        <?php endforeach; ?>
        <?php if (!$media): ?><p class="muted">La médiathèque est vide.</p><?php endif; ?>
      </div>
    </dialog>
    <script defer src="../assets/js/admin-editor.js"></script>
    <?php
}
