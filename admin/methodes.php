<?php
require_once __DIR__ . '/auth.php';
require_admin();
verify_csrf();

$pdo = db();
if (!$pdo) {
    page_head('Admin indisponible', '', ['math' => false]);
    echo '<main class="page-container"><p class="notice-warning mt-8">Base non configurée.</p></main></body></html>';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'save';
    try {
        if ($action === 'delete' && $id) {
            $stmt = $pdo->prepare('DELETE FROM methods WHERE id=?');
            $stmt->execute([$id]);
            header('Location: methodes.php?deleted=1');
            exit;
        }
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '') throw new RuntimeException('Le titre est obligatoire.');

        $data = [
            'slug' => slugify_text(trim((string)($_POST['slug'] ?? '')) ?: $title, 'methode'),
            'title' => $title,
            'summary' => trim((string)($_POST['summary'] ?? '')),
            'domain' => trim((string)($_POST['domain'] ?? '')) ?: 'Méthode générale',
            'levels' => trim((string)($_POST['levels'] ?? '')) ?: 'Tous niveaux',
            'accent' => preg_match('/^#[0-9a-fA-F]{3,8}$/', (string)($_POST['accent'] ?? '')) ? (string)$_POST['accent'] : '#2563eb',
            'icon' => mb_substr(trim((string)($_POST['icon'] ?? '')), 0, 8),
            'content' => sanitize_rich_html((string)($_POST['content'] ?? '')),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        if ($id) {
            $data['id'] = $id;
            $stmt = $pdo->prepare('UPDATE methods SET slug=:slug, title=:title, summary=:summary, domain=:domain, levels=:levels, accent=:accent, icon=:icon, content=:content, sort_order=:sort_order, is_published=:is_published WHERE id=:id');
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare('INSERT INTO methods (slug,title,summary,domain,levels,accent,icon,content,sort_order,is_published) VALUES (:slug,:title,:summary,:domain,:levels,:accent,:icon,:content,:sort_order,:is_published)');
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: methodes.php?edit=' . $id . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM methods WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$methods = get_methods(true);
$domains = method_domains();
$canSave = methods_available();

page_head('Admin - Fiches méthodes', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">Fiches méthodes</h1>
    <p class="muted">Créer et modifier les fiches de méthode affichées sur <a href="../methodes.php">/methodes.php</a>. Le contenu accepte le HTML simple (titres, listes, encadrés) et les formules LaTeX entre <code>$…$</code>.</p>
  </section>

  <?php if ($error): ?><p class="notice-error mt-5" role="alert"><?= h($error) ?></p><?php endif; ?>
  <?php if (!empty($_GET['saved'])): ?><p class="notice-success mt-5" role="status">Fiche enregistrée.</p><?php endif; ?>
  <?php if (!empty($_GET['deleted'])): ?><p class="notice-success mt-5" role="status">Fiche supprimée.</p><?php endif; ?>

  <?php if (!$canSave): ?>
    <p class="notice-warning mt-6">
      La table <code>methods</code> n'existe pas encore : importez <code>database/migrate_v74_to_v75.sql</code> dans phpMyAdmin.
      Les <?= count($methods) ?> fiches ci-dessous sont affichées depuis les données de démarrage et ne sont pas modifiables.
    </p>
  <?php endif; ?>

  <?php if ($canSave): ?>
  <section class="card mt-8">
    <h2 class="section-title"><?= $edit ? 'Modifier la fiche' : 'Ajouter une fiche' ?></h2>
    <form method="post" class="mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">

      <div class="admin-form-grid">
        <label class="span-full">Titre
          <input name="title" required value="<?= h($edit['title'] ?? '') ?>" placeholder="Ex : Convertir des unités">
        </label>
        <label>Domaine (étiquette)
          <input name="domain" list="method-domains" value="<?= h($edit['domain'] ?? 'Méthode générale') ?>">
          <datalist id="method-domains">
            <?php foreach ($domains as $domain): ?><option value="<?= h($domain) ?>"></option><?php endforeach; ?>
          </datalist>
        </label>
        <label>Niveaux concernés
          <input name="levels" value="<?= h($edit['levels'] ?? 'Tous niveaux') ?>" placeholder="Ex : Collège / Lycée">
        </label>
        <label>Couleur d'accent
          <input type="color" name="accent" value="<?= h($edit['accent'] ?? '#2563eb') ?>">
        </label>
        <label>Icône (emoji)
          <input name="icon" value="<?= h($edit['icon'] ?? '') ?>" placeholder="Ex : 📈" maxlength="8">
        </label>
        <label>Slug
          <input name="slug" value="<?= h($edit['slug'] ?? '') ?>" placeholder="auto si vide">
        </label>
        <label>Ordre
          <input type="number" name="sort_order" value="<?= h($edit['sort_order'] ?? '0') ?>">
        </label>
      </div>

      <label class="mt-4">Résumé affiché dans la liste
        <textarea name="summary" rows="2" placeholder="Une phrase qui explique à quoi sert la fiche."><?= h($edit['summary'] ?? '') ?></textarea>
      </label>

      <label class="mt-4">Contenu de la fiche (HTML)
        <textarea name="content" rows="18" spellcheck="false" placeholder="<section class=&quot;part-card&quot;>…"><?= h($edit['content'] ?? '') ?></textarea>
        <small>Balises utiles : <code>&lt;h2&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;ul&gt;&lt;li&gt;</code>, <code>&lt;div class="method-steps"&gt;</code>, <code>&lt;div class="content-callout"&gt;</code>. Les scripts et les gestionnaires d'événements sont retirés automatiquement.</small>
      </label>

      <div class="mt-4 flex flex-wrap items-center gap-3">
        <label><input type="checkbox" name="is_published" <?= !isset($edit) || (int)($edit['is_published'] ?? 1) === 1 ? 'checked' : '' ?>> Publiée</label>
        <button class="btn-primary" type="submit">Enregistrer</button>
        <?php if ($edit): ?><a class="btn-secondary" href="../methode.php?slug=<?= h($edit['slug']) ?>" target="_blank" rel="noopener">Voir la fiche</a><?php endif; ?>
        <?php if ($edit): ?><a class="btn-secondary" href="methodes.php">Nouvelle fiche</a><?php endif; ?>
      </div>
    </form>
  </section>
  <?php endif; ?>

  <section class="chapter-grid mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($methods as $method): ?>
      <article class="chapter-tile" style="--card-accent:<?= h($method['accent'] ?? '#2563eb') ?>">
        <span class="chapter-number">
          <?= h($method['icon'] ?: '📄') ?> <?= h($method['domain']) ?>
          · <strong class="status-pill <?= (int)$method['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$method['is_published'] ? 'Publiée' : 'Brouillon' ?></strong>
        </span>
        <h3><?= h($method['title']) ?></h3>
        <p><?= h($method['summary'] ?: 'Sans résumé') ?></p>
        <div class="chapter-actions">
          <?php if ($canSave): ?><a href="methodes.php?edit=<?= (int)$method['id'] ?>">Modifier</a><?php endif; ?>
          <a href="../methode.php?slug=<?= h($method['slug']) ?>" target="_blank" rel="noopener">Voir</a>
          <?php if ($canSave): ?><form method="post" onsubmit="return confirm('Supprimer cette fiche méthode ?')">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$method['id'] ?>">
            <button class="btn-secondary" type="submit">Supprimer</button>
          </form><?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
</body>
</html>
