<?php
require_once __DIR__ . '/auth.php';
$admin = require_admin();
require_once __DIR__ . '/chapter-insights.php';
admin_ensure_v69_schema();
$counts = ['levels'=>0,'subjects'=>0,'chapters'=>0,'assignments'=>0,'files'=>0,'chapter_videos'=>0,'chapter_skills'=>0,'qcms'=>0,'flashcard_decks'=>0,'exercises'=>0];
try { foreach ($counts as $table=>$v) $counts[$table]=(int)db()->query('SELECT COUNT(*) FROM '.$table)->fetchColumn(); } catch (Throwable $e) {}
$readyChapters = 0; $attentionChapters = 0;
try {
    $dashboardChapters = db()->query('SELECT c.*,l.short AS level_short,l.accent FROM chapters c JOIN levels l ON l.id=c.level_id')->fetchAll();
    foreach ($dashboardChapters as $dashboardChapter) {
        if (chapter_health(db(), $dashboardChapter)['score'] === 100) $readyChapters++; else $attentionChapters++;
    }
} catch (Throwable $e) {}
page_head('Tableau de bord admin', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container">
  <a class="nav-link" href="../index.php">← Site public</a>
  <section class="work-global-hero mt-5">
    <span class="badge">Admin</span>
    <h1 class="page-title mt-4">Tableau de bord</h1>
    <p class="muted">Gérer les niveaux, chapitres, documents, vidéos, exercices et activités interactives.</p>
  </section>
  <section class="mt-6 dashboard-actions" aria-label="Actions rapides">
    <a class="dashboard-action" href="chapitres.php"><?= icon('book', 'dashboard-action-icon', 22) ?><strong>Chapitres</strong><span>Ajouter ou modifier un chapitre</span></a>
    <a class="dashboard-action" href="qcm.php"><?= icon('check', 'dashboard-action-icon', 22) ?><strong>QCM</strong><span>Créer un questionnaire</span></a>
    <a class="dashboard-action" href="fichiers.php"><?= icon('file', 'dashboard-action-icon', 22) ?><strong>Documents</strong><span>Envoyer un PDF ou une image</span></a>
    <a class="dashboard-action" href="resultats.php"><?= icon('chart', 'dashboard-action-icon', 22) ?><strong>Résultats</strong><span>Voir les scores des élèves</span></a>
  </section>
  <section class="dashboard-pilotage mt-8" aria-labelledby="pilotage-title">
    <div>
      <span class="badge">Pilotage</span>
      <h2 id="pilotage-title">Suivre la préparation des chapitres</h2>
      <p>Visualiser les contenus manquants, les brouillons et les dernières modifications.</p>
    </div>
    <div class="dashboard-pilotage-counts">
      <span><strong><?= $readyChapters ?></strong> complet<?= $readyChapters > 1 ? 's' : '' ?></span>
      <span><strong><?= $attentionChapters ?></strong> à vérifier</span>
      <a class="btn-primary" href="pilotage.php">Ouvrir le pilotage</a>
    </div>
  </section>
  <section class="stat-grid mt-8" aria-label="Contenus enregistrés">
    <?php
    $stats = [
        ['Niveaux', $counts['levels'], 'niveaux.php'],
        ['Matières', $counts['subjects'], 'matieres.php'],
        ['Chapitres', $counts['chapters'], 'chapitres.php'],
        ['Travaux', $counts['assignments'], 'assignments.php'],
        ['Documents', $counts['files'], 'fichiers.php'],
        ['Vidéos', $counts['chapter_videos'], 'ressources-chapitre.php'],
        ['Savoirs', $counts['chapter_skills'], 'ressources-chapitre.php'],
        ['QCM', $counts['qcms'], 'qcm.php'],
        ['Flashcards', $counts['flashcard_decks'], 'flashcards.php'],
        ['Exercices', $counts['exercises'], 'exercices.php'],
    ];
    foreach ($stats as [$label, $value, $href]): ?>
      <a class="stat-card" href="<?= h($href) ?>">
        <span class="stat-label"><?= h($label) ?></span>
        <span class="stat-value"><?= (int)$value ?></span>
        <span class="stat-hint">Gérer</span>
      </a>
    <?php endforeach; ?>
  </section>

</main>
</body>
</html>
