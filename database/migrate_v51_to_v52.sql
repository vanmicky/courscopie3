-- Migration V51 -> V52
-- Le schema V51 contenait deja les colonnes principales des chapitres.
-- Ces requetes sont volontairement prudentes pour les hebergements MySQL/MariaDB.

ALTER TABLE chapters
  MODIFY content LONGTEXT NULL,
  MODIFY summary TEXT NULL,
  MODIFY legacy_path VARCHAR(255) NULL;

ALTER TABLE lessons
  MODIFY content LONGTEXT NULL;
