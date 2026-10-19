<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chapter-workspace.php';
require_admin();
verify_csrf();

$pdo = db();
if (!$pdo) {
    page_head('Admin indisponible');
    echo '<main class="page-container py-8"><p>Base non configuree.</p></main></body></html>';
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save_deck';
    try {
        if ($action === 'delete_deck') {
            $stmt = $pdo->prepare('DELETE FROM flashcard_decks WHERE id=?');
            $stmt->execute([(int)$_POST['id']]);
            header('Location: flashcards.php');
            exit;
        }
        if ($action === 'duplicate_deck') {
            $sourceId = (int)$_POST['id'];
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('SELECT * FROM flashcard_decks WHERE id=?');
                $stmt->execute([$sourceId]);
                $source = $stmt->fetch();
                if (!$source) throw new RuntimeException('Deck introuvable.');
                $slug = admin_unique_slug($pdo, 'flashcard_decks', $source['slug']);
                $pdo->prepare('INSERT INTO flashcard_decks(chapter_id,level_id,title,slug,is_published) VALUES(?,?,?,?,0)')->execute([$source['chapter_id'],$source['level_id'],'Copie de '.$source['title'],$slug]);
                $newId = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO flashcards(deck_id,front_text,back_text,sort_order) SELECT ?,front_text,back_text,sort_order FROM flashcards WHERE deck_id=?')->execute([$newId,$sourceId]);
                $pdo->commit();
                header('Location: flashcards.php?edit=' . $newId . '&saved=1');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
        }
        if ($action === 'delete_card') {
            $deckId = (int)$_POST['deck_id'];
            $stmt = $pdo->prepare('DELETE FROM flashcards WHERE id=?');
            $stmt->execute([(int)$_POST['id']]);
            header('Location: flashcards.php?edit=' . $deckId);
            exit;
        }
        if ($action === 'save_card') {
            $deckId = (int)$_POST['deck_id'];
            $cardId = (int)($_POST['card_id'] ?? 0);
            $data = [
                'deck_id' => $deckId,
                'front_text' => trim($_POST['front_text'] ?? ''),
                'back_text' => trim($_POST['back_text'] ?? ''),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ];
            if ($data['front_text'] === '' || $data['back_text'] === '') {
                throw new RuntimeException('Le recto et le verso sont obligatoires.');
            }
            // Images du recto et du verso (facultatives).
            $data = array_merge($data, workspace_image_fields('flashcards', $cardId));
            unset($data['deck_id']);
            $colonnes = array_keys($data);
            if ($cardId) {
                $sets = array_map(static fn($c) => "`$c`=?", $colonnes);
                $params = array_map(static fn($c) => $data[$c], $colonnes);
                $params[] = $cardId;
                $pdo->prepare('UPDATE flashcards SET ' . implode(',', $sets) . ' WHERE id=?')->execute($params);
            } else {
                $colonnes[] = 'deck_id';
                $params = array_map(static fn($c) => $data[$c], array_keys($data));
                $params[] = $deckId;
                $pdo->prepare('INSERT INTO flashcards(`' . implode('`,`', $colonnes) . '`) VALUES(' . implode(',', array_fill(0, count($colonnes), '?')) . ')')->execute($params);
            }
            header('Location: flashcards.php?edit=' . $deckId . '&saved=1');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify_text($title, 'flashcards');
        $data = [
            'level_id' => (int)($_POST['level_id'] ?? 0),
            'chapter_id' => $_POST['chapter_id'] !== '' ? (int)$_POST['chapter_id'] : null,
            'title' => $title,
            'slug' => $slug,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $data['id'] = $id;
            $stmt = $pdo->prepare('UPDATE flashcard_decks SET level_id=:level_id, chapter_id=:chapter_id, title=:title, slug=:slug, is_published=:is_published WHERE id=:id');
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare('INSERT INTO flashcard_decks(level_id,chapter_id,title,slug,is_published) VALUES(:level_id,:chapter_id,:title,:slug,:is_published)');
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: flashcards.php?edit=' . $id . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM flashcard_decks WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$cardEdit = null;
if (!empty($_GET['card'])) {
    $stmt = $pdo->prepare('SELECT * FROM flashcards WHERE id=?');
    $stmt->execute([(int)$_GET['card']]);
    $cardEdit = $stmt->fetch() ?: null;
}

$levels = $pdo->query('SELECT * FROM levels ORDER BY sort_order,id')->fetchAll();
$chapters = $pdo->query('SELECT c.*, l.short AS level_short FROM chapters c JOIN levels l ON l.id=c.level_id ORDER BY l.sort_order,c.number')->fetchAll();
$listFilters = [
    'includeDrafts' => true,
    'level_id' => (int)($_GET['level_id'] ?? 0),
    'status' => $_GET['status'] ?? '',
    'q' => trim($_GET['q'] ?? ''),
];
$decks = get_flashcard_decks($listFilters);
$cards = $edit ? get_flashcards((int)$edit['id']) : [];

page_head('Admin - Flashcards');
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">Flashcards</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Créer des cartes de révision par niveau et par chapitre.</p>
  </section>
  <?php if ($message): ?><p class="mt-5 text-sm font-black text-green-700"><?= h($message) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="mt-5 text-sm font-black text-red-700"><?= h($error) ?></p><?php endif; ?>

  <section class="card mt-8 p-6">
    <h2 class="section-title"><?= $edit ? 'Modifier le deck' : 'Ajouter un deck' ?></h2>
    <form method="post" class="work-filter-panel mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="save_deck">
      <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
      <div class="work-filter-grid">
        <label>Niveau<select name="level_id" required><?php foreach ($levels as $level): ?><option value="<?= (int)$level['id'] ?>" <?= (int)($edit['level_id'] ?? 0)===(int)$level['id']?'selected':'' ?>><?= h($level['short'].' — '.$level['name']) ?></option><?php endforeach; ?></select></label>
        <label>Chapitre<select name="chapter_id"><option value="">Sans chapitre</option><?php foreach ($chapters as $chapter): ?><option value="<?= (int)$chapter['id'] ?>" <?= (int)($edit['chapter_id'] ?? 0)===(int)$chapter['id']?'selected':'' ?>><?= h($chapter['level_short'].' · Ch '.$chapter['number'].' · '.$chapter['title']) ?></option><?php endforeach; ?></select></label>
        <label>Titre<input name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
        <label>Slug<input name="slug" value="<?= h($edit['slug'] ?? '') ?>" placeholder="auto si vide"></label>
      </div>
      <div class="mt-4 flex flex-wrap items-center gap-3">
        <label><input type="checkbox" name="is_published" <?= !isset($edit) || (int)($edit['is_published'] ?? 1) === 1 ? 'checked' : '' ?>> Publié</label>
        <button class="btn-primary" type="submit">Enregistrer</button>
        <?php if ($edit): ?><a class="btn-secondary" href="flashcards.php">Nouveau deck</a><a class="btn-secondary" href="../flashcards.php?slug=<?= h($edit['slug']) ?>">Voir</a><?php endif; ?>
      </div>
    </form>
  </section>

  <?php if ($edit): ?>
    <section class="card mt-8 p-6">
      <h2 class="section-title"><?= $cardEdit ? 'Modifier la carte' : 'Ajouter une carte' ?></h2>
      <form method="post" enctype="multipart/form-data" class="work-filter-panel mt-6">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="save_card">
        <input type="hidden" name="deck_id" value="<?= (int)$edit['id'] ?>">
        <input type="hidden" name="card_id" value="<?= h($cardEdit['id'] ?? '') ?>">
        <div class="work-filter-grid">
          <label>Ordre<input type="number" name="sort_order" value="<?= h($cardEdit['sort_order'] ?? count($cards) + 1) ?>"></label>
        </div>
        <label class="mt-4 block font-bold">Recto<textarea name="front_text" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 p-4 dark:border-slate-700 dark:bg-slate-900" required><?= h($cardEdit['front_text'] ?? '') ?></textarea></label>
        <label class="mt-4 block font-bold">Verso<textarea name="back_text" rows="5" class="mt-2 w-full rounded-2xl border border-slate-200 p-4 dark:border-slate-700 dark:bg-slate-900" required><?= h($cardEdit['back_text'] ?? '') ?></textarea></label>
        <?php ws_image_field($cardEdit ?: [], 'image', 'Image du recto'); ?>
        <?php ws_image_field($cardEdit ?: [], 'image_back', 'Image du verso'); ?>
        <?php latex_help_panel(); ?>
        <div class="mt-4 flex flex-wrap gap-3"><button class="btn-primary" type="submit">Enregistrer la carte</button><?php if ($cardEdit): ?><a class="btn-secondary" href="flashcards.php?edit=<?= (int)$edit['id'] ?>">Annuler</a><?php endif; ?></div>
      </form>
    </section>

    <section class="flashcard-public-grid mt-8">
      <?php foreach ($cards as $card): ?>
        <article class="flashcard-public-card">
          <div class="flashcard-admin-face"><span>Carte <?= (int)$card['sort_order'] ?></span><?= render_text($card['front_text']) ?></div>
          <div><?= render_text($card['back_text']) ?></div>
          <div class="work-actions mt-4"><a href="flashcards.php?edit=<?= (int)$edit['id'] ?>&card=<?= (int)$card['id'] ?>">Modifier</a><form method="post" onsubmit="return confirm('Supprimer cette carte ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete_card"><input type="hidden" name="deck_id" value="<?= (int)$edit['id'] ?>"><input type="hidden" name="id" value="<?= (int)$card['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <section class="card mt-8 p-6">
    <div class="admin-list-header">
      <div><span class="badge">Liste</span><h2 class="section-title mt-3">Decks existants</h2></div>
      <a class="btn-secondary" href="flashcards.php">Réinitialiser</a>
    </div>
    <form method="get" class="work-filter-panel mt-5">
      <div class="work-filter-grid">
        <label>Niveau<select name="level_id"><option value="0">Tous</option><?php foreach ($levels as $level): ?><option value="<?= (int)$level['id'] ?>" <?= (int)$listFilters['level_id']===(int)$level['id']?'selected':'' ?>><?= h($level['short'].' — '.$level['name']) ?></option><?php endforeach; ?></select></label>
        <label>Statut<select name="status"><option value="">Tous</option><option value="1" <?= $listFilters['status']==='1'?'selected':'' ?>>Publié</option><option value="0" <?= $listFilters['status']==='0'?'selected':'' ?>>Brouillon</option></select></label>
        <label>Recherche<input type="search" name="q" value="<?= h($listFilters['q']) ?>" placeholder="Titre, chapitre..."></label>
        <div class="filter-submit"><button class="btn-primary" type="submit">Filtrer</button></div>
      </div>
    </form>
  </section>

  <section class="chapter-grid mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($decks as $deck): ?>
      <article class="chapter-tile" data-card style="--card-accent:<?= h($deck['accent']) ?>">
        <span class="chapter-number"><?= h($deck['level_short']) ?> · <?= (int)$deck['card_count'] ?> carte(s)</span>
        <h3><?= h($deck['title']) ?></h3>
        <p><?= h($deck['chapter_title'] ?: 'Sans chapitre') ?> · <strong class="status-pill <?= (int)$deck['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$deck['is_published'] ? 'Publié' : 'Brouillon' ?></strong></p>
        <div class="chapter-actions"><a href="flashcards.php?edit=<?= (int)$deck['id'] ?>">Modifier</a><a href="../flashcards.php?slug=<?= h($deck['slug']) ?>">Voir</a><form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="duplicate_deck"><input type="hidden" name="id" value="<?= (int)$deck['id'] ?>"><button class="btn-secondary" type="submit">Dupliquer</button></form><form method="post" onsubmit="return confirm('Supprimer ce deck ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete_deck"><input type="hidden" name="id" value="<?= (int)$deck['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
</body>
</html>
