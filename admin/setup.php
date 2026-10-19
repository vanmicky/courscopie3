<?php
require_once __DIR__ . '/auth.php';

$error = '';
$pdo = db();

if (!$pdo) {
    $error = 'Connexion MySQL impossible. Vérifiez les informations dans includes/config.php.';
} else {
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
        error_log('Admin setup schema error: ' . $e->getMessage());
        $error = 'La table admins ne peut pas être préparée. Vérifiez les droits MySQL de cet hébergement.';
    }
}

if ($pdo && $error === '' && admin_count() > 0) {
    header('Location: login.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $pdo && $error === '') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $name = trim($_POST['name'] ?? '') ?: 'M. Gedion';
    $password = (string)($_POST['password'] ?? '');
    $confirmation = (string)($_POST['password_confirmation'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Saisissez une adresse email valide.';
    } elseif (strlen($password) < 10) {
        $error = 'Le mot de passe doit contenir au moins 10 caractères.';
    } elseif ($password !== $confirmation) {
        $error = 'Les deux mots de passe ne correspondent pas.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO admins(email,password_hash,name,is_active) VALUES(?,?,?,1)');
            $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $name]);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$pdo->lastInsertId();
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $e) {
            error_log('Admin setup insert error: ' . $e->getMessage());
            $error = (string)$e->getCode() === '23000'
                ? 'Cette adresse email existe déjà dans la table admins.'
                : 'Le compte n’a pas pu être enregistré. Vérifiez la structure de la table admins dans phpMyAdmin.';
        }
    }
}

page_head('Initialisation admin', '', ['math' => false]);
?>
<main class="page-container admin-auth-page" id="main-content">
  <section class="card p-6 sm:p-8">
    <span class="badge">Installation · V73</span>
    <h1 class="page-title mt-4">Créer le compte admin</h1>
    <p class="mt-4 muted">Cette page fonctionne uniquement lorsque la table admins ne contient aucun compte.</p>
    <?php if ($error): ?><div class="notice-warning mt-5" role="alert"><strong>Création impossible</strong><p><?= h($error) ?></p></div><?php endif; ?>
    <?php if ($pdo && $error === ''): ?>
      <form method="post" class="work-filter-panel mt-6">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="admin-account-grid">
          <label>Nom affiché<input name="name" value="M. Gedion" maxlength="120" autocomplete="name" required></label>
          <label>Email admin<input name="email" type="email" maxlength="190" autocomplete="email" required></label>
          <label>Mot de passe<input name="password" type="password" minlength="10" autocomplete="new-password" required></label>
          <label>Confirmer le mot de passe<input name="password_confirmation" type="password" minlength="10" autocomplete="new-password" required></label>
        </div>
        <div class="mt-5 flex flex-wrap items-center gap-3">
          <button class="btn-primary" type="submit">Créer et ouvrir l’administration</button>
          <a class="btn-secondary" href="login.php">Retour à la connexion</a>
        </div>
      </form>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
