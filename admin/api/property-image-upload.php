<?php
/**
 * Property Image Upload API
 * Handles multiple image uploads for properties
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

// Get property ID
$propertyId = (int)($_POST['property_id'] ?? 0);

if ($propertyId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Érvénytelen ingatlan azonosító']);
    exit;
}

// Verify property exists
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id FROM properties WHERE id = ?");
    $stmt->execute([$propertyId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Az ingatlan nem található']);
        exit;
    }
} catch (PDOException $e) {
    error_log('Property check error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba']);
    exit;
}

// Check if files were uploaded
if (empty($_FILES['images']) || !isset($_FILES['images']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nincs feltöltött fájl']);
    exit;
}

// Prepare upload directory
$uploadDir = UPLOAD_PATH;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Allowed image types
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

$uploadedImages = [];
$errors = [];

// Handle single or multiple file uploads
$files = $_FILES['images'];
$fileCount = is_array($files['tmp_name']) ? count($files['tmp_name']) : 1;

// Get current max sort_order
try {
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) as max_order FROM property_images WHERE property_id = ?");
    $stmt->execute([$propertyId]);
    $maxOrder = (int)$stmt->fetch()['max_order'];
} catch (PDOException $e) {
    error_log('Max order query error: ' . $e->getMessage());
    $maxOrder = -1;
}

for ($i = 0; $i < $fileCount; $i++) {
    // Get file info for current index
    $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
    $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
    $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
    $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];

    // Check for upload errors
    if ($fileError !== UPLOAD_ERR_OK) {
        $errors[] = "Fájl feltöltési hiba: {$fileName}";
        continue;
    }

    // Check file size (max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        $errors[] = "A fájl túl nagy (max 5MB): {$fileName}";
        continue;
    }

    // Validate mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = "Nem támogatott fájltípus: {$fileName}";
        continue;
    }

    // Validate extension
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        $errors[] = "Nem támogatott fájlkiterjesztés: {$fileName}";
        continue;
    }

    // Generate unique filename
    $uniqueFileName = uniqid('property_' . $propertyId . '_', true) . '.' . $extension;
    $destination = $uploadDir . '/' . $uniqueFileName;

    // Move uploaded file
    if (!move_uploaded_file($tmpName, $destination)) {
        $errors[] = "Fájl mentése sikertelen: {$fileName}";
        continue;
    }

    // Insert into database
    try {
        $sortOrder = ++$maxOrder;
        $stmt = $pdo->prepare("
            INSERT INTO property_images (property_id, filename, is_primary, sort_order)
            VALUES (?, ?, 0, ?)
        ");
        $stmt->execute([$propertyId, $uniqueFileName, $sortOrder]);

        $imageId = $pdo->lastInsertId();

        $uploadedImages[] = [
            'id' => $imageId,
            'filename' => $uniqueFileName,
            'url' => ASSETS_PATH . '/images/properties/' . $uniqueFileName,
            'sort_order' => $sortOrder
        ];

    } catch (PDOException $e) {
        error_log('Image insert error: ' . $e->getMessage());
        // Delete uploaded file if DB insert fails
        if (file_exists($destination)) {
            unlink($destination);
        }
        $errors[] = "Adatbázis hiba: {$fileName}";
    }
}

// Prepare response
if (empty($uploadedImages) && !empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Egyik fájl sem került feltöltésre',
        'errors' => $errors
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => count($uploadedImages) . ' kép sikeresen feltöltve',
        'images' => $uploadedImages,
        'errors' => $errors
    ]);
}
