-- V59 - Journal des sauvegardes et restaurations
SET NAMES utf8mb4;

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
