<?php
require_once __DIR__ . '/helpers.php';

function seed_levels(): array { return require __DIR__ . '/seed-levels.php'; }
function seed_chapters(): array { return require __DIR__ . '/seed-chapters.php'; }
function seed_assignments(): array { return require __DIR__ . '/seed-assignments.php'; }

function get_levels(): array {
    if ($pdo = db()) {
        try { return $pdo->query('SELECT * FROM levels WHERE is_published = 1 ORDER BY sort_order, id')->fetchAll(); } catch (Throwable $e) {}
    }
    return seed_levels();
}
function get_level(string $slug, bool $includeDrafts = false): ?array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT * FROM levels WHERE slug=?' . ($includeDrafts ? '' : ' AND is_published=1') . ' LIMIT 1';
            $s=$pdo->prepare($sql); $s->execute([$slug]); $row=$s->fetch(); return $row ?: null;
        } catch (Throwable $e) {}
    }
    foreach (seed_levels() as $level) if ($level['slug'] === $slug) return $level;
    return null;
}
function get_subjects(bool $includeDrafts = false): array {
    if ($pdo = db()) {
        try {
            $published = $includeDrafts ? '' : ' WHERE is_published=1';
            return $pdo->query('SELECT * FROM subjects' . $published . ' ORDER BY sort_order, name, id')->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}
function get_chapters(?string $levelSlug = null, bool $includeDrafts = false): array {
    if ($pdo = db()) {
        try {
            $published = $includeDrafts ? '' : ' AND c.is_published=1';
            if ($levelSlug) {
                $s=$pdo->prepare('SELECT c.*, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent FROM chapters c JOIN levels l ON l.id=c.level_id WHERE l.slug=?' . $published . ' ORDER BY c.sort_order, c.number, c.id');
                $s->execute([$levelSlug]); return $s->fetchAll();
            }
            return $pdo->query('SELECT c.*, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent FROM chapters c JOIN levels l ON l.id=c.level_id WHERE 1=1' . $published . ' ORDER BY l.sort_order, c.sort_order, c.number')->fetchAll();
        } catch (Throwable $e) {}
    }
    return array_values(array_filter(seed_chapters(), fn($c) => !$levelSlug || $c['level_slug'] === $levelSlug));
}
function get_chapter(string $slug, bool $includeDrafts = false): ?array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT c.*, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent FROM chapters c JOIN levels l ON l.id=c.level_id WHERE c.slug=?';
            if (!$includeDrafts) $sql .= ' AND c.is_published=1';
            $s=$pdo->prepare($sql . ' LIMIT 1'); $s->execute([$slug]); $row=$s->fetch(); return $row ?: null;
        } catch (Throwable $e) {}
    }
    foreach (seed_chapters() as $chapter) if ($chapter['slug'] === $slug) return $chapter;
    return null;
}
function get_lessons(int $chapterId, bool $includeDrafts = false): array {
    if ($pdo = db()) {
        try {
            $published = $includeDrafts ? '' : ' AND is_published=1';
            $s=$pdo->prepare('SELECT * FROM lessons WHERE chapter_id=?' . $published . ' ORDER BY sort_order,id');
            $s->execute([$chapterId]);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}
function get_chapter_files(int $chapterId, bool $includeHidden = false): array {
    if ($pdo = db()) {
        try {
            $published = $includeHidden ? '' : ' AND is_published=1';
            $s = $pdo->prepare('SELECT * FROM files WHERE chapter_id=?' . $published . ' ORDER BY sort_order,id');
            $s->execute([$chapterId]);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}
function document_type_labels(): array {
    return [
        'activite' => 'Activité',
        'correction-activite' => 'Correction d’activité',
        'correction-exercices' => 'Correction des exercices',
        'feuille-exercices' => 'Feuille d’exercices',
        'carte-mentale' => 'Carte mentale',
        'document' => 'Document',
    ];
}
function get_chapter_videos(int $chapterId, bool $includeDrafts = false): array {
    if ($pdo = db()) {
        try {
            $published = $includeDrafts ? '' : ' AND is_published=1';
            $stmt = $pdo->prepare('SELECT * FROM chapter_videos WHERE chapter_id=?' . $published . ' ORDER BY sort_order,id');
            $stmt->execute([$chapterId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}
function get_chapter_skills(int $chapterId, bool $includeDrafts = false): array {
    if ($pdo = db()) {
        try {
            $published = $includeDrafts ? '' : ' AND is_published=1';
            $stmt = $pdo->prepare('SELECT * FROM chapter_skills WHERE chapter_id=?' . $published . ' ORDER BY CASE category WHEN \'Savoir\' THEN 1 ELSE 2 END,sort_order,id');
            $stmt->execute([$chapterId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}
function video_embed_url(string $url): ?string {
    $url = trim($url);
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/(?:embed|shorts)/)([A-Za-z0-9_-]{6,})~i', $url, $match)) {
        return 'https://www.youtube-nocookie.com/embed/' . $match[1];
    }
    if (preg_match('~vimeo\.com/(?:video/)?([0-9]+)~i', $url, $match)) {
        return 'https://player.vimeo.com/video/' . $match[1];
    }
    return null;
}
function get_assignment_files(int $assignmentId, bool $includeHidden = false): array {
    if ($pdo = db()) {
        try {
            $published = $includeHidden ? '' : ' AND f.is_published=1';
            $s = $pdo->prepare('SELECT f.*, af.label AS assignment_label FROM assignment_files af JOIN files f ON f.id=af.file_id WHERE af.assignment_id=?' . $published . ' ORDER BY af.id');
            $s->execute([$assignmentId]);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}
function get_assignment_links(int $assignmentId): array {
    if ($pdo = db()) {
        try {
            $s = $pdo->prepare('SELECT * FROM assignment_links WHERE assignment_id=? ORDER BY id');
            $s->execute([$assignmentId]);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}
function get_files(array $filters = []): array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT f.*, l.short AS level_short, l.name AS level_name, c.title AS chapter_title FROM files f LEFT JOIN levels l ON l.id=f.level_id LEFT JOIN chapters c ON c.id=f.chapter_id WHERE 1=1';
            $params = [];
            if (!empty($filters['level_id'])) { $sql .= ' AND f.level_id=?'; $params[] = (int)$filters['level_id']; }
            if (!empty($filters['chapter_id'])) { $sql .= ' AND f.chapter_id=?'; $params[] = (int)$filters['chapter_id']; }
            if (!empty($filters['q'])) {
                $sql .= ' AND (f.original_name LIKE ? OR f.label LIKE ? OR c.title LIKE ?)';
                $q = '%' . $filters['q'] . '%';
                array_push($params, $q, $q, $q);
            }
            $sql .= ' ORDER BY f.created_at DESC, f.id DESC';
            $s = $pdo->prepare($sql);
            $s->execute($params);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}
function allowed_upload_extensions(): array {
    return ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip'];
}
function allowed_image_extensions(): array {
    return ['png', 'jpg', 'jpeg', 'webp', 'gif'];
}
function upload_error_message(int $code): string {
    if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE) return 'Le fichier est trop lourd.';
    if ($code === UPLOAD_ERR_PARTIAL) return 'Le fichier a ete envoye partiellement.';
    if ($code === UPLOAD_ERR_NO_FILE) return 'Aucun fichier selectionne.';
    return 'Upload impossible.';
}
function store_uploaded_file(array $upload, array $meta = []): array {
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(upload_error_message((int)($upload['error'] ?? UPLOAD_ERR_NO_FILE)));
    }
    $maxBytes = 20 * 1024 * 1024;
    if ((int)$upload['size'] > $maxBytes) throw new RuntimeException('Le fichier depasse 20 Mo.');
    $original = basename((string)$upload['name']);
    $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($extension, allowed_upload_extensions(), true)) {
        throw new RuntimeException('Type de fichier non autorise.');
    }
    $stored = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
    $relativeDir = 'uploads/files';
    $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
    if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0755, true);
    $destination = $absoluteDir . '/' . $stored;
    if (!move_uploaded_file($upload['tmp_name'], $destination)) {
        throw new RuntimeException('Impossible de deplacer le fichier envoye.');
    }
    $mime = function_exists('mime_content_type') ? mime_content_type($destination) : ($upload['type'] ?? null);
    $pdo = db();
    if (!$pdo) throw new RuntimeException('Base de donnees indisponible.');
    $stmt = $pdo->prepare('INSERT INTO files(level_id, chapter_id, original_name, stored_name, path, mime_type, size_bytes, label, resource_type, sort_order, is_published) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $meta['level_id'] ?? null,
        $meta['chapter_id'] ?? null,
        $original,
        $stored,
        $relativeDir . '/' . $stored,
        $mime,
        (int)$upload['size'],
        $meta['label'] ?? $original,
        $meta['resource_type'] ?? 'document',
        (int)($meta['sort_order'] ?? 0),
        isset($meta['is_published']) ? (int)$meta['is_published'] : 1,
    ]);
    return ['id' => (int)$pdo->lastInsertId(), 'path' => $relativeDir . '/' . $stored, 'name' => $original];
}
function store_site_image(array $upload, string $folder): ?string {
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(upload_error_message((int)($upload['error'] ?? UPLOAD_ERR_NO_FILE)));
    }
    $maxBytes = 5 * 1024 * 1024;
    if ((int)$upload['size'] > $maxBytes) throw new RuntimeException('L image dépasse 5 Mo.');
    $original = basename((string)$upload['name']);
    $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($extension, allowed_image_extensions(), true)) {
        throw new RuntimeException('Image non autorisee.');
    }
    $safeFolder = trim(preg_replace('/[^a-z0-9_-]+/', '-', strtolower($folder)) ?? 'images', '-');
    $relativeDir = 'uploads/' . ($safeFolder ?: 'images');
    $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
    if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0755, true);
    $stored = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
    $destination = $absoluteDir . '/' . $stored;
    if (!move_uploaded_file($upload['tmp_name'], $destination)) {
        throw new RuntimeException('Impossible de déplacer l image envoyée.');
    }
    return $relativeDir . '/' . $stored;
}
function get_assignments(array $filters = []): array {
    if ($pdo = db()) {
        try {
            $sql='SELECT a.*, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent, COALESCE(s.name,l.subject,\'\') AS subject, c.slug AS chapter_slug, c.title AS chapter_title FROM assignments a JOIN levels l ON l.id=a.level_id LEFT JOIN subjects s ON s.id=a.subject_id LEFT JOIN chapters c ON c.id=a.chapter_id WHERE a.is_published=1';
            $params=[];
            if (!empty($filters['level'])) { $sql.=' AND l.slug=?'; $params[]=$filters['level']; }
            if (!empty($filters['q'])) { $sql.=' AND (a.title LIKE ? OR a.instructions LIKE ? OR c.title LIKE ?)'; $q='%'.$filters['q'].'%'; array_push($params,$q,$q,$q); }
            $sql.=' ORDER BY a.due_date IS NULL, a.due_date, l.sort_order, a.id';
            $s=$pdo->prepare($sql); $s->execute($params); return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    $rows = seed_assignments();
    if (!empty($filters['level'])) $rows = array_values(array_filter($rows, fn($a) => $a['level_slug'] === $filters['level']));
    if (!empty($filters['q'])) {
        $q = strtolower($filters['q']);
        $rows = array_values(array_filter($rows, fn($a) => strpos(strtolower(($a['title'] ?? '') . ' ' . ($a['instructions'] ?? '') . ' ' . ($a['subject'] ?? '')), $q) !== false));
    }
    return $rows;
}

function slugify_text(string $text, string $fallback = 'element'): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text ?? '', '-') ?: $fallback;
}

function get_qcms(array $filters = []): array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT q.*, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent, c.slug AS chapter_slug, c.title AS chapter_title FROM qcms q JOIN levels l ON l.id=q.level_id LEFT JOIN chapters c ON c.id=q.chapter_id WHERE 1=1';
            $params = [];
            if (empty($filters['includeDrafts'])) $sql .= ' AND q.is_published=1';
            if (!empty($filters['level'])) { $sql .= ' AND l.slug=?'; $params[] = $filters['level']; }
            if (!empty($filters['level_id'])) { $sql .= ' AND q.level_id=?'; $params[] = (int)$filters['level_id']; }
            if (!empty($filters['chapter_id'])) { $sql .= ' AND q.chapter_id=?'; $params[] = (int)$filters['chapter_id']; }
            if (isset($filters['status']) && $filters['status'] !== '') { $sql .= ' AND q.is_published=?'; $params[] = (int)$filters['status']; }
            if (!empty($filters['q'])) {
                $sql .= ' AND (q.title LIKE ? OR c.title LIKE ?)';
                $needle = '%' . $filters['q'] . '%';
                array_push($params, $needle, $needle);
            }
            $sql .= ' ORDER BY l.sort_order, q.sort_order, q.id';
            $s = $pdo->prepare($sql);
            $s->execute($params);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}

function get_qcm(string $slug, bool $includeDrafts = false): ?array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT q.*, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent, c.slug AS chapter_slug, c.title AS chapter_title FROM qcms q JOIN levels l ON l.id=q.level_id LEFT JOIN chapters c ON c.id=q.chapter_id WHERE q.slug=?';
            if (!$includeDrafts) $sql .= ' AND q.is_published=1';
            $sql .= ' LIMIT 1';
            $s = $pdo->prepare($sql);
            $s->execute([$slug]);
            $row = $s->fetch();
            return $row ?: null;
        } catch (Throwable $e) {}
    }
    return null;
}

function get_qcm_questions(int $qcmId): array {
    if ($pdo = db()) {
        try {
            $s = $pdo->prepare('SELECT * FROM qcm_questions WHERE qcm_id=? ORDER BY sort_order,id');
            $s->execute([$qcmId]);
            $questions = $s->fetchAll();
            foreach ($questions as &$question) {
                $choices = $pdo->prepare('SELECT * FROM qcm_choices WHERE question_id=? ORDER BY sort_order,id');
                $choices->execute([(int)$question['id']]);
                $question['choices'] = $choices->fetchAll();
            }
            unset($question);
            return $questions;
        } catch (Throwable $e) {}
    }
    return [];
}

function get_flashcard_decks(array $filters = []): array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT d.*, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent, c.slug AS chapter_slug, c.title AS chapter_title, (SELECT COUNT(*) FROM flashcards f WHERE f.deck_id=d.id) AS card_count FROM flashcard_decks d JOIN levels l ON l.id=d.level_id LEFT JOIN chapters c ON c.id=d.chapter_id WHERE 1=1';
            $params = [];
            if (empty($filters['includeDrafts'])) $sql .= ' AND d.is_published=1';
            if (!empty($filters['level'])) { $sql .= ' AND l.slug=?'; $params[] = $filters['level']; }
            if (!empty($filters['level_id'])) { $sql .= ' AND d.level_id=?'; $params[] = (int)$filters['level_id']; }
            if (!empty($filters['chapter_id'])) { $sql .= ' AND d.chapter_id=?'; $params[] = (int)$filters['chapter_id']; }
            if (isset($filters['status']) && $filters['status'] !== '') { $sql .= ' AND d.is_published=?'; $params[] = (int)$filters['status']; }
            if (!empty($filters['q'])) {
                $sql .= ' AND (d.title LIKE ? OR c.title LIKE ?)';
                $needle = '%' . $filters['q'] . '%';
                array_push($params, $needle, $needle);
            }
            $sql .= ' ORDER BY l.sort_order, d.sort_order, d.id';
            $s = $pdo->prepare($sql);
            $s->execute($params);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}

