-- HABIT TRACKER WEB APPLICATION DATABASE SCHEMA
-- Target Database: habit_tracker
-- Compatible with MySQL / MariaDB (XAMPP)

CREATE DATABASE IF NOT EXISTS `habit_tracker` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `habit_tracker`;

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) UNIQUE NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `habits`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `habits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `frequency` ENUM('daily', 'weekly') NOT NULL DEFAULT 'daily',
  `target` INT NOT NULL DEFAULT 1,
  `start_date` DATE NOT NULL,
  `reminder_time` TIME NULL,
  `status` ENUM('active', 'paused', 'archived') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
  INDEX `idx_habits_user_id` (`user_id`),
  INDEX `idx_habits_category_id` (`category_id`),
  INDEX `idx_habits_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `habit_completions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `habit_completions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `habit_id` INT NOT NULL,
  `completion_date` DATE NOT NULL,
  `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`habit_id`) REFERENCES `habits`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_habit_completion_date` (`habit_id`, `completion_date`),
  INDEX `idx_completions_habit_id` (`habit_id`),
  INDEX `idx_completions_date` (`completion_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data for `categories`
-- --------------------------------------------------------
INSERT INTO `categories` (`name`, `description`) VALUES
('Health', 'Habits related to physical & mental health, wellness, and self-care'),
('Fitness', 'Exercise routines, workouts, cardio, and physical training'),
('Study', 'Learning, reading, academic goals, and skill acquisition'),
('Work', 'Career development, productivity, professional tasks, and projects'),
('Personal', 'Mindfulness, hobbies, relationships, and personal growth'),
('Finance', 'Budgeting, saving goals, expense tracking, and financial discipline'),
('Other', 'General daily habits and miscellaneous goals')
-- --------------------------------------------------------
-- Seed Data for Default Admin User
-- Username: admin | Password: admin123
-- --------------------------------------------------------
INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`) VALUES
('System Administrator', 'admin', 'admin@habit-tracker.local', '$2y$10$E3K6lDqD2GvgU35q7R4B5OaG4E1rB7Hj/m7S1Vq9aP1c1m0O9P.4u', 'admin', 'active')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

