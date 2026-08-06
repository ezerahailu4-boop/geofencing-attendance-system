-- Geo-fencing Attendance System Database
-- Database: location

CREATE DATABASE IF NOT EXISTS `location`;
USE `location`;

-- Admin table
CREATE TABLE `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(100) NOT NULL,
  `image` VARCHAR(100) DEFAULT 'admin.jpg'
);

-- Theme table
CREATE TABLE `theme` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `theme` VARCHAR(50) NOT NULL DEFAULT 'theme-default'
);

-- Employee table
CREATE TABLE `employee` (
  `emp_id` INT AUTO_INCREMENT PRIMARY KEY,
  `emp_name` VARCHAR(100) NOT NULL,
  `emp_gender` VARCHAR(10) NOT NULL,
  `emp_dob` DATE NOT NULL,
  `emp_position` VARCHAR(100) NOT NULL,
  `emp_address` TEXT NOT NULL,
  `emp_mobile` VARCHAR(20) NOT NULL,
  `emp_email` VARCHAR(100) NOT NULL UNIQUE,
  `emp_password` VARCHAR(100) NOT NULL,
  `emp_joining_date` DATE NOT NULL,
  `image` VARCHAR(100) DEFAULT 'user.jpg',
  `status` VARCHAR(20) DEFAULT 'Active'
);

-- Location (master) table
CREATE TABLE `master` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lat` VARCHAR(50) NOT NULL,
  `longi` VARCHAR(50) NOT NULL,
  `address` TEXT NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Active'
);

-- Assign location (geofencing) table
CREATE TABLE `assign_location` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `emp_id` INT NOT NULL,
  `location_id` INT NOT NULL,
  `distance_limit` INT NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Active',
  FOREIGN KEY (`emp_id`) REFERENCES `employee`(`emp_id`),
  FOREIGN KEY (`location_id`) REFERENCES `master`(`id`)
);

-- Check-in table
CREATE TABLE `checkin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` INT NOT NULL,
  `lat` VARCHAR(50) NOT NULL,
  `longi` VARCHAR(50) NOT NULL,
  `address` TEXT NOT NULL,
  `distance` VARCHAR(50) NOT NULL,
  `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Check-out table
CREATE TABLE `checkout` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` INT NOT NULL,
  `lat` VARCHAR(50) NOT NULL,
  `longi` VARCHAR(50) NOT NULL,
  `address` TEXT NOT NULL,
  `check_in_id` INT NOT NULL,
  `distance` VARCHAR(50) NOT NULL,
  `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`check_in_id`) REFERENCES `checkin`(`id`)
);

-- Projects table
CREATE TABLE `projects` (
  `project_id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_name` VARCHAR(100) NOT NULL,
  `project_start_date` DATE NOT NULL,
  `project_end_date` DATE NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Active'
);

-- Employee-Project assignment table
CREATE TABLE `emp_project_matrix` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `emp_id` INT NOT NULL,
  `project_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Active',
  FOREIGN KEY (`emp_id`) REFERENCES `employee`(`emp_id`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`project_id`)
);

-- Holiday table
CREATE TABLE `holiday` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `holiday_date` DATE NOT NULL UNIQUE,
  `holiday_event` VARCHAR(100) NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Active'
);

-- Messages table
CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `emp_id` INT NOT NULL,
  `admin_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `messageStatus` VARCHAR(20) DEFAULT 'Sent',
  `viewStatus` VARCHAR(20) DEFAULT 'NotSeen',
  `status` VARCHAR(20) DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Default seed data
-- --------------------------------------------------------

-- Default theme
INSERT INTO `theme` (`id`, `theme`) VALUES (1, 'theme-default');

-- Default admin (email: admin@123.com | password: admin123)
INSERT INTO `admin` (`name`, `email`, `password`, `image`)
VALUES ('Admin', 'admin@123.com', 'admin123', 'admin@123.com.jpg');

-- Sample employee (email: employee@123.com | password: emp123)
INSERT INTO `employee` (`emp_name`, `emp_gender`, `emp_dob`, `emp_position`, `emp_address`, `emp_mobile`, `emp_email`, `emp_password`, `emp_joining_date`, `image`, `status`)
VALUES ('John Doe', 'Male', '1995-06-15', 'Application Developer', '123 Main Street, City', '9876543210', 'employee@123.com', 'emp123', '2022-01-01', 'employee@123.com.jpg', 'Active');
