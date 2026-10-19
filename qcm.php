<?php
require_once __DIR__ . '/includes/repository.php';

$slug = trim($_GET['slug'] ?? '');
$levels = get_levels();

if ($slug !== '') {
    $preview = admin_preview_active();
    $qcm = get_qcm($slug, $preview);
    if (!$qcm) {
        http_response_code(404);
        $qcm = ['title' => 'QCM introuvable'];
    }
    $questions = !empty($qcm['id']) ? get_qcm_questions((int)$qcm['id']) : [];
    $answers = $_POST['answers'] ?? [];
    $submitted = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

    // Identite facultative, memorisee d'une fois sur l'autre pour ne pas la ressaisir.
    $studentName = trim((string)($_POST['student_name'] ?? $_COOKIE['qcm_name'] ?? ''));
    $studentClass = trim((string)($_POST['student_class'] ?? $_COOKIE['qcm_class'] ?? ''));
    $tracking = !empty($qcm['id']) && qcm_attempts_available();

    $score = 0;
    $detail = [];
    foreach ($questions as $question) {
        $given = $answers[$question['id']] ?? null;
        $good = false;
        foreach ($question['choices'] as $choice) {
            if ((int)$choice['is_correct'] === 1 && $given === $choice['choice_label']) $good = true;
        }
        if ($good) $score++;
        $detail[] = [
            'question_id' => (int)$question['id'],
            'choice_label' => $given !== null ? (string)$given : null,
            'is_correct' => $good,
        ];
    }
    $total = count($questions);
    $percent = $total > 0 ? (int)round($score / $total * 100) : 0;
    $appreciation = $percent >= 80 ? 'Très bonne maîtrise' : ($percent >= 50 ? 'À consolider' : 'À retravailler');

    // Enregistrement de la tentative (suivi pedagogique).
    $attemptSaved = false;
    if ($submitted && $tracking && $total > 0) {
        $startedAt = (int)($_POST['started_at'] ?? 0);
        $duration = $startedAt > 0 ? max(0, min(86400, time() - $startedAt)) : 0;
        $attemptSaved = save_qcm_attempt((int)$qcm['id'], $detail, $score, $total, $studentName, $studentClass, $duration) !== null;
        if ($studentName !== '' || $studentClass !== '') {
            setcookie('qcm_name', $studentName, ['expires' => time() + 31536000, 'path' => '/', 'samesite' => 'Lax']);
            setcookie('qcm_class', $studentClass, ['expires' => time() + 31536000, 'path' => '/', 'samesite' => 'Lax']);
        }
    }

    // Questions a revoir, avec le lien vers le chapitre correspondant.
    $toReview = [];
    if ($submitted) {
        foreach ($questions as $index => $question) {
            if (!empty($detail[$index]['is_correct'])) continue;
            $toReview[] = ['index' => $index + 1, 'text' => trim(strip_tags((string)$question['question_text']))];
        }
    }

    page_head($qcm['title'] ?? 'QCM', '', ['accent' => (string)($qcm['accent'] ?? '')]);
    site_header();
    if ($preview && !empty($qcm['id'])) admin_preview_banner(base_path('admin/chapitre.php?chapter_id=' . (int)$qcm['chapter_id'] . '&tab=qcm&edit=' . (int)$qcm['id']));
    ?>
    <main class="page-container student-page qcm-page" style="--accent:<?= h($qcm['accent'] ?? '#2563eb') ?>">
      <a class="nav-link" href="<?= h(base_path('qcm.php')) ?>">← Tous les QCM</a>
      <section class="work-global-hero mt-5">
        <span class="badge"><?= h(($qcm['level_short'] ?? '') . (!empty($qcm['chapter_title']) ? ' · ' . $qcm['chapter_title'] : '')) ?></span>
        <h1 class="page-title mt-4"><?= h($qcm['title']) ?></h1>
        <div class="qcm-hero-meta">
          <span><strong><?= $total ?></strong> question<?= $total > 1 ? 's' : '' ?></span>
          <span>Correction immédiate</span>
          <span>Réponses justifiées</span>
        </div>
      </section>

      <?php if (!$questions): ?>
        <section class="student-empty mt-8">
          <h2>Aucune question publiée</h2>
          <p>Ce QCM est encore en préparation. Revenez plus tard.</p>
          <a class="btn-secondary" href="<?= h(base_path('qcm.php')) ?>">Voir les autres QCM</a>
        </section>
      <?php else: ?>
        <?php if ($submitted): ?>
          <section class="qcm-result" aria-live="polite">
            <div class="qcm-result-score">
              <span class="qcm-result-value"><?= (int)$score ?><small> / <?= $total ?></small></span>
              <span class="qcm-result-percent"><?= $percent ?> %</span>
            </div>
            <div class="qcm-result-copy">
              <strong><?= h($appreciation) ?></strong>
              <p>Les bonnes réponses sont surlignées en vert, vos erreurs en rouge, avec une explication quand elle existe.</p>
              <?php if ($tracking): ?>
                <p class="qcm-result-tracking"><?= $attemptSaved ? 'Résultat enregistré pour ton professeur.' : 'Résultat non enregistré : préviens ton professeur.' ?></p>
              <?php endif; ?>
            </div>
            <a class="btn-secondary" href="<?= h(base_path('qcm.php?slug=' . $qcm['slug'])) ?>">Recommencer</a>
          </section>

          <?php if ($toReview): ?>
            <section class="qcm-review mt-6" aria-labelledby="qcm-review-title">
              <h2 id="qcm-review-title" class="section-title">À revoir (<?= count($toReview) ?> question<?= count($toReview) > 1 ? 's' : '' ?>)</h2>
              <ul class="qcm-review-list">
                <?php foreach ($toReview as $item): ?>
                  <li><span class="qcm-review-num"><?= (int)$item['index'] ?></span><span><?= h(mb_strimwidth($item['text'], 0, 140, '…')) ?></span></li>
                <?php endforeach; ?>
              </ul>
              <p class="muted">
                Reprends le cours correspondant :
                <?php if (!empty($qcm['chapter_slug'])): ?><a href="<?= h(base_path('chapitre.php?slug=' . $qcm['chapter_slug'])) ?>">chapitre « <?= h($qcm['chapter_title']) ?> »</a><?php endif; ?>
                <?php if (!empty($qcm['level_slug'])): ?> · <a href="<?= h(base_path('methodes.php')) ?>">fiches méthodes</a><?php endif; ?>
              </p>
            </section>
          <?php endif; ?>
        <?php endif; ?>

        <form method="post" class="qcm-public-list mt-8" data-qcm-form>
          <input type="hidden" name="started_at" value="<?= time() ?>">
          <?php if ($tracking && !$submitted): ?>
            <fieldset class="qcm-identity">
              <legend>Pour ton professeur <span class="qcm-identity-hint">(facultatif)</span></legend>
              <div class="qcm-identity-grid">
                <label>Prénom et nom
                  <input name="student_name" value="<?= h($studentName) ?>" autocomplete="name" maxlength="120" placeholder="Ex : Camille Dupont">
                </label>
                <label>Classe
                  <input name="student_class" value="<?= h($studentClass) ?>" maxlength="60" placeholder="Ex : 3eB">
                </label>
              </div>
              <p class="qcm-identity-note">Ces informations servent uniquement à ce que ton professeur voie où la classe a des difficultés. Tu peux laisser vide.</p>
            </fieldset>
          <?php endif; ?>
          <?php if ($tracking && $submitted): ?>
            <input type="hidden" name="student_name" value="<?= h($studentName) ?>">
            <input type="hidden" name="student_class" value="<?= h($studentClass) ?>">
          <?php endif; ?>
          <?php foreach ($questions as $index => $question): ?>
            <article class="qcm-question-card">
              <header class="qcm-question-head">
                <span class="chapter-number">Question <?= (int)$index + 1 ?></span>
                <?php if ($submitted): ?>
                  <span class="status-pill <?= !empty($detail[$index]['is_correct']) ? 'is-qcm-good' : 'is-qcm-bad' ?>"><?= !empty($detail[$index]['is_correct']) ? 'Réussi' : 'À revoir' ?></span>
                <?php endif; ?>
              </header>
              <?php $imageQuestion = (string)($question['image_path'] ?? ''); ?>
              <?php if ($imageQuestion !== ''): ?>
                <p class="qcm-question-image"><img src="./<?= h($imageQuestion) ?>" alt="Illustration de la question" style="width:<?= max(10, min(100, (int)($question['image_width'] ?? 40))) ?>%"></p>
              <?php endif; ?>
              <h2><?= render_text($question['question_text']) ?></h2>
              <div class="qcm-choice-grid">
                <?php foreach ($question['choices'] as $choice): ?>
                  <?php
                    $selected = ($answers[$question['id']] ?? '') === $choice['choice_label'];
                    $correct = (int)$choice['is_correct'] === 1;
                    $class = $submitted && $correct ? ' is-correct' : ($submitted && $selected ? ' is-wrong' : '');
                  ?>
                  <label class="qcm-choice<?= $class ?>">
                    <input type="radio" name="answers[<?= (int)$question['id'] ?>]" value="<?= h($choice['choice_label']) ?>" <?= $selected ? 'checked' : '' ?>>
                    <span class="qcm-choice-letter" aria-hidden="true"><?= h($choice['choice_label']) ?></span>
                    <span><?= render_text($choice['choice_text']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <?php if ($submitted && trim((string)$question['explanation']) !== ''): ?>
                <div class="qcm-explanation"><strong>Explication — </strong><?= render_text($question['explanation']) ?></div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
          <div class="qcm-actions" data-qcm-actions>
            <p class="qcm-progress" data-qcm-progress role="status" aria-live="polite">0 réponse sur <?= $total ?></p>
            <button class="btn-primary btn-lg" type="submit"><?= $submitted ? 'Corriger à nouveau' : 'Corriger mes réponses' ?></button>
          </div>
        </form>
        <script src="<?= h(base_path('assets/js/qcm-form.js')) ?>" defer></script>
      <?php endif; ?>
    </main>
    <?php site_footer(); exit;
}

$filters = ['level' => trim($_GET['level'] ?? ''), 'q' => trim($_GET['q'] ?? '')];
$qcms = get_qcms($filters);
page_head('QCM');
site_header();
?>
<main class="page-container student-page activity-library-page">
  <a class="nav-link" href="<?= h(base_path('index.php')) ?>">← Accueil</a>
  <section class="work-global-hero mt-5">
    <span class="badge">Entraînement</span>
    <h1 class="page-title mt-4">QCM</h1>
    <p class="muted">Choisis un chapitre, réponds aux questions puis compare avec la correction expliquée.</p>
  </section>
  <form method="get" class="work-filter-panel mt-8">
    <div class="work-filter-grid">
      <label>Niveau<select name="level"><option value="">Tous</option><?php foreach ($levels as $level): ?><option value="<?= h($level['slug']) ?>" <?= ($filters['level'] === $level['slug']) ? 'selected' : '' ?>><?= h($level['short'] . ' — ' . $level['name']) ?></option><?php endforeach; ?></select></label>
      <label>Recherche<input type="search" name="q" value="<?= h($filters['q']) ?>" placeholder="Chapitre, notion…"></label>
      <div class="filter-submit"><button class="btn-primary" type="submit">Filtrer</button></div>
    </div>
    <?php if ($filters['level'] !== '' || $filters['q'] !== ''): ?><a class="filter-reset" href="<?= h(base_path('qcm.php')) ?>">Réinitialiser les filtres</a><?php endif; ?>
  </form>
  <section class="chapter-grid mt-8" aria-labelledby="qcm-list-title">
    <h2 id="qcm-list-title" class="sr-only">Liste des QCM disponibles</h2>
    <?php foreach ($qcms as $qcm): ?>
      <article class="chapter-tile" data-card style="--card-accent:<?= h($qcm['accent']) ?>">
        <span class="chapter-number"><?= h($qcm['level_short']) ?><?= !empty($qcm['chapter_title']) ? ' · ' . h($qcm['chapter_title']) : '' ?></span>
        <h3><?= h($qcm['title']) ?></h3>
        <p>Correction immédiate et bonne réponse mise en valeur.</p>
        <div class="chapter-actions"><a href="<?= h(base_path('qcm.php?slug=' . $qcm['slug'])) ?>">Démarrer</a></div>
      </article>
    <?php endforeach; ?>
  </section>
  <?php if (!$qcms): ?>
    <div class="student-empty mt-8">
      <h2>Aucun QCM disponible</h2>
      <p>Les QCM apparaîtront ici dès leur publication depuis l’administration.</p>
      <a class="btn-secondary" href="<?= h(base_path('index.php')) ?>">Retour aux niveaux</a>
    </div>
  <?php endif; ?>
</main>
<?php site_footer(); ?>
