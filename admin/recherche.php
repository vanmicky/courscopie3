<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = db();
if (!$pdo) {
    page_head('Admin indisponible', '', ['math' => false]);
    echo '<main class="page-container py-8"><p>Base non configuree.</p></main></body></html>';
    exit;
}
admin_ensure_v64_schema();

$query = trim((string)($_GET['q'] ?? ''));
$results = [];
function admin_search_excerpt(string $text): string {
    $text = trim(strip_tags($text));
    if (function_exists('mb_strimwidth')) return mb_strimwidth($text, 0, 180, '…', 'UTF-8');
    return strlen($text) > 180 ? substr($text, 0, 177) . '...' : $text;
}
$sources = [
    ['type'=>'Niveau','sql'=>'SELECT id,name AS title,summary AS excerpt FROM levels WHERE name LIKE ? OR summary LIKE ? LIMIT 20','url'=>'niveaux.php?edit='],
    ['type'=>'Matière','sql'=>'SELECT id,name AS title,summary AS excerpt FROM subjects WHERE name LIKE ? OR summary LIKE ? LIMIT 20','url'=>'matieres.php?edit='],
    ['type'=>'Chapitre','sql'=>'SELECT id,title,COALESCE(summary,\'\') AS excerpt FROM chapters WHERE title LIKE ? OR summary LIKE ? LIMIT 20','url'=>'chapitres.php?edit='],
    ['type'=>'Exercice','sql'=>'SELECT id,title,CONCAT(COALESCE(statement,\'\'),\' \',COALESCE(correction,\'\')) AS excerpt FROM exercises WHERE title LIKE ? OR statement LIKE ? OR correction LIKE ? LIMIT 20','url'=>'exercices.php?edit='],
    ['type'=>'QCM','sql'=>'SELECT id,title,slug AS excerpt FROM qcms WHERE title LIKE ? OR slug LIKE ? LIMIT 20','url'=>'qcm.php?edit='],
    ['type'=>'Question QCM','sql'=>'SELECT id,question_text AS title,explanation AS excerpt,qcm_id FROM qcm_questions WHERE question_text LIKE ? OR explanation LIKE ? LIMIT 20','url'=>'qcm.php?question='],
    ['type'=>'Flashcards','sql'=>'SELECT id,title,slug AS excerpt FROM flashcard_decks WHERE title LIKE ? OR slug LIKE ? LIMIT 20','url'=>'flashcards.php?edit='],
    ['type'=>'Carte de révision','sql'=>'SELECT id,front_text AS title,back_text AS excerpt,deck_id FROM flashcards WHERE front_text LIKE ? OR back_text LIKE ? LIMIT 20','url'=>'flashcards.php?card='],
    ['type'=>'Travail à faire','sql'=>'SELECT id,title,instructions AS excerpt FROM assignments WHERE title LIKE ? OR instructions LIKE ? LIMIT 20','url'=>'assignments.php?edit='],
    ['type'=>'Fichier','sql'=>'SELECT id,COALESCE(label,original_name) AS title,original_name AS excerpt FROM files WHERE label LIKE ? OR original_name LIKE ? LIMIT 20','url'=>'fichiers.php?q='],
    ['type'=>'Vidéo','sql'=>'SELECT id,title,description AS excerpt,chapter_id FROM chapter_videos WHERE title LIKE ? OR description LIKE ? LIMIT 20','url'=>'ressources-chapitre.php?video='],
    ['type'=>'Savoir','sql'=>'SELECT id,label AS title,category AS excerpt,chapter_id FROM chapter_skills WHERE label LIKE ? OR category LIKE ? LIMIT 20','url'=>'ressources-chapitre.php?skill='],
];

if ($query !== '') {
    $like = '%' . $query . '%';
    foreach ($sources as $source) {
        try {
            $parameterCount = substr_count($source['sql'], '?');
            $stmt = $pdo->prepare($source['sql']);
            $stmt->execute(array_fill(0, $parameterCount, $like));
            foreach ($stmt->fetchAll() as $row) {
                $url = $source['url'] . ($source['type'] === 'Fichier' ? urlencode($query) : (int)$row['id']);
                if ($source['type'] === 'Vidéo' || $source['type'] === 'Savoir') $url .= '&chapter_id=' . (int)$row['chapter_id'];
                if ($source['type'] === 'Question QCM') $url .= '&edit=' . (int)$row['qcm_id'];
                if ($source['type'] === 'Carte de révision') $url .= '&edit=' . (int)$row['deck_id'];
                $results[] = ['type'=>$source['type'],'title'=>$row['title'],'excerpt'=>$row['excerpt'] ?? '','url'=>$url];
            }
        } catch (Throwable $e) {}
    }
}

page_head('Admin - Recherche globale', '', ['math' => false]);
?>
<?php admin_nav(); ?>
<main class="page-container py-8">
  <section class="admin-page-heading">
    <div><span class="badge">Admin · V73</span><h1 class="page-title mt-3">Recherche globale</h1></div>
    <a class="btn-secondary" href="dashboard.php">Tableau de bord</a>
  </section>

  <form method="get" class="admin-global-search mt-6" role="search">
    <label for="admin-search">Rechercher dans tous les contenus</label>
    <div><input id="admin-search" type="search" name="q" value="<?= h($query) ?>" placeholder="Chapitre, exercice, devoir, fichier…" autofocus><button class="btn-primary" type="submit">Rechercher</button></div>
  </form>

  <?php if ($query === ''): ?>
    <p class="admin-empty-state mt-6">Saisissez un mot pour rechercher dans les niveaux, chapitres, activités, vidéos et documents.</p>
  <?php elseif (!$results): ?>
    <p class="admin-empty-state mt-6">Aucun résultat pour « <?= h($query) ?> ».</p>
  <?php else: ?>
    <div class="admin-search-summary mt-6"><strong><?= count($results) ?> résultat(s)</strong><span>pour « <?= h($query) ?> »</span></div>
    <section class="admin-search-results mt-4">
      <?php foreach ($results as $result): ?>
        <a class="admin-search-result" href="<?= h($result['url']) ?>"><span><?= h($result['type']) ?></span><div><h2><?= h($result['title']) ?></h2><p><?= h(admin_search_excerpt((string)$result['excerpt'])) ?></p></div><strong aria-hidden="true">→</strong></a>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</main>
</body>
</html>
