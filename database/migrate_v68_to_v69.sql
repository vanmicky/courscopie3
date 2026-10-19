-- V69 : ordre manuel des ressources d'un chapitre.
ALTER TABLE `files` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0;
ALTER TABLE `exercises` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0;
ALTER TABLE `qcms` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0;
ALTER TABLE `flashcard_decks` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0;
