-- Migration V55 -> V56
-- Admin complet pour niveaux, matieres et images.

ALTER TABLE subjects
  ADD COLUMN accent VARCHAR(20) NOT NULL DEFAULT '#2563eb',
  ADD COLUMN image VARCHAR(255) DEFAULT NULL,
  ADD COLUMN summary TEXT,
  ADD COLUMN sort_order INT NOT NULL DEFAULT 0,
  ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

UPDATE subjects SET accent='#2563eb', summary='Cours et activités de physique-chimie.', sort_order=1 WHERE slug='physique-chimie';
UPDATE subjects SET accent='#ca8a04', summary='Cours, exercices et automatismes mathématiques.', sort_order=2 WHERE slug='mathematiques';
UPDATE subjects SET accent='#dc2626', summary='Spécialité physique-chimie au lycée.', sort_order=3 WHERE slug='specialite-physique-chimie';
UPDATE subjects SET accent='#7c3aed', summary='Algorithmique, programmation, web et données.', sort_order=4 WHERE slug='specialite-nsi';
