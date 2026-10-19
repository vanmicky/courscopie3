<?php
/**
 * Tests fonctionnels — vérifient que tout ce qui s'enregistre s'enregistre
 * vraiment, en passant par le code utilisé par les formulaires.
 *
 *   php tools/tests.php
 *
 * Le script travaille sur un chapitre temporaire (« CHAPITRE DE TEST ») et
 * supprime tout ce qu'il crée à la fin, y compris en cas d'échec.
 *
 * Sortie : une ligne par test, puis un bilan. Code de sortie 1 si un test
 * échoue, ce qui permet de l'utiliser dans une chaîne d'intégration.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("À lancer en ligne de commande : php tools/tests.php\n");
}

require_once __DIR__ . '/../includes/repository.php';
require_once __DIR__ . '/../admin/chapter-workspace.php';

$pdo = db();
if (!$pdo) {
    fwrite(STDERR, "Connexion MySQL impossible. Vérifiez includes/config.local.php.\n");
    exit(2);
}

$total = 0;
$echecs = 0;
$chapitreTest = 0;

/** Affiche le résultat d'un test. */
function verifie(string $intitule, bool $ok, string $detail = ''): void {
    global $total, $echecs;
    $total++;
    if (!$ok) $echecs++;
    echo ($ok ? "  ✓ " : "  ✗ ") . $intitule . ($ok || $detail === '' ? '' : ' — ' . $detail) . PHP_EOL;
}

function section(string $titre): void {
    echo PHP_EOL . $titre . PHP_EOL;
}

/** Soumet un 「formulaire」 à workspace_save() avec les valeurs données. */
function soumettre(PDO $pdo, array $chapitre, string $onglet, array $post): int {
    $_POST = $post;
    return workspace_save($pdo, $chapitre, $onglet);
}

function ligne(PDO $pdo, string $table, int $id): ?array {
    $s = $pdo->prepare("SELECT * FROM `$table` WHERE id=?");
    $s->execute([$id]);
    $r = $s->fetch();
    return $r ?: null;
}

/* -------------------------------------------------------------------------
   Chapitre temporaire
   ------------------------------------------------------------------------- */
try {
    $niveau = $pdo->query('SELECT id FROM levels ORDER BY sort_order, id LIMIT 1')->fetchColumn();
    $matiere = $pdo->query('SELECT id FROM subjects ORDER BY sort_order, id LIMIT 1')->fetchColumn();
    if (!$niveau || !$matiere) {
        fwrite(STDERR, "Il faut au moins un niveau et une matière pour lancer les tests.\n");
        exit(2);
    }
    $ins = $pdo->prepare('INSERT INTO chapters(level_id,subject_id,slug,number,title,summary,sort_order,is_published) VALUES(?,?,?,?,?,?,?,1)');
    $ins->execute([$niveau, $matiere, 'chapitre-de-test-' . bin2hex(random_bytes(4)), 99, 'CHAPITRE DE TEST', '', 999]);
    $chapitreTest = (int)$pdo->lastInsertId();
} catch (Throwable $e) {
    fwrite(STDERR, "Impossible de créer le chapitre de test : " . $e->getMessage() . "\n");
    exit(2);
}

// L'application passe la ligne complète du chapitre (avec level_id) :
// le test doit faire de même, sinon il teste un cas qui n'existe pas.
$chapitre = ligne($pdo, 'chapters', $chapitreTest) ?: ['id' => $chapitreTest];
$crees = ['files' => [], 'exercises' => [], 'chapter_videos' => [], 'qcms' => [], 'flashcard_decks' => [], 'chapter_skills' => [], 'flashcards' => [], 'qcm_questions' => []];

