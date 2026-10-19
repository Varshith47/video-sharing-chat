-- VideoShare Chat upgrade (run AFTER importing db.sql)
-- USE `vsp`;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_type` ENUM('video','direct') NOT NULL,
  `video_id` INT(11) DEFAULT NULL,
  `sender_id` INT(11) NOT NULL,
  `recipient_id` INT(11) DEFAULT NULL,
  `message` VARCHAR(2000) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_video_chat` (`room_type`,`video_id`,`id`),
  KEY `idx_direct_chat` (`room_type`,`sender_id`,`recipient_id`,`id`),
  CONSTRAINT `chat_messages_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_recipient_fk` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_video_fk` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_typing` (
  `user_id` INT(11) NOT NULL,
  `room_type` ENUM('video','direct') NOT NULL,
  `video_id` INT(11) NOT NULL DEFAULT 0,
  `recipient_id` INT(11) NOT NULL DEFAULT 0,
  `is_typing` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`room_type`,`video_id`,`recipient_id`),
  KEY `idx_typing_lookup` (`room_type`,`video_id`,`recipient_id`,`updated_at`),
  CONSTRAINT `chat_typing_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `video_playback_state` (
  `video_id` INT(11) NOT NULL,
  `updated_by` INT(11) NOT NULL,
  `position_seconds` DECIMAL(10,3) NOT NULL DEFAULT 0,
  `is_paused` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`video_id`),
  CONSTRAINT `playback_video_fk` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `playback_user_fk` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
