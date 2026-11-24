-- Movies & Series Streaming Platform — MySQL / MariaDB schema
-- Import with: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS cinehub_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cinehub_db;

CREATE TABLE IF NOT EXISTS media_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tmdb_id VARCHAR(20) NOT NULL,
  type ENUM('movie', 'series') NOT NULL,
  title VARCHAR(255) NOT NULL,
  poster VARCHAR(500) DEFAULT NULL,
  rating VARCHAR(10) DEFAULT NULL,
  year VARCHAR(10) DEFAULT NULL,
  genre TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_media_type_tmdb (type, tmdb_id),
  KEY idx_media_type (type),
  KEY idx_media_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(64) NOT NULL,
  setting_value TEXT DEFAULT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value)
VALUES ('show_annoying_ads', '0')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

CREATE TABLE IF NOT EXISTS visitors_log (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip_address VARCHAR(45) NOT NULL,
  browser VARCHAR(50) DEFAULT NULL,
  os VARCHAR(50) DEFAULT NULL,
  visit_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_visit_time (visit_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