function get_flashcard_deck(string $slug, bool $includeDrafts = false): ?array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT d.*, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent, c.slug AS chapter_slug, c.title AS chapter_title FROM flashcard_decks d JOIN levels l ON l.id=d.level_id LEFT JOIN chapters c ON c.id=d.chapter_id WHERE d.slug=?';
            if (!$includeDrafts) $sql .= ' AND d.is_published=1';
            $sql .= ' LIMIT 1';
            $s = $pdo->prepare($sql);
            $s->execute([$slug]);
            $row = $s->fetch();
            return $row ?: null;
        } catch (Throwable $e) {}
    }
    return null;
}

function get_flashcards(int $deckId): array {
    if ($pdo = db()) {
        try {
            $s = $pdo->prepare('SELECT * FROM flashcards WHERE deck_id=? ORDER BY sort_order,id');
            $s->execute([$deckId]);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}

function get_exercises(array $filters = []): array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT e.*, c.slug AS chapter_slug, c.title AS chapter_title, c.number AS chapter_number, l.slug AS level_slug, l.short AS level_short, l.name AS level_name, l.accent FROM exercises e JOIN chapters c ON c.id=e.chapter_id JOIN levels l ON l.id=c.level_id WHERE 1=1';
            $params = [];
            if (empty($filters['includeDrafts'])) $sql .= ' AND e.is_published=1';
            if (!empty($filters['level'])) { $sql .= ' AND l.slug=?'; $params[] = $filters['level']; }
            if (!empty($filters['level_id'])) { $sql .= ' AND l.id=?'; $params[] = (int)$filters['level_id']; }
            if (!empty($filters['chapter_id'])) { $sql .= ' AND e.chapter_id=?'; $params[] = (int)$filters['chapter_id']; }
            if (!empty($filters['category'])) { $sql .= ' AND e.category=?'; $params[] = $filters['category']; }
            if (!empty($filters['difficulty'])) { $sql .= ' AND e.difficulty=?'; $params[] = $filters['difficulty']; }
            if (($filters['correction'] ?? '') === 'yes') $sql .= " AND TRIM(COALESCE(e.correction,''))<>''";
            if (($filters['correction'] ?? '') === 'no') $sql .= " AND TRIM(COALESCE(e.correction,''))=''";
            if (isset($filters['status']) && $filters['status'] !== '') { $sql .= ' AND e.is_published=?'; $params[] = (int)$filters['status']; }
            if (!empty($filters['q'])) {
                $sql .= ' AND (e.title LIKE ? OR e.statement LIKE ? OR c.title LIKE ?)';
                $needle = '%' . $filters['q'] . '%';
                array_push($params, $needle, $needle, $needle);
            }
            $sql .= ' ORDER BY l.sort_order, c.number, e.sort_order, e.id';
            $s = $pdo->prepare($sql);
            $s->execute($params);
            return $s->fetchAll();
        } catch (Throwable $e) {}
    }
    return [];
}

