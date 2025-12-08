<?php
require_once __DIR__ . '/../../config/config.php';
requireAdmin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $pdo = getPDO();

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get single property
                $stmt = $pdo->prepare("
                    SELECT p.*, pt.name_hu as property_type_name, l.city, l.region
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    LEFT JOIN locations l ON p.location_id = l.id
                    WHERE p.id = ?
                ");
                $stmt->execute([$id]);
                $property = $stmt->fetch();

                if ($property) {
                    echo json_encode(['success' => true, 'data' => $property]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Ingatlan nem található']);
                }
            } else {
                // Get all properties
                $stmt = $pdo->query("
                    SELECT p.*, pt.name_hu as property_type_name, l.city, l.region
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    LEFT JOIN locations l ON p.location_id = l.id
                    ORDER BY p.created_at DESC
                ");
                $properties = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $properties]);
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID megadása kötelező']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Ingatlan sikeresen törölve']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Ingatlan nem található']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Nem támogatott művelet']);
            break;
    }

} catch (PDOException $e) {
    error_log('API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Szerver hiba történt']);
}
