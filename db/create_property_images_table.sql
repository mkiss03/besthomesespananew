-- Create property_images table if it doesn't exist
-- This table stores multiple images for each property

CREATE TABLE IF NOT EXISTS property_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
