-- Fix Contact Inquiries Table
-- Run this in phpMyAdmin if the contact form is not working

-- Drop existing table if it exists (CAUTION: This will delete existing data!)
DROP TABLE IF EXISTS contact_inquiries;

-- Create the correct table structure
CREATE TABLE contact_inquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    message TEXT NOT NULL,
    source_page VARCHAR(191) DEFAULT NULL COMMENT 'Which page the inquiry came from',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_created_at (created_at),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
