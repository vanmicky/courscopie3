<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = db();
if (!$pdo) {
    page_head('Admin indisponible', '', ['math' => false]);
    echo '<main class="page-container"><p class="notice-warning mt-8">Base non configurée.</p></main></body></html>';
    exit;
}

$filters = [
    'level' => trim((string)($_GET['level'] ?? '')),
    'qcm_id' => (int)($_GET['qcm'] ?? 0),
    'class' => trim((string)($_GET['classe'] ?? '')),
    'since' => trim((string)($_GET['depuis'] ?? '')),
];
$available = qcm_attempts_available();

$attempts = $available ? get_qcm_attempts($filters, 300) : [];
$summary = $available ? get_qcm_summary($filters) : [];
$questionStats = $available && $filters['qcm_id'] ? get_qcm_question_stats($filters['qcm_id']) : [];
$classes = $available ? get_attempt_classes() : [];
$levels = get_levels();
$qcms = get_qcms([]);

/* --- Export CSV ---------------------------------------------------------- */
if ($available && ($_GET['export'] ?? '') === 'csv') {
    $rows = get_qcm_attempts($filters, 1000);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="resultats-qcm-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Date', 'Classe', 'Élève', 'Niveau', 'QCM', 'Score', 'Total', 'Pourcentage', 'Durée (s)'], ';');
    foreach ($rows as $row) {
        $total = (int)$row['total'];
        fputcsv($out, [
            $row['created_at'],
            $row['student_class'],
            $row['student_name'],
            $row['level_short'],
            $row['qcm_title'],
            (int)$row['score'],
            $total,
            $total > 0 ? (int)round((int)$row['score'] / $total * 100) : 0,
            (int)$row['duration_seconds'],
        ], ';');
    }
    fclose($out);
    exit;
}

/* --- Indicateurs globaux ------------------------------------------------- */
$totalAttempts = count($attempts);
$students = [];
$scoreSum = 0;
$scoreMax = 0;
foreach ($attempts as $row) {
    $key = trim($row['student_class'] . '|' . $row['student_name'], '|');
    if ($key !== '') $students[$key] = true;
    $scoreSum += (int)$row['score'];
    $scoreMax += (int)$row['total'];
}
$average = $scoreMax > 0 ? round($scoreSum / $scoreMax * 100, 1) : null;

// Questions les plus ratées pour le QCM sélectionné.
$hardest = array_values(array_filter($questionStats, static fn($q) => (int)$q['answers_count'] > 0));
usort($hardest, static function ($a, $b) {
    $ra = (int)$a['answers_count'] > 0 ? (int)$a['correct_count'] / (int)$a['answers_count'] : 1;
    $rb = (int)$b['answers_count'] > 0 ? (int)$b['correct_count'] / (int)$b['answers_count'] : 1;
    return $ra <=> $rb;
});

$selectedQcmTitle = '';
foreach ($qcms as $qcm) {
    if ((int)$qcm['id'] === $filters['qcm_id']) { $selectedQcmTitle = (string)$qcm['title']; break; }
}

$query = static function (array $overrides = []) use ($filters): string {
    $params = array_filter(array_merge([
        'level' => $filters['level'],
        'qcm' => $filters['qcm_id'] ?: '',
        'classe' => $filters['class'],
        'depuis' => $filters['since'],
    ], $overrides), static fn($v) => $v !== '' && $v !== 0);
    return $params ? '?' . http_build_query($params) : '';
};

