<?php
/**
 * Properties Search API Endpoint
 * AJAX endpoint az ingatlanok keresésé

hez
 * Visszaad egy JSON választ { "html": "..." } formátumban
 */

require_once __DIR__ . '/../../config/config.php';

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPDO();

    // Get filter parameters from GET
    $propertyId = $_GET['property_id'] ?? '';
    $location = $_GET['location'] ?? '';
    $type = $_GET['type'] ?? '';
    $priceMin = $_GET['price_min'] ?? '';
    $priceMax = $_GET['price_max'] ?? '';
    $areaMin = $_GET['area_min'] ?? '';
    $bedrooms = $_GET['bedrooms'] ?? '';
    $hasPool = isset($_GET['has_pool']) && $_GET['has_pool'] === '1' ? 1 : 0;
    $hasSeaView = isset($_GET['has_sea_view']) && $_GET['has_sea_view'] === '1' ? 1 : 0;

    // Build WHERE clause
    $where = ['p.is_active = 1'];
    $params = [];

    if ($propertyId) {
        $where[] = 'p.id = ?';
        $params[] = $propertyId;
    }

    if ($location) {
        $where[] = 'l.city = ?';
        $params[] = $location;
    }

    if ($type) {
        $where[] = 'p.property_type_id = ?';
        $params[] = $type;
    }

    if ($priceMin) {
        $where[] = 'p.price >= ?';
        $params[] = $priceMin;
    }

    if ($priceMax) {
        $where[] = 'p.price <= ?';
        $params[] = $priceMax;
    }

    if ($areaMin) {
        $where[] = 'p.area_sqm >= ?';
        $params[] = $areaMin;
    }

    if ($bedrooms) {
        $where[] = 'p.bedrooms >= ?';
        $params[] = $bedrooms;
    }

    if ($hasPool) {
        $where[] = 'p.has_pool = 1';
    }

    if ($hasSeaView) {
        $where[] = 'p.has_sea_view = 1';
    }

    $whereClause = implode(' AND ', $where);

    // Query properties
    $sql = "
        SELECT
            p.*,
            pt.name_hu as property_type_name,
            l.city,
            l.region
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE $whereClause
        ORDER BY p.is_featured DESC, p.created_at DESC
        LIMIT 50
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

    // Capture the property-list.php output
    ob_start();
    include __DIR__ . '/../partials/property-list.php';
    $html = ob_get_clean();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => count($properties)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('Properties Search API Error: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error' => 'Hiba történt a keresés során.',
        'html' => '<div class="no-results"><p>Hiba történt a keresés során. Kérjük, próbálja újra később.</p></div>'
    ], JSON_UNESCAPED_UNICODE);
}
