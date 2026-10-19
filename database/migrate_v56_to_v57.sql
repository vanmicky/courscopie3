-- Migration V56 -> V57
-- Blocs de cours et fichiers relies aux chapitres.

ALTER TABLE lessons
  ADD COLUMN kind VARCHAR(80) NOT NULL DEFAULT 'Cours' AFTER title;

CREATE INDEX idx_lessons_chapter_order ON lessons(chapter_id, sort_order);
CREATE INDEX idx_files_chapter_public ON files(chapter_id, is_published);
