-- Kristohanon account and personalization schema for MySQL/MariaDB.
-- Apply once in Hostinger phpMyAdmin before enabling accounts.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(249) COLLATE utf8mb4_unicode_ci NOT NULL,
  password VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  username VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  status TINYINT UNSIGNED NOT NULL DEFAULT 0,
  verified TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  resettable TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  roles_mask INT UNSIGNED NOT NULL DEFAULT 0,
  registered INT UNSIGNED NOT NULL,
  last_login INT UNSIGNED DEFAULT NULL,
  force_logout MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id), UNIQUE KEY users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users_confirmations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT, user_id INT UNSIGNED NOT NULL,
  email VARCHAR(249) COLLATE utf8mb4_unicode_ci NOT NULL,
  selector VARCHAR(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  token VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  expires INT UNSIGNED NOT NULL, PRIMARY KEY (id), UNIQUE KEY confirmations_selector (selector),
  KEY confirmations_email_expires (email, expires), KEY confirmations_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users_remembered (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user INT UNSIGNED NOT NULL,
  selector VARCHAR(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  token VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  expires INT UNSIGNED NOT NULL, PRIMARY KEY (id), UNIQUE KEY remembered_selector (selector), KEY remembered_user (user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users_resets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user INT UNSIGNED NOT NULL,
  selector VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  token VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  expires INT UNSIGNED NOT NULL, PRIMARY KEY (id), UNIQUE KEY resets_selector (selector), KEY resets_user_expires (user, expires)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users_throttling (
  bucket VARCHAR(44) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  tokens FLOAT UNSIGNED NOT NULL, replenished_at INT UNSIGNED NOT NULL,
  expires_at INT UNSIGNED NOT NULL, PRIMARY KEY (bucket), KEY throttling_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_preferences (
  user_id INT UNSIGNED NOT NULL,
  language ENUM('en','ceb') NOT NULL DEFAULT 'en',
  topics JSON NOT NULL,
  email_digest ENUM('off','daily','weekly') NOT NULL DEFAULT 'off',
  quiet_hours_start TIME NOT NULL DEFAULT '21:00:00',
  quiet_hours_end TIME NOT NULL DEFAULT '07:00:00',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_interactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  interaction_type ENUM('view','save','helpful','dismiss','pray','complete') NOT NULL,
  content_id VARCHAR(120) NOT NULL,
  topic VARCHAR(60) NOT NULL DEFAULT '',
  weight SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY interactions_user_date (user_id, created_at), KEY interactions_user_topic (user_id, topic)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_passages (
  user_id INT UNSIGNED NOT NULL, passage_id VARCHAR(120) NOT NULL,
  saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, passage_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feed_impressions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_id INT UNSIGNED NOT NULL,
  content_id VARCHAR(120) NOT NULL, position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  shown_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY feed_user_date (user_id, shown_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feed_readings (
  id VARCHAR(120) NOT NULL,
  passage_id VARCHAR(120) NOT NULL,
  language ENUM('en','ceb') NOT NULL,
  topic VARCHAR(60) NOT NULL,
  title VARCHAR(180) NOT NULL,
  reflection TEXT NOT NULL,
  practice VARCHAR(500) NOT NULL,
  prayer TEXT NOT NULL,
  publish_on DATE NOT NULL,
  status ENUM('scheduled','published') NOT NULL DEFAULT 'scheduled',
  source_batch VARCHAR(40) NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY feed_readings_schedule (language, status, publish_on),
  KEY feed_readings_passage (passage_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_id INT UNSIGNED NOT NULL,
  notification_type VARCHAR(40) NOT NULL, title VARCHAR(160) NOT NULL,
  message VARCHAR(500) NOT NULL, action_url VARCHAR(255) NOT NULL DEFAULT '/feed',
  read_at TIMESTAMP NULL DEFAULT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY notifications_user_read (user_id, read_at, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_queue (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_id INT UNSIGNED NOT NULL,
  recipient VARCHAR(249) NOT NULL, subject VARCHAR(190) NOT NULL, body TEXT NOT NULL,
  status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending', attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  available_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, sent_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY email_status_available (status, available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
