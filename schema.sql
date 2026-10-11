-- إنشاء قاعدة البيانات إذا لم تكن موجودة
CREATE DATABASE IF NOT EXISTS `user_management_db` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `user_management_db`;

-- جدول المستخدمين
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة مستخدم افتراضي بصلاحية مدير (كلمة المرور: admin123)
-- كلمة المرور مشفرة بواسطة BCRYPT
INSERT INTO `users` (`username`, `full_name`, `email`, `password`, `role`) 
VALUES (
    'admin', 
    'المدير العام', 
    'admin@example.com', 
    '$2y$10$wZk0J3H/iKovs7hHk1xQ9evk5m3gN4k5O7jF3qD8l3k2m1n0o9p8q', -- كلمة المرور admin123
    'admin'
) ON DUPLICATE KEY UPDATE `username`=`username`;