try {
    section('Base de données');
    verifie('connexion établie', db() instanceof PDO);
    verifie('table des résultats (qcm_attempts) présente', (bool)$pdo->query("SHOW TABLES LIKE 'qcm_attempts'")->fetch());
    verifie('table des méthodes présente', methods_available());
    verifie('texte de remplissage masqué', chapter_summary('Contenu repris du site statique.') === '');

    /* ---------------------------------------------------------------- */
    section('Chapitre — informations');

    soumettre($pdo, $chapitre, 'informations', [
        'action' => 'save', 'title' => 'CHAPITRE DE TEST', 'summary' => 'Résumé de test', 'number' => '3', 'is_published' => '1',
    ]);
    $c = ligne($pdo, 'chapters', $chapitreTest);
    verifie('titre enregistré', ($c['title'] ?? '') === 'CHAPITRE DE TEST');
    verifie('résumé enregistré', ($c['summary'] ?? '') === 'Résumé de test');
    verifie('numéro enregistré', (int)($c['number'] ?? 0) === 3);

    /* ---------------------------------------------------------------- */
    section('Chaque type de contenu : création, modification, suppression');

    $jeux = [
        'savoirs' => [
            'label' => 'Savoirs',
            'table' => 'chapter_skills',
            'creation' => ['action' => 'save', 'label' => 'Savoir créé', 'category' => 'Savoir', 'sort_order' => '10', 'is_published' => '1'],
            'modification' => ['action' => 'save', 'label' => 'Savoir modifié', 'category' => 'Savoir-faire', 'sort_order' => '20', 'is_published' => '0'],
            'champ' => 'label',
        ],
        'exercices' => [
            'label' => 'Exercices',
            'table' => 'exercises',
            'creation' => ['action' => 'save', 'title' => 'Exercice créé', 'category' => 'ds', 'difficulty' => 'normal', 'statement' => 'Énoncé', 'correction' => 'Correction', 'sort_order' => '10', 'is_published' => '1'],
            'modification' => ['action' => 'save', 'title' => 'Exercice modifié', 'category' => 'brevet', 'difficulty' => 'difficile', 'statement' => 'Énoncé 2', 'correction' => 'Correction 2', 'sort_order' => '20', 'is_published' => '0'],
            'champ' => 'title',
        ],
        'videos' => [
            'label' => 'Vidéos',
            'table' => 'chapter_videos',
            'creation' => ['action' => 'save', 'title' => 'Vidéo créée', 'url' => 'https://exemple.fr/v1', 'description' => 'Description', 'sort_order' => '10', 'is_published' => '1'],
            'modification' => ['action' => 'save', 'title' => 'Vidéo modifiée', 'url' => 'https://exemple.fr/v2', 'description' => 'Description 2', 'sort_order' => '20', 'is_published' => '0'],
            'champ' => 'title',
        ],
        'qcm' => [
            'label' => 'QCM',
            'table' => 'qcms',
            'creation' => ['action' => 'save', 'title' => 'QCM créé', 'sort_order' => '10', 'is_published' => '1'],
            'modification' => ['action' => 'save', 'title' => 'QCM modifié', 'sort_order' => '20', 'is_published' => '0'],
            'champ' => 'title',
        ],
        'flashcards' => [
            'label' => 'Flashcards',
            'table' => 'flashcard_decks',
            'creation' => ['action' => 'save', 'title' => 'Série créée', 'sort_order' => '10', 'is_published' => '1'],
            'modification' => ['action' => 'save', 'title' => 'Série modifiée', 'sort_order' => '20', 'is_published' => '0'],
            'champ' => 'title',
        ],
    ];

    foreach ($jeux as $onglet => $jeu) {
        $table = $jeu['table'];

        $id = soumettre($pdo, $chapitre, $onglet, $jeu['creation']);
        $crees[$table][] = $id;
        $r = ligne($pdo, $table, $id);
        verifie($jeu['label'] . ' — création', $r !== null && $r[$jeu['champ']] === $jeu['creation'][$jeu['champ']], 'valeur en base inattendue');

        $post = $jeu['modification'];
        $post['id'] = $id;
        soumettre($pdo, $chapitre, $onglet, $post);
        $r = ligne($pdo, $table, $id);
        verifie($jeu['label'] . ' — modification', $r !== null && $r[$jeu['champ']] === $jeu['modification'][$jeu['champ']]);

        // Une modification ne doit pas créer de doublon.
        $s = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE chapter_id=?");
        $s->execute([$chapitreTest]);
        verifie($jeu['label'] . ' — pas de doublon', (int)$s->fetchColumn() === 1, 'plusieurs lignes pour ce chapitre');

        soumettre($pdo, $chapitre, $onglet, ['action' => 'delete', 'id' => $id]);
        verifie($jeu['label'] . ' — suppression', ligne($pdo, $table, $id) === null);
    }

    /* ---------------------------------------------------------------- */
    section('Flashcards — cartes d’une série');

    $deckId = soumettre($pdo, $chapitre, 'flashcards', ['action' => 'save', 'title' => 'Série de test', 'sort_order' => '10', 'is_published' => '1']);
    $crees['flashcard_decks'][] = $deckId;

    // workspace_save() renvoie l'id de la série : on relit la carte créée en base.
    soumettre($pdo, $chapitre, 'flashcards', [
        'action' => 'save_child', 'id' => $deckId, 'child_id' => '0',
        'front_text' => 'Question créée', 'back_text' => 'Réponse créée', 'sort_order' => '10',
    ]);
    $carteId = (int)$pdo->query('SELECT id FROM flashcards WHERE deck_id=' . (int)$deckId . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    $crees['flashcards'][] = $carteId;
    verifie('carte — identifiant obtenu', $carteId > 0);
    $carte = ligne($pdo, 'flashcards', $carteId);
    verifie('carte — création', $carte !== null && $carte['front_text'] === 'Question créée' && $carte['back_text'] === 'Réponse créée');
    verifie('carte — rattachée à la série', $carte !== null && (int)$carte['deck_id'] === $deckId);

    soumettre($pdo, $chapitre, 'flashcards', [
        'action' => 'save_child', 'id' => $deckId, 'child_id' => $carteId,
        'front_text' => 'Question modifiée', 'back_text' => 'Réponse modifiée', 'sort_order' => '20',
    ]);
    $carte = ligne($pdo, 'flashcards', $carteId);
    verifie('carte — modification', $carte !== null && $carte['front_text'] === 'Question modifiée' && $carte['back_text'] === 'Réponse modifiée');

    $cartes = get_flashcards($deckId);
    verifie('carte — visible depuis la série', count($cartes) === 1 && $cartes[0]['front_text'] === 'Question modifiée');

    soumettre($pdo, $chapitre, 'flashcards', ['action' => 'delete_child', 'id' => $deckId, 'child_id' => $carteId]);
    verifie('carte — suppression', ligne($pdo, 'flashcards', $carteId) === null);

    /* ---------------------------------------------------------------- */
    section('QCM — questions et réponses');

    $qcmId = soumettre($pdo, $chapitre, 'qcm', ['action' => 'save', 'title' => 'QCM de test', 'sort_order' => '10', 'is_published' => '1']);
    $crees['qcms'][] = $qcmId;

    soumettre($pdo, $chapitre, 'qcm', [
        'action' => 'save_child', 'id' => $qcmId, 'child_id' => '0',
        'question_text' => 'Question créée', 'explanation' => 'Explication',
        'choice_A' => 'Réponse A', 'choice_B' => 'Réponse B', 'choice_C' => 'Réponse C', 'choice_D' => 'Réponse D',
        'correct' => 'B', 'sort_order' => '10',
    ]);
    $questionId = (int)$pdo->query('SELECT id FROM qcm_questions WHERE qcm_id=' . (int)$qcmId . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    $crees['qcm_questions'][] = $questionId;
    verifie('question — identifiant obtenu', $questionId > 0);
    verifie('question — création', ligne($pdo, 'qcm_questions', $questionId) !== null);
    $choix = $pdo->prepare('SELECT choice_label, is_correct FROM qcm_choices WHERE question_id=? ORDER BY choice_label');
    $choix->execute([$questionId]);
    $choix = $choix->fetchAll();
    verifie('question — 4 réponses enregistrées', count($choix) === 4);
    verifie('question — bonne réponse conservée', count(array_filter($choix, static fn($c) => (int)$c['is_correct'] === 1 && $c['choice_label'] === 'B')) === 1);

    soumettre($pdo, $chapitre, 'qcm', ['action' => 'delete_child', 'id' => $qcmId, 'child_id' => $questionId]);
    verifie('question — suppression', ligne($pdo, 'qcm_questions', $questionId) === null);

    /* ---------------------------------------------------------------- */
    section('Publication — visibilité sur le site public');

    $deckPublie = soumettre($pdo, $chapitre, 'flashcards', ['action' => 'save', 'title' => 'Série publiée', 'sort_order' => '30', 'is_published' => '1']);
    $deckBrouillon = soumettre($pdo, $chapitre, 'flashcards', ['action' => 'save', 'title' => 'Série en brouillon', 'sort_order' => '40', 'is_published' => '0']);
    $crees['flashcard_decks'][] = $deckPublie;
    $crees['flashcard_decks'][] = $deckBrouillon;

    $visibles = array_column(get_flashcard_decks(['chapter_id' => $chapitreTest]), 'title');
    $avecBrouillons = array_column(get_flashcard_decks(['chapter_id' => $chapitreTest, 'includeDrafts' => true]), 'title');
    verifie('série publiée visible sur le site', in_array('Série publiée', $visibles, true));
    verifie('série en brouillon masquée sur le site', !in_array('Série en brouillon', $visibles, true), 'une série non publiée apparaît publiquement');
    verifie('brouillon visible dans l’administration', in_array('Série en brouillon', $avecBrouillons, true));

    // Même contrôle pour les exercices, qui utilisent le même mécanisme.
    $exPublie = soumettre($pdo, $chapitre, 'exercices', ['action' => 'save', 'title' => 'Exercice publié', 'category' => 'ds', 'difficulty' => 'normal', 'statement' => 'Énoncé', 'sort_order' => '30', 'is_published' => '1']);
    $exBrouillon = soumettre($pdo, $chapitre, 'exercices', ['action' => 'save', 'title' => 'Exercice brouillon', 'category' => 'ds', 'difficulty' => 'normal', 'statement' => 'Énoncé', 'sort_order' => '40', 'is_published' => '0']);
    $crees['exercises'][] = $exPublie;
    $crees['exercises'][] = $exBrouillon;
    $titres = array_column(get_exercises(['chapter_id' => $chapitreTest]), 'title');
    verifie('exercice publié visible', in_array('Exercice publié', $titres, true));
    verifie('exercice en brouillon masqué', !in_array('Exercice brouillon', $titres, true));

    /* ---------------------------------------------------------------- */
    section('QCM : nombre de réponses variable');

    $qcmVar = soumettre($pdo, $chapitre, 'qcm', ['action' => 'save', 'title' => 'QCM variable', 'sort_order' => '50', 'is_published' => '1']);
    $crees['qcms'][] = $qcmVar;

    // Deux réponses seulement doivent être acceptées.
    soumettre($pdo, $chapitre, 'qcm', [
        'action' => 'save_child', 'id' => $qcmVar, 'child_id' => '0',
        'question_text' => 'Deux réponses', 'choice_A' => 'Oui', 'choice_B' => 'Non',
        'choice_C' => '', 'choice_D' => '', 'choice_E' => '', 'choice_F' => '',
        'correct' => 'B', 'sort_order' => '10',
    ]);
    $qid = (int)$pdo->query('SELECT id FROM qcm_questions WHERE qcm_id=' . (int)$qcmVar . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    $crees['qcm_questions'][] = $qid;
    $nb = $pdo->prepare('SELECT COUNT(*) FROM qcm_choices WHERE question_id=?');
    $nb->execute([$qid]);
    verifie('QCM — deux réponses suffisent', (int)$nb->fetchColumn() === 2);

    // Six réponses doivent aussi être acceptées.
    soumettre($pdo, $chapitre, 'qcm', [
        'action' => 'save_child', 'id' => $qcmVar, 'child_id' => '0',
        'question_text' => 'Six réponses', 'choice_A' => 'a', 'choice_B' => 'b', 'choice_C' => 'c',
        'choice_D' => 'd', 'choice_E' => 'e', 'choice_F' => 'f', 'correct' => 'F', 'sort_order' => '20',
    ]);
    $qid2 = (int)$pdo->query('SELECT id FROM qcm_questions WHERE qcm_id=' . (int)$qcmVar . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    $crees['qcm_questions'][] = $qid2;
    $nb->execute([$qid2]);
    verifie('QCM — six réponses acceptées', (int)$nb->fetchColumn() === 6);

    // Une seule réponse doit être refusée.
    $refus = false;
    try {
        soumettre($pdo, $chapitre, 'qcm', [
            'action' => 'save_child', 'id' => $qcmVar, 'child_id' => '0',
            'question_text' => 'Une réponse', 'choice_A' => 'seule', 'choice_B' => '', 'correct' => 'A',
        ]);
    } catch (Throwable $e) { $refus = true; }
    verifie('QCM — une seule réponse refusée', $refus);

    // Bonne réponse hors des réponses saisies.
    $refus = false;
    try {
        soumettre($pdo, $chapitre, 'qcm', [
            'action' => 'save_child', 'id' => $qcmVar, 'child_id' => '0',
            'question_text' => 'Bonne réponse invalide', 'choice_A' => 'a', 'choice_B' => 'b', 'correct' => 'D',
        ]);
    } catch (Throwable $e) { $refus = true; }
    verifie('QCM — bonne réponse hors liste refusée', $refus);

    verifie('QCM — colonnes d’image présentes', count(array_filter($pdo->query("SHOW COLUMNS FROM qcm_questions")->fetchAll(), static fn($c) => str_starts_with($c['Field'], 'image'))) === 2);
    $colsCartes = array_filter($pdo->query('SHOW COLUMNS FROM flashcards')->fetchAll(), static fn($c) => str_starts_with($c['Field'], 'image'));
    verifie('Cartes — colonnes recto et verso', count($colsCartes) === 4, count($colsCartes) . ' colonne(s)');

    /* ---------------------------------------------------------------- */
    section('Sécurité des formulaires');

    $_POST = ['action' => 'save', 'title' => '', 'sort_order' => '10'];
    $refuse = false;
    try { workspace_save($pdo, $chapitre, 'savoirs'); } catch (Throwable $e) { $refuse = true; }
    verifie('titre vide refusé', $refuse);

    $_POST = ['action' => 'save', 'label' => 'Savoir', 'category' => 'Catégorie inventée', 'sort_order' => '10'];
    $refuse = false;
    try { workspace_save($pdo, $chapitre, 'savoirs'); } catch (Throwable $e) { $refuse = true; }
    verifie('valeur de liste inventée refusée', $refuse);

    $autre = $pdo->query('SELECT id FROM chapters ORDER BY id LIMIT 1')->fetchColumn();
    $_POST = ['action' => 'delete', 'id' => $deckId];
    $refuse = false;
    try { workspace_save($pdo, ['id' => (int)$autre], 'flashcards'); } catch (Throwable $e) { $refuse = true; }
    verifie('suppression d’une série d’un autre chapitre refusée', $refuse && ligne($pdo, 'flashcard_decks', $deckId) !== null);

    /* ---------------------------------------------------------------- */
    section('Statistiques de fréquentation');

    verifie('table visits présente', (bool)$pdo->query("SHOW TABLES LIKE 'visits'")->fetch());
    verifie('fichier de localisation présent', is_file(__DIR__ . '/../data/geoip-country.bin'));

    require_once __DIR__ . '/../includes/geoip.php';
    require_once __DIR__ . '/../includes/analytics.php';
    verifie('localisation : boucle locale', geoip_code('127.0.0.1') === 'LOCAL');
    verifie('localisation : IPv6 locale', geoip_code('::1') === 'LOCAL');
    verifie('localisation : adresse publique connue', strlen(geoip_code('8.8.8.8')) === 2);
    verifie('localisation : nom lisible', geoip_nom('FR') === 'France');

    // Enregistrement d'une visite de test, puis nettoyage.
    $avant = (int)$pdo->query('SELECT COUNT(*) FROM visits')->fetchColumn();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['REMOTE_ADDR'] = '90.110.1.1';
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh)';
    $_SERVER['REQUEST_URI'] = '/test-visite';
    $_SERVER['HTTP_HOST'] = 'localhost';
    visite_enregistrer();
    $apres = (int)$pdo->query('SELECT COUNT(*) FROM visits')->fetchColumn();
    verifie('visite enregistrée', $apres === $avant + 1, 'aucune ligne ajoutée');
    $derniere = $pdo->query('SELECT country, is_bot FROM visits ORDER BY id DESC LIMIT 1')->fetch();
    verifie('pays de la visite résolu', $derniere !== false && $derniere['country'] !== 'ZZ');
    verifie('visiteur humain détecté', $derniere !== false && (int)$derniere['is_bot'] === 0);
    $pdo->prepare('DELETE FROM visits WHERE path = ?')->execute(['/test-visite']);

    verifie('robots détectés', visites_est_robot('Googlebot/2.1 (+http://www.google.com/bot.html)'));
    verifie('navigateur non classé robot', !visites_est_robot('Mozilla/5.0 (Macintosh)'));

    /* ---------------------------------------------------------------- */
    section('Connexion administrateur');

    $admins = $pdo->query('SELECT email, password_hash FROM admins WHERE is_active=1')->fetchAll();
    verifie('au moins un administrateur actif', count($admins) > 0);
    verifie('empreintes de mot de passe valides', count(array_filter($admins, static fn($a) => str_starts_with((string)$a['password_hash'], '$2y$'))) === count($admins));
    verifie('adresse des comptes en minuscules', count(array_filter($admins, static fn($a) => $a['email'] === strtolower($a['email']))) === count($admins));
} catch (Throwable $e) {
    echo PHP_EOL . '  ✗ EXCEPTION : ' . $e->getMessage() . PHP_EOL;
    $echecs++;
    $total++;
} finally {
    /* ---------------------------------------------------------------------
       Nettoyage : on supprime tout ce que les tests ont créé.
       --------------------------------------------------------------------- */
    try {
        $pdo->prepare('DELETE FROM flashcards WHERE deck_id IN (SELECT id FROM flashcard_decks WHERE chapter_id=?)')->execute([$chapitreTest]);
        $pdo->prepare('DELETE FROM qcm_choices WHERE question_id IN (SELECT id FROM qcm_questions WHERE qcm_id IN (SELECT id FROM qcms WHERE chapter_id=?))')->execute([$chapitreTest]);
        $pdo->prepare('DELETE FROM qcm_questions WHERE qcm_id IN (SELECT id FROM qcms WHERE chapter_id=?)')->execute([$chapitreTest]);
        foreach (['flashcard_decks', 'qcms', 'chapter_videos', 'exercises', 'chapter_skills'] as $table) {
            $pdo->prepare("DELETE FROM `$table` WHERE chapter_id=?")->execute([$chapitreTest]);
        }
        $pdo->prepare('UPDATE files SET chapter_id=NULL WHERE chapter_id=?')->execute([$chapitreTest]);
        $pdo->prepare('DELETE FROM chapters WHERE id=?')->execute([$chapitreTest]);
        $reste = $pdo->prepare('SELECT COUNT(*) FROM chapters WHERE id=?');
        $reste->execute([$chapitreTest]);
        echo PHP_EOL . 'Nettoyage : ' . ((int)$reste->fetchColumn() === 0 ? 'chapitre de test supprimé' : 'ATTENTION, chapitre restant') . PHP_EOL;
    } catch (Throwable $e) {
        echo PHP_EOL . 'Nettoyage incomplet : ' . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . str_repeat('─', 52) . PHP_EOL;
echo $echecs === 0
    ? "Tous les tests passent ({$total} vérifications)." . PHP_EOL
    : "{$echecs} échec(s) sur {$total} vérifications." . PHP_EOL;

exit($echecs === 0 ? 0 : 1);
