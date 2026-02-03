-- Event Registration Module Database Schema
-- Drupal 10 Compatible

-- Event Configuration Table
CREATE TABLE IF NOT EXISTS `event_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `event_date` INT(11) NOT NULL,
  `reg_start` INT(11) NOT NULL,
  `reg_end` INT(11) NOT NULL,
  `created` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `event_date` (`event_date`),
  INDEX `category` (`category`),
  INDEX `reg_dates` (`reg_start`, `reg_end`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event Registration Table
CREATE TABLE IF NOT EXISTS `event_registration` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) UNSIGNED NOT NULL,
  `event_name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `event_date` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `college` VARCHAR(255) NOT NULL,
  `department` VARCHAR(255) NOT NULL,
  `created` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_event_date` (`email`, `event_date`),
  FOREIGN KEY (`event_id`) REFERENCES `event_config`(`id`) ON DELETE CASCADE,
  INDEX `event_id` (`event_id`),
  INDEX `email` (`email`),
  INDEX `event_date` (`event_date`),
  INDEX `created` (`created`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuration Storage for Module Settings
-- This is automatically created by Drupal Config API
-- INSERT statements for initial config if needed:
-- INSERT INTO `config` (`collection`, `name`, `data`) VALUES
-- ('', 'event_registration.settings', 'a:3:{s:11:"admin_email";s:0:"";s:26:"enable_admin_notifications";i:1;s:28:"notification_email_subject";s:20:"New Event Registration";}');