page_head('Admin - Résultats des QCM', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container">
  <a class="nav-link" href="dashboard.php">← Tableau de bord</a>
  <section class="work-global-hero mt-5">
    <span class="badge">Suivi</span>
    <h1 class="page-title mt-4">Résultats des QCM</h1>
    <p class="muted">Ce que les élèves ont réellement répondu : taux de réussite par QCM et par question, pour repérer ce qui doit être revu en classe.</p>
  </section>

  <?php if (!$available): ?>
    <p class="notice-warning mt-6">
      La table des résultats n'existe pas encore. Importez <code>database/migrate_v74_to_v75.sql</code> dans phpMyAdmin :
      les tentatives seront enregistrées à partir de ce moment.
    </p>
  <?php else: ?>

  <form method="get" class="work-filter-panel mt-6">
    <div class="work-filter-grid">
      <label>Niveau
        <select name="level">
          <option value="">Tous</option>
          <?php foreach ($levels as $level): ?>
            <option value="<?= h($level['slug']) ?>" <?= $filters['level'] === $level['slug'] ? 'selected' : '' ?>><?= h($level['short'] . ' — ' . $level['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>QCM
        <select name="qcm">
          <option value="0">Tous</option>
          <?php foreach ($qcms as $qcm): ?>
            <option value="<?= (int)$qcm['id'] ?>" <?= $filters['qcm_id'] === (int)$qcm['id'] ? 'selected' : '' ?>><?= h($qcm['level_short'] . ' · ' . $qcm['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Classe
        <select name="classe">
          <option value="">Toutes</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= h($class) ?>" <?= $filters['class'] === $class ? 'selected' : '' ?>><?= h($class) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Depuis le
        <input type="date" name="depuis" value="<?= h($filters['since']) ?>">
      </label>
      <div class="filter-submit"><button class="btn-primary" type="submit">Filtrer</button></div>
    </div>
    <div class="flex flex-wrap gap-3 mt-3">
      <a class="filter-reset" href="resultats.php">Réinitialiser</a>
      <a class="filter-reset" href="resultats.php<?= h($query(['export' => 'csv'])) ?>">Télécharger en CSV</a>
    </div>
  </form>

  <section class="stat-grid mt-6">
    <div class="stat-card"><span class="stat-label">Tentatives</span><span class="stat-value"><?= $totalAttempts ?></span><span class="stat-hint"><?= count($attempts) >= 300 ? '300 plus récentes' : 'sur la période' ?></span></div>
    <div class="stat-card"><span class="stat-label">Élèves</span><span class="stat-value"><?= count($students) ?></span><span class="stat-hint">identifiés</span></div>
    <div class="stat-card"><span class="stat-label">Réussite moyenne</span><span class="stat-value"><?= $average !== null ? h((string)$average) . ' %' : '—' ?></span><span class="stat-hint">toutes questions</span></div>
    <a class="stat-card" href="qcm.php"><span class="stat-label">QCM publiés</span><span class="stat-value"><?= count($qcms) ?></span><span class="stat-hint">Gérer</span></a>
  </section>

  <?php if ($filters['qcm_id'] && $hardest): ?>
    <section class="card mt-8">
      <h2 class="section-title">Questions les plus ratées — <?= h($selectedQcmTitle) ?></h2>
      <div class="document-table-shell mt-5">
        <div class="document-table-scroll">
          <table class="document-data-table">
            <thead><tr><th>Question</th><th>Réponses</th><th>Réussite</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($hardest as $index => $stat):
                $count = (int)$stat['answers_count'];
                $correct = (int)$stat['correct_count'];
                $percent = $count > 0 ? (int)round($correct / $count * 100) : 0;
                $tone = $percent >= 70 ? 'is-live' : ($percent >= 40 ? 'is-draft' : 'is-danger'); ?>
                <tr>
                  <td><?= render_text(mb_substr(trim(strip_tags((string)$stat['question_text'])), 0, 90)) ?></td>
                  <td><?= $count ?></td>
                  <td><span class="status-pill <?= $tone ?>"><?= $percent ?> %</span></td>
                  <td>
                    <div class="completion-bar <?= $percent < 40 ? 'is-danger' : ($percent < 70 ? 'is-warning' : '') ?>"><span style="width:<?= $percent ?>%"></span></div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="card mt-8">
    <h2 class="section-title">Par QCM</h2>
    <div class="document-table-shell mt-5">
      <div class="document-table-scroll">
        <table class="document-data-table">
          <thead><tr><th>QCM</th><th>Niveau</th><th>Tentatives</th><th>Élèves</th><th>Moyenne</th><th>Meilleur</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($summary as $row): $attemptsCount = (int)$row['attempts']; ?>
              <tr>
                <td><a href="resultats.php<?= h($query(['qcm' => (int)$row['id']])) ?>"><?= h($row['title']) ?></a></td>
                <td><?= h($row['level_short']) ?></td>
                <td><?= $attemptsCount ?></td>
                <td><?= (int)$row['students'] ?></td>
                <td><?= $row['average_percent'] !== null ? h((string)$row['average_percent']) . ' %' : '—' ?></td>
                <td><?= $row['best_percent'] !== null ? h((string)$row['best_percent']) . ' %' : '—' ?></td>
                <td><?php if ($attemptsCount): ?><a class="btn-secondary btn-sm" href="resultats.php<?= h($query(['qcm' => (int)$row['id']])) ?>">Détail</a><?php endif; ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$summary): ?><tr><td colspan="7" class="document-table-empty">Aucun QCM pour le moment.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="card mt-8">
    <h2 class="section-title">Dernières tentatives</h2>
    <div class="document-table-shell mt-5">
      <div class="document-table-scroll">
        <table class="document-data-table">
          <thead><tr><th>Date</th><th>Classe</th><th>Élève</th><th>QCM</th><th>Score</th><th>Durée</th></tr></thead>
          <tbody>
            <?php foreach ($attempts as $row): $total = (int)$row['total']; $percent = $total > 0 ? (int)round((int)$row['score'] / $total * 100) : 0; ?>
              <tr>
                <td><?= h(date('d/m/Y H:i', strtotime((string)$row['created_at']))) ?></td>
                <td><?= h($row['student_class'] ?: '—') ?></td>
                <td><?= h($row['student_name'] ?: 'Anonyme') ?></td>
                <td><a href="../qcm.php?slug=<?= h($row['qcm_slug']) ?>" target="_blank" rel="noopener"><?= h($row['qcm_title']) ?></a></td>
                <td><span class="status-pill <?= $percent >= 70 ? 'is-live' : ($percent >= 40 ? 'is-draft' : 'is-danger') ?>"><?= (int)$row['score'] ?> / <?= $total ?></span></td>
                <td><?= (int)$row['duration_seconds'] > 0 ? h(gmdate('i:s', (int)$row['duration_seconds'])) : '—' ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$attempts): ?><tr><td colspan="6" class="document-table-empty">Aucune tentative enregistrée pour ces filtres.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <?php endif; ?>
</main>
</body>
</html>
