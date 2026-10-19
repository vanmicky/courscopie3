<?php

function chapter_health(PDO $pdo, array $chapter): array {
    $chapterId = (int)$chapter['id'];
    $checks = [];
    $alerts = [];

    $checks['Présentation'] = trim((string)($chapter['summary'] ?? '')) !== '';

    $stmt = $pdo->prepare('SELECT * FROM files WHERE chapter_id=? AND is_published=1 ORDER BY sort_order,id');
    $stmt->execute([$chapterId]);
    $files = $stmt->fetchAll();
    $validFiles = 0;
    foreach ($files as $file) {
        $absolute = dirname(__DIR__) . '/' . ltrim((string)$file['path'], '/');
        if (is_file($absolute)) $validFiles++;
        else $alerts[] = 'Fichier absent : ' . ($file['label'] ?: $file['original_name']);
    }
    $checks['Documents'] = $validFiles > 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN TRIM(COALESCE(correction,''))<>'' THEN 1 ELSE 0 END) AS corrected FROM exercises WHERE chapter_id=? AND is_published=1");
    $stmt->execute([$chapterId]);
    $exercise = $stmt->fetch();
    $checks['Exercices corrigés'] = (int)$exercise['total'] > 0 && (int)$exercise['corrected'] > 0;
    if ((int)$exercise['total'] > 0 && (int)$exercise['corrected'] === 0) $alerts[] = 'Les exercices publiés n’ont pas de correction.';

    $stmt = $pdo->prepare('SELECT title,url FROM chapter_videos WHERE chapter_id=? AND is_published=1');
    $stmt->execute([$chapterId]);
    $videos = $stmt->fetchAll();
    $validVideos = 0;
    foreach ($videos as $video) {
        if (filter_var($video['url'], FILTER_VALIDATE_URL) && preg_match('~^https?://~i', $video['url'])) $validVideos++;
        else $alerts[] = 'Lien vidéo invalide : ' . $video['title'];
    }
    $checks['Vidéos'] = $validVideos > 0;

    $stmt = $pdo->prepare('SELECT q.title, COUNT(qq.id) AS question_count FROM qcms q LEFT JOIN qcm_questions qq ON qq.qcm_id=q.id WHERE q.chapter_id=? AND q.is_published=1 GROUP BY q.id,q.title');
    $stmt->execute([$chapterId]);
    $qcms = $stmt->fetchAll();
    $validQcms = 0;
    foreach ($qcms as $qcm) {
        if ((int)$qcm['question_count'] > 0) $validQcms++;
        else $alerts[] = 'QCM vide : ' . $qcm['title'];
    }
    $checks['QCM'] = $validQcms > 0;

    $stmt = $pdo->prepare('SELECT d.title, COUNT(f.id) AS card_count FROM flashcard_decks d LEFT JOIN flashcards f ON f.deck_id=d.id WHERE d.chapter_id=? AND d.is_published=1 GROUP BY d.id,d.title');
    $stmt->execute([$chapterId]);
    $decks = $stmt->fetchAll();
    $validDecks = 0;
    foreach ($decks as $deck) {
        if ((int)$deck['card_count'] > 0) $validDecks++;
        else $alerts[] = 'Série de flashcards vide : ' . $deck['title'];
    }
    $checks['Flashcards'] = $validDecks > 0;

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM chapter_skills WHERE chapter_id=? AND is_published=1');
    $stmt->execute([$chapterId]);
    $checks['Savoirs'] = (int)$stmt->fetchColumn() > 0;

    foreach ($checks as $label => $complete) if (!$complete) $alerts[] = 'À compléter : ' . $label . '.';
    $completed = count(array_filter($checks));
    return [
        'score' => (int)round($completed * 100 / max(1, count($checks))),
        'completed' => $completed,
        'total' => count($checks),
        'checks' => $checks,
        'alerts' => array_values(array_unique($alerts)),
    ];
}

