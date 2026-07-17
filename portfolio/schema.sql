-- Database Schema for Portfolio Website
-- Automatically created and populated by config/database.php on first run.

CREATE DATABASE IF NOT EXISTS `portfolio` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio`;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `image` VARCHAR(255) NULL,
    `tech_stack` VARCHAR(255) NOT NULL,
    `live_url` VARCHAR(255) NULL,
    `github_url` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Skills Table
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `level` INT NOT NULL DEFAULT 0,
    `category` VARCHAR(100) NOT NULL,
    `description` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Contact Info Table
CREATE TABLE IF NOT EXISTS `contact_info` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `location` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Social Links Table
CREATE TABLE IF NOT EXISTS `social_links` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `platform` VARCHAR(50) NOT NULL,
    `url` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Messages Table
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `setting_key` VARCHAR(50) PRIMARY KEY,
    `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Hobbies Table
CREATE TABLE IF NOT EXISTS `hobbies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(50) NULL,
    `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEED DATA (Default username: admin, password: admin123)
-- The password hash below corresponds to 'admin123'
INSERT INTO `admins` (`username`, `password`) VALUES 
('admin', '$2y$10$vO.N0s9m2VwJ7R.9jK8Piu0zD/3iQ9w3yqQ4qE6u5k2R9Lw3l7/yW')
ON DUPLICATE KEY UPDATE `username`=`username`;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('hero_name', 'ALEX RIVERS'),
('hero_title', 'FULL-STACK DEVELOPER & ARCHITECT'),
('hero_tagline', 'Building high-performance backend systems and elegant, interactive frontend interfaces.'),
('about_bio', 'I am a developer and designer specializing in custom web applications. I focus on minimal, clean layouts combined with strong backend architectures. When I am not writing code, I design user interfaces and explore modern system design methodologies.'),
('about_photo', ''),
('default_theme', 'dark'),
('site_name', 'Alex Rivers | Portfolio')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;

INSERT INTO `contact_info` (`email`, `phone`, `location`) VALUES
('alex.rivers@example.com', '+1 (555) 234-5678', 'San Francisco, CA')
ON DUPLICATE KEY UPDATE `email`=`email`;

INSERT INTO `social_links` (`platform`, `url`) VALUES
('GitHub', 'https://github.com'),
('LinkedIn', 'https://linkedin.com'),
('Twitter', 'https://twitter.com');

INSERT INTO `skills` (`name`, `level`, `category`, `description`) VALUES
('HTML5 & CSS3', 95, 'Frontend', 'Expertise in modern CSS layout techniques including Grid, Flexbox, Custom Properties, and responsive media queries.'),
('JavaScript (ES6+)', 92, 'Frontend', 'Proficient in vanilla ES6+ JS, covering asynchronous programming, custom DOM interactions, and API communication.'),
('PHP (PDO & OOP)', 88, 'Backend', 'Developing scalable backend systems utilizing Object-Oriented principles, secure database preparation, and custom session middleware.'),
('MySQL & Redis', 85, 'Backend', 'Designing optimized relational database schemas, handling complex joins, indexing, and utilizing Redis for caching.'),
('RESTful APIs', 90, 'Backend', 'Designing and consuming HTTP endpoints with secure token/session authentication and JSON responses.'),
('Figma & UI Design', 82, 'Design', 'Crafting modern UI layouts, design tokens, and components using Figma to establish clean design systems.');

INSERT INTO `projects` (`title`, `description`, `image`, `tech_stack`, `live_url`, `github_url`) VALUES
('Nebula E-Commerce', 'A high-performance e-commerce platform built with raw PHP, offering security-hardened transaction processing, a responsive glassmorphic UI, and product inventory metrics dashboards.', '', 'PHP, MySQL, Vanilla JS, CSS Custom Properties', 'https://github.com', 'https://github.com'),
('Vortex Logistics System', 'An interactive logistics tracing dashboard with visual metrics, real-time map plotting using Leaflet, and asynchronous data sync via custom background workers.', '', 'JavaScript, HTML5, CSS Grid, JSON APIs', 'https://github.com', 'https://github.com');

INSERT INTO `hobbies` (`name`, `icon`, `description`) VALUES
('Photography', 'camera', 'Capturing landscape and street photography.'),
('Gaming', 'gamepad', 'Playing strategy and RPG games.'),
('Open Source', 'code', 'Contributing to GitHub projects.');
