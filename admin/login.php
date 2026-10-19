<?php
require_once __DIR__ . '/auth.php';
if (db_ready() && admin_count() === 0) { header('Location: setup.php'); exit; }
if (current_admin()) { header('Location: dashboard.php'); exit; }
$error = '';
$email = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && db_ready()) {
    // Les adresses sont stockées en minuscules : on normalise la saisie.
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (empty($_SESSION['csrf']) || !hash_equals((string)$_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $error = 'Session expirée. Merci de renvoyer le formulaire.';
    } else {
        $s = db()->prepare('SELECT * FROM admins WHERE email=? AND is_active=1 LIMIT 1');
        $s->execute([$email]);
        $admin = $s->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            // « Se souvenir de moi » : jeton de 30 jours, renouvelé à chaque usage.
            if (!empty($_POST['remember'])) {
                admin_remember_login((int)$admin['id']);
            } else {
                admin_remember_oublier();
            }
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Identifiants incorrects.';
    }
}
page_head('Connexion admin', 'Espace enseignant : gérer les niveaux, chapitres et ressources.', ['math' => false]);
?>
<main class="page-container admin-auth-page" id="main-content">
  <div class="auth-card">
    <header class="auth-card-head">
      <span class="logo-mark" aria-hidden="true">MG</span>
      <div>
        <span class="section-eyebrow">Espace enseignant</span>
        <h1 class="page-title">Connexion</h1>
      </div>
    </header>

    <?php if (!db_ready()): ?>
      <p class="notice-warning">Base de données non configurée. Renseignez <code>includes/config.php</code> puis importez <code>database/schema.sql</code>.</p>
    <?php endif; ?>

    <?php if ($error): ?>
      <p class="notice-error" role="alert"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post" class="auth-form">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <label for="admin-email">Adresse e-mail
        <input id="admin-email" name="email" type="email" value="<?= h($email) ?>" autocomplete="username" required autofocus>
      </label>
      <label for="admin-password">Mot de passe
        <input id="admin-password" name="password" type="password" autocomplete="current-password" required>
      </label>
      <label class="auth-remember" for="admin-remember">
        <input id="admin-remember" name="remember" type="checkbox" value="1" checked>
        <span>Se souvenir de moi pendant 30 jours</span>
      </label>
      <button class="btn-primary btn-block" type="submit">Se connecter</button>
    </form>

    <footer class="auth-card-foot">
      <a href="../index.php">← Retour au site public</a>
    </footer>
  </div>
</main>
</body></html>
