-- Schema V51 PHP/MySQL - Les cours de M. Gedion
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120) NOT NULL DEFAULT 'Admin',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS backup_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action VARCHAR(40) NOT NULL,
  mode VARCHAR(40) DEFAULT NULL,
  filename VARCHAR(255) DEFAULT NULL,
  row_count INT NOT NULL DEFAULT 0,
  table_count INT NOT NULL DEFAULT 0,
  status VARCHAR(40) NOT NULL DEFAULT 'success',
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS levels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  short VARCHAR(40) NOT NULL,
  name VARCHAR(190) NOT NULL,
  subject VARCHAR(190) NOT NULL,
  accent VARCHAR(20) NOT NULL DEFAULT '#2563eb',
  image VARCHAR(255) DEFAULT NULL,
  alt_text VARCHAR(255) DEFAULT NULL,
  summary TEXT,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  name VARCHAR(190) NOT NULL,
  accent VARCHAR(20) NOT NULL DEFAULT '#2563eb',
  image VARCHAR(255) DEFAULT NULL,
  alt_text VARCHAR(255) DEFAULT NULL,
  summary TEXT,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chapters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  level_id INT NOT NULL,
  subject_id INT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE,
  number INT NOT NULL DEFAULT 1,
  title VARCHAR(255) NOT NULL,
  summary TEXT,
  content LONGTEXT,
  legacy_path VARCHAR(255) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_chapters_level FOREIGN KEY(level_id) REFERENCES levels(id) ON DELETE CASCADE,
  CONSTRAINT fk_chapters_subject FOREIGN KEY(subject_id) REFERENCES subjects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lessons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  kind VARCHAR(80) NOT NULL DEFAULT 'Cours',
  content LONGTEXT,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lessons_chapter FOREIGN KEY(chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exercises (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  statement LONGTEXT,
  correction LONGTEXT,
  difficulty VARCHAR(40) DEFAULT 'normal',
  category VARCHAR(40) NOT NULL DEFAULT 'ds',
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_exercises_chapter FOREIGN KEY(chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qcms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NULL,
  level_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_qcms_chapter FOREIGN KEY(chapter_id) REFERENCES chapters(id) ON DELETE SET NULL,
  CONSTRAINT fk_qcms_level FOREIGN KEY(level_id) REFERENCES levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qcm_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  qcm_id INT NOT NULL,
  question_text LONGTEXT NOT NULL,
  explanation LONGTEXT,
  sort_order INT NOT NULL DEFAULT 0,
  image_path VARCHAR(255) NULL,
  image_width SMALLINT UNSIGNED NOT NULL DEFAULT 40,
  CONSTRAINT fk_qcm_questions_qcm FOREIGN KEY(qcm_id) REFERENCES qcms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qcm_choices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_id INT NOT NULL,
  choice_label CHAR(1) NOT NULL,
  choice_text LONGTEXT NOT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_qcm_choices_question FOREIGN KEY(question_id) REFERENCES qcm_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS flashcard_decks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NULL,
  level_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_decks_chapter FOREIGN KEY(chapter_id) REFERENCES chapters(id) ON DELETE SET NULL,
  CONSTRAINT fk_decks_level FOREIGN KEY(level_id) REFERENCES levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS flashcards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  deck_id INT NOT NULL,
  front_text LONGTEXT NOT NULL,
  back_text LONGTEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  image_path VARCHAR(255) NULL,
  image_width SMALLINT UNSIGNED NOT NULL DEFAULT 40,
  CONSTRAINT fk_flashcards_deck FOREIGN KEY(deck_id) REFERENCES flashcard_decks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NULL,
  level_id INT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) DEFAULT NULL,
  size_bytes INT DEFAULT NULL,
  label VARCHAR(190) DEFAULT NULL,
  resource_type VARCHAR(80) NOT NULL DEFAULT 'document',
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_files_chapter FOREIGN KEY(chapter_id) REFERENCES chapters(id) ON DELETE SET NULL,
  CONSTRAINT fk_files_level FOREIGN KEY(level_id) REFERENCES levels(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chapter_videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  url VARCHAR(500) NOT NULL,
  description TEXT,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_chapter_videos_chapter FOREIGN KEY(chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chapter_skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NOT NULL,
  category VARCHAR(40) NOT NULL DEFAULT 'Savoir',
  label VARCHAR(500) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_chapter_skills_chapter FOREIGN KEY(chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  level_id INT NOT NULL,
  subject_id INT NULL,
  chapter_id INT NULL,
  title VARCHAR(255) NOT NULL,
  kind VARCHAR(80) NOT NULL DEFAULT 'Travail',
  instructions LONGTEXT NOT NULL,
  due_date DATE NULL,
  priority VARCHAR(40) NOT NULL DEFAULT 'normal',
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_assignments_level FOREIGN KEY(level_id) REFERENCES levels(id) ON DELETE CASCADE,
  CONSTRAINT fk_assignments_subject FOREIGN KEY(subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
  CONSTRAINT fk_assignments_chapter FOREIGN KEY(chapter_id) REFERENCES chapters(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assignment_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  assignment_id INT NOT NULL,
  file_id INT NOT NULL,
  label VARCHAR(190) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_assignment_files_assignment FOREIGN KEY(assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  CONSTRAINT fk_assignment_files_file FOREIGN KEY(file_id) REFERENCES files(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assignment_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  assignment_id INT NOT NULL,
  label VARCHAR(190) NOT NULL,
  url VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_assignment_links_assignment FOREIGN KEY(assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  level_id INT NULL,
  title VARCHAR(255) NOT NULL,
  content LONGTEXT NOT NULL,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_announcements_level FOREIGN KEY(level_id) REFERENCES levels(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- V75 : fiches methodes (contenu des anciennes pages statiques pages/methodes/)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(190) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  summary TEXT,
  domain VARCHAR(120) NOT NULL DEFAULT 'Methode generale',
  levels VARCHAR(190) NOT NULL DEFAULT 'Tous niveaux',
  accent VARCHAR(20) NOT NULL DEFAULT '#2563eb',
  icon VARCHAR(16) NOT NULL DEFAULT '',
  content LONGTEXT,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- V75 : resultats des QCM (suivi pedagogique)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS qcm_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  qcm_id INT NOT NULL,
  student_name VARCHAR(120) NOT NULL DEFAULT '',
  student_class VARCHAR(60) NOT NULL DEFAULT '',
  score INT NOT NULL DEFAULT 0,
  total INT NOT NULL DEFAULT 0,
  duration_seconds INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_attempts_qcm (qcm_id),
  KEY idx_attempts_class (student_class),
  KEY idx_attempts_date (created_at),
  CONSTRAINT fk_attempts_qcm FOREIGN KEY(qcm_id) REFERENCES qcms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qcm_attempt_answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT NOT NULL,
  question_id INT NOT NULL,
  choice_label CHAR(1) DEFAULT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  KEY idx_answers_attempt (attempt_id),
  KEY idx_answers_question (question_id),
  CONSTRAINT fk_answers_attempt FOREIGN KEY(attempt_id) REFERENCES qcm_attempts(id) ON DELETE CASCADE,
  CONSTRAINT fk_answers_question FOREIGN KEY(question_id) REFERENCES qcm_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Statistiques de fréquentation (V115)
-- Une ligne par visite. Les adresses IP ne sont pas stockées : seule une
-- empreinte SHA-256 permet de compter les visiteurs différents.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `visits` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `visited_at` DATETIME NOT NULL,
  `visited_day` DATE NOT NULL,
  `country` VARCHAR(8) NOT NULL DEFAULT 'ZZ',
  `path` VARCHAR(160) NOT NULL DEFAULT '',
  `referrer` VARCHAR(160) NOT NULL DEFAULT '',
  `visitor` CHAR(64) NOT NULL DEFAULT '',
  `is_bot` TINYINT(1) NOT NULL DEFAULT 0,
  KEY `idx_visits_day` (`visited_day`),
  KEY `idx_visits_country` (`country`),
  KEY `idx_visits_bot` (`is_bot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