/* ---------------------------------------------------------------------------
   V75 : fiches methodes (ex pages statiques pages/methodes/)
   --------------------------------------------------------------------------- */
function seed_methods(): array {
    $seed = __DIR__ . '/seed-methods.php';
    return is_file($seed) ? require $seed : [];
}
function methods_available(): bool {
    static $available = null;
    if ($available !== null) return $available;
    $pdo = db();
    if (!$pdo) return $available = false;
    try {
        $pdo->query('SELECT 1 FROM methods LIMIT 1');
        return $available = true;
    } catch (Throwable $e) {
        return $available = false;
    }
}
function get_methods(bool $includeDrafts = false, string $query = ''): array {
    $query = trim($query);
    if ($pdo = db()) {
        try {
            $where = [];
            $params = [];
            if (!$includeDrafts) $where[] = 'is_published=1';
            if ($query !== '') {
                $where[] = '(title LIKE ? OR summary LIKE ? OR domain LIKE ? OR content LIKE ?)';
                $like = '%' . $query . '%';
                $params = [$like, $like, $like, $like];
            }
            $sql = 'SELECT * FROM methods';
            if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
            $sql .= ' ORDER BY sort_order, title, id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            if ($rows) return $rows;
        } catch (Throwable $e) {}
    }
    $rows = seed_methods();
    if ($query !== '') {
        $needle = mb_strtolower($query);
        $rows = array_values(array_filter($rows, static function ($method) use ($needle) {
            $haystack = mb_strtolower(($method['title'] ?? '') . ' ' . ($method['summary'] ?? '') . ' ' . ($method['domain'] ?? '') . ' ' . strip_tags((string)($method['content'] ?? '')));
            return mb_strpos($haystack, $needle) !== false;
        }));
    } else {
        $rows = array_slice($rows, 0, 200);
    }
    return $rows;
}
function get_method(string $slug, bool $includeDrafts = false): ?array {
    if ($pdo = db()) {
        try {
            $sql = 'SELECT * FROM methods WHERE slug=?' . ($includeDrafts ? '' : ' AND is_published=1') . ' LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$slug]);
            $row = $stmt->fetch();
            if ($row) return $row;
        } catch (Throwable $e) {}
    }
    foreach (seed_methods() as $method) {
        if (($method['slug'] ?? '') === $slug) return $method;
    }
    return null;
}
function method_domains(): array {
    $domains = [];
    foreach (get_methods(true) as $method) {
        $domain = trim((string)($method['domain'] ?? ''));
        if ($domain !== '' && !in_array($domain, $domains, true)) $domains[] = $domain;
    }
    sort($domains);
    return $domains;
}

