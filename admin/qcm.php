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
    $action = $_POST['action'] ?? 'save_qcm';
    try {
        if ($action === 'delete_qcm') {
            $stmt = $pdo->prepare('DELETE FROM qcms WHERE id=?');
            $stmt->execute([(int)$_POST['id']]);
            header('Location: qcm.php');
            exit;
        }
        if ($action === 'duplicate_qcm') {
            $sourceId = (int)$_POST['id'];
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('SELECT * FROM qcms WHERE id=?');
                $stmt->execute([$sourceId]);
                $source = $stmt->fetch();
                if (!$source) throw new RuntimeException('QCM introuvable.');
                $slug = admin_unique_slug($pdo, 'qcms', $source['slug']);
                $pdo->prepare('INSERT INTO qcms(chapter_id,level_id,title,slug,is_published) VALUES(?,?,?,?,0)')->execute([$source['chapter_id'],$source['level_id'],'Copie de '.$source['title'],$slug]);
                $newQcmId = (int)$pdo->lastInsertId();
                $questions = $pdo->prepare('SELECT * FROM qcm_questions WHERE qcm_id=? ORDER BY id');
                $questions->execute([$sourceId]);
                foreach ($questions->fetchAll() as $question) {
                    $pdo->prepare('INSERT INTO qcm_questions(qcm_id,question_text,explanation,sort_order) VALUES(?,?,?,?)')->execute([$newQcmId,$question['question_text'],$question['explanation'],$question['sort_order']]);
                    $newQuestionId = (int)$pdo->lastInsertId();
                    $pdo->prepare('INSERT INTO qcm_choices(question_id,choice_label,choice_text,is_correct,sort_order) SELECT ?,choice_label,choice_text,is_correct,sort_order FROM qcm_choices WHERE question_id=?')->execute([$newQuestionId,$question['id']]);
                }
                $pdo->commit();
                header('Location: qcm.php?edit=' . $newQcmId . '&saved=1');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
        }
        if ($action === 'delete_question') {
            $qcmId = (int)$_POST['qcm_id'];
            $stmt = $pdo->prepare('DELETE FROM qcm_questions WHERE id=?');
            $stmt->execute([(int)$_POST['id']]);
            header('Location: qcm.php?edit=' . $qcmId);
            exit;
        }
        if ($action === 'save_question') {
            $qcmId = (int)$_POST['qcm_id'];
            $questionId = (int)($_POST['question_id'] ?? 0);
            $questionData = [
                'qcm_id' => $qcmId,
                'question_text' => trim($_POST['question_text'] ?? ''),
                'explanation' => trim($_POST['explanation'] ?? ''),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ];
            $questionData = array_merge($questionData, workspace_image_fields('qcm', $questionId));
            unset($questionData['qcm_id']);
            if ($questionId) {
                $sets = array_map(static fn($c) => "`$c`=?", array_keys($questionData));
                $params = array_map(static fn($c) => $questionData[$c], array_keys($questionData));
                $params[] = $questionId;
                $pdo->prepare('UPDATE qcm_questions SET ' . implode(',', $sets) . ' WHERE id=?')->execute($params);
            } else {
                $cols = array_keys($questionData);
                $cols[] = 'qcm_id';
                $params = array_map(static fn($c) => $questionData[$c], array_keys($questionData));
                $params[] = $qcmId;
                $pdo->prepare('INSERT INTO qcm_questions(`' . implode('`,`', $cols) . '`) VALUES(' . implode(',', array_fill(0, count($cols), '?')) . ')')->execute($params);
                $questionId = (int)$pdo->lastInsertId();
            }
            $pdo->prepare('DELETE FROM qcm_choices WHERE question_id=?')->execute([$questionId]);
            $correct = (string)($_POST['correct_label'] ?? 'A');
            $remplies = [];
            foreach (['A','B','C','D','E','F'] as $index => $label) {
                $text = trim($_POST['choice_' . $label] ?? '');
                if ($text === '') continue;
                $remplies[$label] = $text;
            }
            if (count($remplies) < 2) {
                throw new RuntimeException('Indiquez au moins deux réponses (A et B au minimum).');
            }
            if (!isset($remplies[$correct])) {
                throw new RuntimeException('La bonne réponse doit être l’une des réponses saisies.');
            }
            foreach ($remplies as $label => $text) {
                $pdo->prepare('INSERT INTO qcm_choices(question_id,choice_label,choice_text,is_correct,sort_order) VALUES(?,?,?,?,?)')
                    ->execute([$questionId, $label, $text, $correct === $label ? 1 : 0, ord($label) - 64]);
            }
            header('Location: qcm.php?edit=' . $qcmId . '&saved=1');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify_text($title, 'qcm');
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
            $stmt = $pdo->prepare('UPDATE qcms SET level_id=:level_id, chapter_id=:chapter_id, title=:title, slug=:slug, is_published=:is_published WHERE id=:id');
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare('INSERT INTO qcms(level_id,chapter_id,title,slug,is_published) VALUES(:level_id,:chapter_id,:title,:slug,:is_published)');
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: qcm.php?edit=' . $id . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM qcms WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$questionEdit = null;
if (!empty($_GET['question'])) {
    $stmt = $pdo->prepare('SELECT * FROM qcm_questions WHERE id=?');
    $stmt->execute([(int)$_GET['question']]);
    $questionEdit = $stmt->fetch() ?: null;
}
$choiceValues = ['A'=>'', 'B'=>'', 'C'=>'', 'D'=>''];
$correctLabel = 'A';
if ($questionEdit) {
    $stmt = $pdo->prepare('SELECT * FROM qcm_choices WHERE question_id=? ORDER BY sort_order,id');
    $stmt->execute([(int)$questionEdit['id']]);
    foreach ($stmt->fetchAll() as $choice) {
        $choiceValues[$choice['choice_label']] = $choice['choice_text'];
        if ((int)$choice['is_correct'] === 1) $correctLabel = $choice['choice_label'];
    }
}

$levels = $pdo->query('SELECT * FROM levels ORDER BY sort_order,id')->fetchAll();
$chapters = $pdo->query('SELECT c.*, l.short AS level_short FROM chapters c JOIN levels l ON l.id=c.level_id ORDER BY l.sort_order,c.number')->fetchAll();
$listFilters = [
    'includeDrafts' => true,
    'level_id' => (int)($_GET['level_id'] ?? 0),
    'status' => $_GET['status'] ?? '',
    'q' => trim($_GET['q'] ?? ''),
];
$qcms = get_qcms($listFilters);
$questions = $edit ? get_qcm_questions((int)$edit['id']) : [];

page_head('Admin - QCM');
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5 p-6 sm:p-9">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">QCM</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Créer des quiz corrigés automatiquement. La bonne réponse apparaît en vert côté élève.</p>
  </section>
  <?php if ($message): ?><p class="mt-5 text-sm font-black text-green-700"><?= h($message) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="mt-5 text-sm font-black text-red-700"><?= h($error) ?></p><?php endif; ?>

  <section class="card mt-8 p-6">
    <h2 class="section-title"><?= $edit ? 'Modifier le QCM' : 'Ajouter un QCM' ?></h2>
    <form method="post" class="work-filter-panel mt-6">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="save_qcm">
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
        <?php if ($edit): ?><a class="btn-secondary" href="qcm.php">Nouveau QCM</a><a class="btn-secondary" href="../qcm.php?slug=<?= h($edit['slug']) ?>">Voir</a><?php endif; ?>
      </div>
    </form>
  </section>

  <?php if ($edit): ?>
    <section class="card mt-8 p-6">
      <h2 class="section-title"><?= $questionEdit ? 'Modifier la question' : 'Ajouter une question' ?></h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="save_question">
        <input type="hidden" name="qcm_id" value="<?= (int)$edit['id'] ?>">
        <input type="hidden" name="question_id" value="<?= h($questionEdit['id'] ?? '') ?>">
        <div class="ws-form">
          <label class="ws-form-field">Question
            <textarea name="question_text" rows="3" required><?= h($questionEdit['question_text'] ?? '') ?></textarea>
          </label>
          <?php latex_help_panel(); ?>

          <fieldset class="ws-answers">
            <legend>Réponses <span class="ws-answers-hint">deux au minimum</span></legend>
            <div class="ws-form-duo">
              <?php foreach (['A','B'] as $label): ?>
                <label class="ws-form-field">Réponse <?= $label ?>
                  <textarea class="qcm-choice-editor" data-editor="off" name="choice_<?= $label ?>" rows="2" placeholder="Ex : $x^2$"><?= h($choiceValues[$label]) ?></textarea>
                </label>
              <?php endforeach; ?>
            </div>
            <details class="ws-answers-extra">
              <summary>Réponses supplémentaires (C à F)</summary>
              <div class="ws-form-duo">
                <?php foreach (['C','D','E','F'] as $label): ?>
                  <label class="ws-form-field">Réponse <?= $label ?>
                    <textarea class="qcm-choice-editor" data-editor="off" name="choice_<?= $label ?>" rows="2" placeholder="Laisser vide si inutile"><?= h($choiceValues[$label]) ?></textarea>
                  </label>
                <?php endforeach; ?>
              </div>
            </details>
            <div class="ws-form-duo">
              <label class="ws-form-field">Bonne réponse
                <select name="correct_label"><?php foreach (['A','B','C','D','E','F'] as $label): ?><option value="<?= $label ?>" <?= $correctLabel===$label?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select>
              </label>
              <label class="ws-form-field">Ordre
                <input type="number" name="sort_order" value="<?= h($questionEdit['sort_order'] ?? count($questions) + 1) ?>">
              </label>
            </div>
          </fieldset>

          <?php ws_image_field($questionEdit ?: [], 'image', 'Image de la question'); ?>

          <label class="ws-form-field">Explication
            <textarea name="explanation" rows="3"><?= h($questionEdit['explanation'] ?? '') ?></textarea>
          </label>
        </div>
        <div class="mt-4 flex flex-wrap gap-3"><button class="btn-primary" type="submit">Enregistrer la question</button><?php if ($questionEdit): ?><a class="btn-secondary" href="qcm.php?edit=<?= (int)$edit['id'] ?>">Annuler</a><?php endif; ?></div>
      </form>
    </section>

    <section class="work-board mt-8">
      <?php foreach ($questions as $question): ?>
        <article class="qcm-question-card">
          <span class="chapter-number">Question <?= (int)$question['sort_order'] ?></span>
          <h2><?= render_text($question['question_text']) ?></h2>
          <div class="qcm-choice-grid"><?php foreach ($question['choices'] as $choice): ?><div class="qcm-choice <?= (int)$choice['is_correct'] ? 'is-correct' : '' ?>"><span class="qcm-choice-letter"><?= h($choice['choice_label']) ?></span><span><?= render_text($choice['choice_text']) ?></span></div><?php endforeach; ?></div>
          <div class="work-actions mt-4"><a href="qcm.php?edit=<?= (int)$edit['id'] ?>&question=<?= (int)$question['id'] ?>">Modifier</a><form method="post" onsubmit="return confirm('Supprimer cette question ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete_question"><input type="hidden" name="qcm_id" value="<?= (int)$edit['id'] ?>"><input type="hidden" name="id" value="<?= (int)$question['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <section class="card mt-8 p-6">
    <div class="admin-list-header">
      <div><span class="badge">Liste</span><h2 class="section-title mt-3">QCM existants</h2></div>
      <a class="btn-secondary" href="qcm.php">Réinitialiser</a>
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
    <?php foreach ($qcms as $qcm): ?>
      <article class="chapter-tile" data-card style="--card-accent:<?= h($qcm['accent']) ?>">
        <span class="chapter-number"><?= h($qcm['level_short']) ?> · <strong class="status-pill <?= (int)$qcm['is_published'] ? 'is-live' : 'is-draft' ?>"><?= (int)$qcm['is_published'] ? 'Publié' : 'Brouillon' ?></strong></span>
        <h3><?= h($qcm['title']) ?></h3>
        <p><?= h($qcm['chapter_title'] ?: 'Sans chapitre') ?></p>
        <div class="chapter-actions"><a href="qcm.php?edit=<?= (int)$qcm['id'] ?>">Modifier</a><a href="../qcm.php?slug=<?= h($qcm['slug']) ?>">Voir</a><form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="duplicate_qcm"><input type="hidden" name="id" value="<?= (int)$qcm['id'] ?>"><button class="btn-secondary" type="submit">Dupliquer</button></form><form method="post" onsubmit="return confirm('Supprimer ce QCM ?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete_qcm"><input type="hidden" name="id" value="<?= (int)$qcm['id'] ?>"><button class="btn-secondary" type="submit">Supprimer</button></form></div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
</body>
</html>
