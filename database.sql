-- =======================================================
-- Database Schema for Secure Login Authentication System
-- Database Name: authentication_system
-- =======================================================

CREATE DATABASE IF NOT EXISTS `authentication_system` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `authentication_system`;

-- Drop table if exists to ensure clean structure during migration/reinstallation
DROP TABLE IF EXISTS `users`;

-- Users Table Definition
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `salt` VARCHAR(64) NOT NULL,
    `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `otp` VARCHAR(6) DEFAULT NULL,
    `otp_expiry` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
