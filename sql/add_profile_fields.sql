-- Add profile fields to users table
-- Run this migration on the itam_system database

ALTER TABLE `users`
    ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `role`,
    ADD COLUMN `department` varchar(100) DEFAULT NULL AFTER `phone`,
    ADD COLUMN `photo_url` varchar(255) DEFAULT NULL AFTER `department`;
