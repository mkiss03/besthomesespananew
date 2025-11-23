<?php
require_once __DIR__ . '/../../config/config.php';
requireAdmin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $pdo = getPDO();

    if ($method === 'POST' && $id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'] ?? '';

        if (!in_array($status, ['new', 'contacted', 'closed'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Érvénytelen státusz']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        echo json_encode(['success' => true, 'message' => 'Státusz frissítve']);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nem támogatott művelet']);
    }

} catch (PDOException $e) {
    error_log('API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Szerver hiba történt']);
}
