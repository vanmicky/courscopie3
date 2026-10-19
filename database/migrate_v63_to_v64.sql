-- Migration V63 -> V64
-- Ressources de chapitre : categories de documents, videos et savoirs.

ALTER TABLE `files`
  ADD COLUMN `resource_type` VARCHAR(80) NOT NULL DEFAULT 'document' AFTER `label`;

CREATE TABLE IF NOT EXISTS `chapter_videos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `chapter_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `description` TEXT,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_chapter_videos_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chapter_skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `chapter_id` INT NOT NULL,
  `category` VARCHAR(40) NOT NULL DEFAULT 'Savoir',
  `label` VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_chapter_skills_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
