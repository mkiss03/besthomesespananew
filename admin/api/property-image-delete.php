<?php
/**
 * Property Image Delete API
 * Deletes image file and database record
 */
require_once __DIR__ . '/../../config/config.php';
startSession();
requireAdmin();

header('Content-Type: application/json');

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get image ID
$imageId = (int)($_POST['image_id'] ?? 0);

if ($imageId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Érvénytelen kép azonosító']);
    exit;
}

try {
    $pdo = getPDO();

    // Fetch image record
    $stmt = $pdo->prepare("SELECT id, filename FROM property_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch();

    if (!$image) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'A kép nem található']);
        exit;
    }

    // Delete file from filesystem if exists
    $filePath = UPLOAD_PATH . '/' . $image['filename'];
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            error_log('Failed to delete image file: ' . $filePath);
            // Continue anyway to remove DB record
        }
    }

    // Delete database record
    $stmt = $pdo->prepare("DELETE FROM property_images WHERE id = ?");
    $stmt->execute([$imageId]);

    echo json_encode([
        'success' => true,
        'message' => 'A kép sikeresen törölve'
    ]);

} catch (PDOException $e) {
    error_log('Image delete error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba']);
}