/* ---------------------------------------------------------------------------
   V75 : resultats des QCM (suivi pedagogique)
   --------------------------------------------------------------------------- */
function qcm_attempts_available(): bool {
    static $available = null;
    if ($available !== null) return $available;
    $pdo = db();
    if (!$pdo) return $available = false;
    try {
        $pdo->query('SELECT 1 FROM qcm_attempts LIMIT 1');
        return $available = true;
    } catch (Throwable $e) {
        return $available = false;
    }
}

/**
 * Enregistre une tentative de QCM et le detail des reponses.
 * @param array<int,array{question_id:int,choice_label:?string,is_correct:bool}> $answers
 */
function save_qcm_attempt(int $qcmId, array $answers, int $score, int $total, string $studentName = '', string $studentClass = '', int $duration = 0): ?int {
    $pdo = db();
    if (!$pdo || !qcm_attempts_available() || $qcmId <= 0 || $total <= 0) return null;
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO qcm_attempts (qcm_id, student_name, student_class, score, total, duration_seconds) VALUES (?,?,?,?,?,?)');
        $stmt->execute([
            $qcmId,
            mb_substr(trim($studentName), 0, 120),
            mb_substr(trim($studentClass), 0, 60),
            max(0, min($score, $total)),
            $total,
            max(0, $duration),
        ]);
        $attemptId = (int)$pdo->lastInsertId();
        $insert = $pdo->prepare('INSERT INTO qcm_attempt_answers (attempt_id, question_id, choice_label, is_correct) VALUES (?,?,?,?)');
        foreach ($answers as $answer) {
            $insert->execute([
                $attemptId,
                (int)$answer['question_id'],
                $answer['choice_label'] !== null ? mb_substr((string)$answer['choice_label'], 0, 1) : null,
                !empty($answer['is_correct']) ? 1 : 0,
            ]);
        }
        $pdo->commit();
        return $attemptId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return null;
    }
}

