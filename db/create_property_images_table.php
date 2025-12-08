<?php
/**
 * Create property_images table
 * Run this script once to create the table if it doesn't exist
 */

require_once __DIR__ . '/../config/config.php';

try {
    $pdo = getPDO();

    $sql = "
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($sql);

    echo "✅ Property images table created successfully!\n";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'property_images'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table verification: property_images exists\n";

        // Show table structure
        $stmt = $pdo->query("DESCRIBE property_images");
        echo "\n📋 Table structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
