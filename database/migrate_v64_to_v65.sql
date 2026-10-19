-- Migration V64 -> V65
-- Classe les exercices dans les sections DS, Brevet ou Bac.

ALTER TABLE `exercises`
  ADD COLUMN `category` VARCHAR(40) NOT NULL DEFAULT 'ds' AFTER `difficulty`;
