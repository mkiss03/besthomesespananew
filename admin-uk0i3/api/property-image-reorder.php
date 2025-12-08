<?php
/**
 * Property Image Reorder API
 * Updates sort_order for property images
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

// Get order data - expecting JSON payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['order']) || !is_array($data['order'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Érvénytelen sorrend adat']);
    exit;
}

$order = $data['order'];

if (empty($order)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Üres sorrend']);
    exit;
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // Update sort_order for each image
    $stmt = $pdo->prepare("UPDATE property_images SET sort_order = ? WHERE id = ?");

    foreach ($order as $index => $imageId) {
        $imageId = (int)$imageId;
        $sortOrder = (int)$index;

        if ($imageId > 0) {
            $stmt->execute([$sortOrder, $imageId]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'A képek sorrendje sikeresen frissítve'
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Image reorder error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba']);
}