function chapter_last_change(PDO $pdo, int $chapterId, array $chapter): string {
    $dates = [(string)($chapter['updated_at'] ?: $chapter['created_at'] ?: '')];
    foreach (['files', 'exercises', 'chapter_videos', 'chapter_skills', 'qcms', 'flashcard_decks'] as $table) {
        $dateColumn = $table === 'files' ? 'created_at' : 'COALESCE(updated_at,created_at)';
        $stmt = $pdo->prepare("SELECT MAX($dateColumn) FROM `$table` WHERE chapter_id=?");
        $stmt->execute([$chapterId]);
        $dates[] = (string)$stmt->fetchColumn();
    }
    rsort($dates);
    return $dates[0] ?? '';
}

function duplicate_complete_chapter(PDO $pdo, int $sourceId, int $targetLevelId): int {
    $stmt = $pdo->prepare('SELECT * FROM chapters WHERE id=?');
    $stmt->execute([$sourceId]);
    $source = $stmt->fetch();
    if (!$source) throw new RuntimeException('Chapitre introuvable.');
    $level = $pdo->prepare('SELECT id FROM levels WHERE id=?');
    $level->execute([$targetLevelId]);
    if (!$level->fetch()) throw new RuntimeException('Niveau de destination introuvable.');

    $createdFiles = [];
    $fileMap = [];
    $pdo->beginTransaction();
    try {
        $slug = admin_unique_slug($pdo, 'chapters', $source['slug']);
        $insert = $pdo->prepare('INSERT INTO chapters(level_id,subject_id,slug,number,title,summary,content,legacy_path,sort_order,is_published) VALUES(?,?,?,?,?,?,?,?,?,0)');
        $insert->execute([$targetLevelId,$source['subject_id'],$slug,$source['number'],'Copie de '.$source['title'],$source['summary'],$source['content'],$source['legacy_path'],$source['sort_order']]);
        $newId = (int)$pdo->lastInsertId();

        $pdo->prepare('INSERT INTO lessons(chapter_id,title,kind,content,sort_order,is_published) SELECT ?,title,kind,content,sort_order,0 FROM lessons WHERE chapter_id=?')->execute([$newId,$sourceId]);
        $pdo->prepare('INSERT INTO exercises(chapter_id,title,statement,correction,difficulty,category,sort_order,is_published) SELECT ?,title,statement,correction,difficulty,category,sort_order,0 FROM exercises WHERE chapter_id=?')->execute([$newId,$sourceId]);
        $pdo->prepare('INSERT INTO chapter_videos(chapter_id,title,url,description,sort_order,is_published) SELECT ?,title,url,description,sort_order,0 FROM chapter_videos WHERE chapter_id=?')->execute([$newId,$sourceId]);
        $pdo->prepare('INSERT INTO chapter_skills(chapter_id,category,label,sort_order,is_published) SELECT ?,category,label,sort_order,0 FROM chapter_skills WHERE chapter_id=?')->execute([$newId,$sourceId]);

        $fileStmt = $pdo->prepare('SELECT * FROM files WHERE chapter_id=?');
        $fileStmt->execute([$sourceId]);
        $fileInsert = $pdo->prepare('INSERT INTO files(chapter_id,level_id,original_name,stored_name,path,mime_type,size_bytes,label,resource_type,sort_order,is_published) VALUES(?,?,?,?,?,?,?,?,?,?,0)');
        foreach ($fileStmt->fetchAll() as $file) {
            $sourceRelative = ltrim((string)$file['path'], '/');
            if (strpos($sourceRelative, 'uploads/') !== 0 || strpos($sourceRelative, '..') !== false) throw new RuntimeException('Chemin de document non autorisé.');
            $oldPath = dirname(__DIR__) . '/' . $sourceRelative;
            $extension = pathinfo($file['stored_name'], PATHINFO_EXTENSION);
            $stored = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . ($extension ? '.' . $extension : '');
            $relative = 'uploads/files/' . $stored;
            $newPath = dirname(__DIR__) . '/' . $relative;
            if (!is_file($oldPath) || !copy($oldPath, $newPath)) throw new RuntimeException('Impossible de copier le document ' . $file['original_name'] . '.');
            $createdFiles[] = $newPath;
            $fileInsert->execute([$newId,$targetLevelId,$file['original_name'],$stored,$relative,$file['mime_type'],$file['size_bytes'],$file['label'],$file['resource_type'],$file['sort_order']]);
            $fileMap[(int)$file['id']] = (int)$pdo->lastInsertId();
        }

        $assignmentStmt = $pdo->prepare('SELECT * FROM assignments WHERE chapter_id=?');
        $assignmentStmt->execute([$sourceId]);
        foreach ($assignmentStmt->fetchAll() as $assignment) {
            $pdo->prepare('INSERT INTO assignments(level_id,subject_id,chapter_id,title,kind,instructions,due_date,priority,is_published) VALUES(?,?,?,?,?,?,?,?,0)')->execute([$targetLevelId,$assignment['subject_id'],$newId,$assignment['title'],$assignment['kind'],$assignment['instructions'],$assignment['due_date'],$assignment['priority']]);
            $newAssignment = (int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO assignment_links(assignment_id,label,url) SELECT ?,label,url FROM assignment_links WHERE assignment_id=?')->execute([$newAssignment,$assignment['id']]);
            $attachments = $pdo->prepare('SELECT file_id,label FROM assignment_files WHERE assignment_id=?');
            $attachments->execute([$assignment['id']]);
            $attach = $pdo->prepare('INSERT INTO assignment_files(assignment_id,file_id,label) VALUES(?,?,?)');
            foreach ($attachments->fetchAll() as $attachment) {
                $mappedFile = $fileMap[(int)$attachment['file_id']] ?? (int)$attachment['file_id'];
                $attach->execute([$newAssignment,$mappedFile,$attachment['label']]);
            }
        }

        $qcmStmt = $pdo->prepare('SELECT * FROM qcms WHERE chapter_id=?');
        $qcmStmt->execute([$sourceId]);
        foreach ($qcmStmt->fetchAll() as $qcm) {
            $qSlug = admin_unique_slug($pdo, 'qcms', $qcm['slug']);
            $pdo->prepare('INSERT INTO qcms(chapter_id,level_id,title,slug,sort_order,is_published) VALUES(?,?,?,?,?,0)')->execute([$newId,$targetLevelId,$qcm['title'],$qSlug,$qcm['sort_order']]);
            $newQcm = (int)$pdo->lastInsertId();
            $questions = $pdo->prepare('SELECT * FROM qcm_questions WHERE qcm_id=? ORDER BY sort_order,id');
            $questions->execute([$qcm['id']]);
            foreach ($questions->fetchAll() as $question) {
                $pdo->prepare('INSERT INTO qcm_questions(qcm_id,question_text,explanation,sort_order) VALUES(?,?,?,?)')->execute([$newQcm,$question['question_text'],$question['explanation'],$question['sort_order']]);
                $newQuestion = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO qcm_choices(question_id,choice_label,choice_text,is_correct,sort_order) SELECT ?,choice_label,choice_text,is_correct,sort_order FROM qcm_choices WHERE question_id=?')->execute([$newQuestion,$question['id']]);
            }
        }

        $deckStmt = $pdo->prepare('SELECT * FROM flashcard_decks WHERE chapter_id=?');
        $deckStmt->execute([$sourceId]);
        foreach ($deckStmt->fetchAll() as $deck) {
            $deckSlug = admin_unique_slug($pdo, 'flashcard_decks', $deck['slug']);
            $pdo->prepare('INSERT INTO flashcard_decks(chapter_id,level_id,title,slug,sort_order,is_published) VALUES(?,?,?,?,?,0)')->execute([$newId,$targetLevelId,$deck['title'],$deckSlug,$deck['sort_order']]);
            $newDeck = (int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO flashcards(deck_id,front_text,back_text,sort_order) SELECT ?,front_text,back_text,sort_order FROM flashcards WHERE deck_id=?')->execute([$newDeck,$deck['id']]);
        }
        $pdo->commit();
        return $newId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        foreach ($createdFiles as $path) if (is_file($path)) unlink($path);
        throw $e;
    }
}
