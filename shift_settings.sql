-- Run this in phpMyAdmin to add shift settings support
USE `location`;

CREATE TABLE IF NOT EXISTS `shift_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `shift_start` TIME NOT NULL DEFAULT '09:00:00',
  `grace_minutes` INT NOT NULL DEFAULT 10
);

INSERT INTO `shift_settings` (`id`, `shift_start`, `grace_minutes`)
VALUES (1, '09:00:00', 10)
ON DUPLICATE KEY UPDATE `id`=`id`;