/** Tentatives recentes, filtrables par QCM, niveau, classe et periode. */
function get_qcm_attempts(array $filters = [], int $limit = 200): array {
    $pdo = db();
    if (!$pdo || !qcm_attempts_available()) return [];
    try {
        $where = [];
        $params = [];
        if (!empty($filters['qcm_id'])) { $where[] = 'a.qcm_id = ?'; $params[] = (int)$filters['qcm_id']; }
        if (!empty($filters['level'])) { $where[] = 'l.slug = ?'; $params[] = (string)$filters['level']; }
        if (!empty($filters['class'])) { $where[] = 'a.student_class = ?'; $params[] = (string)$filters['class']; }
        if (!empty($filters['since'])) { $where[] = 'a.created_at >= ?'; $params[] = (string)$filters['since'] . ' 00:00:00'; }
        $sql = 'SELECT a.*, q.title AS qcm_title, q.slug AS qcm_slug, l.short AS level_short, l.accent, l.slug AS level_slug
                FROM qcm_attempts a
                JOIN qcms q ON q.id = a.qcm_id
                JOIN levels l ON l.id = q.level_id';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY a.created_at DESC, a.id DESC LIMIT ' . max(1, min($limit, 1000));
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Taux de reussite par question pour un QCM donne. */
function get_qcm_question_stats(int $qcmId): array {
    $pdo = db();
    if (!$pdo || !qcm_attempts_available() || $qcmId <= 0) return [];
    try {
        $stmt = $pdo->prepare(
            'SELECT qq.id AS question_id, qq.question_text, qq.sort_order,
                    COUNT(aa.id) AS answers_count,
                    SUM(aa.is_correct) AS correct_count
             FROM qcm_questions qq
             LEFT JOIN qcm_attempt_answers aa ON aa.question_id = qq.id
             LEFT JOIN qcm_attempts a ON a.id = aa.attempt_id AND a.qcm_id = qq.qcm_id
             WHERE qq.qcm_id = ?
             GROUP BY qq.id, qq.question_text, qq.sort_order
             ORDER BY qq.sort_order, qq.id');
        $stmt->execute([$qcmId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Synthese par QCM : nombre de tentatives, moyenne, taux de reussite. */
function get_qcm_summary(array $filters = []): array {
    $pdo = db();
    if (!$pdo || !qcm_attempts_available()) return [];
    try {
        $where = [];
        $params = [];
        if (!empty($filters['qcm_id'])) { $where[] = 'q.id = ?'; $params[] = (int)$filters['qcm_id']; }
        if (!empty($filters['level'])) { $where[] = 'l.slug = ?'; $params[] = (string)$filters['level']; }
        if (!empty($filters['class'])) { $where[] = 'a.student_class = ?'; $params[] = (string)$filters['class']; }
        if (!empty($filters['since'])) { $where[] = 'a.created_at >= ?'; $params[] = (string)$filters['since'] . ' 00:00:00'; }
        $sql = 'SELECT q.id, q.title, q.slug, l.short AS level_short, l.accent,
                       COUNT(a.id) AS attempts,
                       COUNT(DISTINCT NULLIF(a.student_name, "")) AS students,
                       ROUND(AVG(a.score / NULLIF(a.total, 0) * 100), 1) AS average_percent,
                       ROUND(MAX(a.score / NULLIF(a.total, 0) * 100), 1) AS best_percent
                FROM qcms q
                JOIN levels l ON l.id = q.level_id
                LEFT JOIN qcm_attempts a ON a.qcm_id = q.id';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' GROUP BY q.id, q.title, q.slug, l.short, l.accent ORDER BY attempts DESC, q.title';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Classes distinctes ayant deja compose, pour alimenter le filtre. */
function get_attempt_classes(): array {
    $pdo = db();
    if (!$pdo || !qcm_attempts_available()) return [];
    try {
        return $pdo->query('SELECT DISTINCT student_class FROM qcm_attempts WHERE student_class <> "" ORDER BY student_class')->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        return [];
    }
}

/** Tentatives d'un eleve donne (nom + classe), pour son historique. */
function get_student_attempts(string $name, string $class, int $limit = 30): array {
    return get_qcm_attempts(['class' => $class], $limit);
}
