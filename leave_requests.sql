-- Run this in phpMyAdmin to add leave request support
USE `location`;

CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `emp_id` INT NOT NULL,
  `leave_date` DATE NOT NULL,
  `leave_type` VARCHAR(50) NOT NULL DEFAULT 'Annual',
  `reason` TEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `admin_note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`emp_id`) REFERENCES `employee`(`emp_id`)
);

-- Add is_late column to checkin if not exists
ALTER TABLE `checkin` ADD COLUMN IF NOT EXISTS `is_late` TINYINT(1) NOT NULL DEFAULT 0;
