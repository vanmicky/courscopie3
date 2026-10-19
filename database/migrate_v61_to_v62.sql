-- Migration V61 -> V62
-- Corrige l'erreur : Unknown column 'kind' in 'field list'.
-- A executer uniquement si la colonne kind est absente de la table lessons.

ALTER TABLE `lessons`
  ADD COLUMN `kind` VARCHAR(80) NOT NULL DEFAULT 'Cours' AFTER `title`;
