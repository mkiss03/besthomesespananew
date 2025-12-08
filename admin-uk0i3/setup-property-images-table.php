<?php
/**
 * Setup property_images table
 * This page creates the property_images table if it doesn't exist
 * Access: /admin-uk0i3/setup-property-images-table.php
 */

require_once __DIR__ . '/../config/config.php';
startSession();
requireAdmin();

$status = [];
$error = null;

try {
    $pdo = getPDO();

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'property_images'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        $status[] = '✅ Table already exists: property_images';
    } else {
        // Create the table
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
        $status[] = '✅ Table created successfully: property_images';
    }

    // Verify table structure
    $stmt = $pdo->query("DESCRIBE property_images");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create upload directory if doesn't exist
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
        $status[] = '✅ Upload directory created: ' . UPLOAD_PATH;
    } else {
        $status[] = '✅ Upload directory exists: ' . UPLOAD_PATH;
    }

    // Check write permissions
    if (is_writable(UPLOAD_PATH)) {
        $status[] = '✅ Upload directory is writable';
    } else {
        $status[] = '⚠️ Warning: Upload directory is not writable';
    }

} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Images Table Setup | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .setup-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-item {
            padding: 0.75rem;
            margin: 0.5rem 0;
            border-left: 4px solid #4caf50;
            background: #f1f8f4;
        }
        .error-item {
            padding: 0.75rem;
            margin: 0.5rem 0;
            border-left: 4px solid #f44336;
            background: #fef1f0;
            color: #c62828;
        }
        .table-structure {
            margin-top: 1.5rem;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: 600;
        }
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: #0078d4;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>Property Images Table Setup</h1>

        <?php if ($error): ?>
            <div class="error-item">
                <strong>❌ Error:</strong> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php foreach ($status as $message): ?>
            <div class="status-item"><?= $message ?></div>
        <?php endforeach; ?>

        <?php if (!empty($columns)): ?>
            <div class="table-structure">
                <h2>Table Structure</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($columns as $column): ?>
                            <tr>
                                <td><?= e($column['Field']) ?></td>
                                <td><?= e($column['Type']) ?></td>
                                <td><?= e($column['Null']) ?></td>
                                <td><?= e($column['Key']) ?></td>
                                <td><?= e($column['Default'] ?? 'NULL') ?></td>
                                <td><?= e($column['Extra']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <a href="/admin-uk0i3/index.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>
