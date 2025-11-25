<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Ingatlanok kezelése';

// Get filter parameters
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build WHERE clause
$where = [];
$params = [];

if ($search) {
    $where[] = '(p.title LIKE ? OR l.city LIKE ?)';
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get properties
try {
    $pdo = getPDO();

    // Count total
    $countSql = "
        SELECT COUNT(*) as total
        FROM properties p
        LEFT JOIN locations l ON p.location_id = l.id
        $whereClause
    ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalProperties = $countStmt->fetch()['total'];
    $totalPages = ceil($totalProperties / $perPage);

    // Get properties for current page
    $sql = "
        SELECT
            p.*,
            pt.name_hu as property_type_name,
            l.city,
            l.region
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        LEFT JOIN locations l ON p.location_id = l.id
        $whereClause
        ORDER BY p.created_at DESC
        LIMIT $perPage OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Properties list error: ' . $e->getMessage());
    $properties = [];
    $totalProperties = 0;
    $totalPages = 0;
}

include __DIR__ . '/partials/header.php';
?>

<div class="data-table">
    <div class="table-header">
        <h2>Ingatlanok (<?= number_format($totalProperties, 0, ',', ' ') ?> db)</h2>
        <div style="display: flex; gap: var(--spacing-sm);">
            <a href="/admin-uk0i3/property-edit.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Új ingatlan
            </a>
        </div>
    </div>

    <!-- Search -->
    <div style="padding: var(--spacing-lg); border-bottom: 1px solid #e0e0e0;">
        <form method="GET" action="/admin-uk0i3/properties.php" style="display: flex; gap: var(--spacing-sm);">
            <input
                type="text"
                name="search"
                placeholder="Keresés cím vagy város alapján..."
                value="<?= e($search) ?>"
                style="flex: 1; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: var(--radius-md);"
            >
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-search"></i> Keresés
            </button>
            <?php if ($search): ?>
                <a href="/admin-uk0i3/properties.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Törlés
                </a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($properties)): ?>
        <div style="padding: var(--spacing-xxl); text-align: center; color: var(--text-medium);">
            <i class="fas fa-building" style="font-size: 3rem; margin-bottom: var(--spacing-md);"></i>
            <p>Nincsenek ingatlanok.</p>
            <a href="/admin-uk0i3/property-edit.php" class="btn btn-primary" style="margin-top: var(--spacing-md);">
                <i class="fas fa-plus"></i> Első ingatlan hozzáadása
            </a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cím</th>
                    <th>Helyszín</th>
                    <th>Típus</th>
                    <th>Ár</th>
                    <th>Státusz</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $property): ?>
                    <tr>
                        <td><?= $property['id'] ?></td>
                        <td>
                            <strong><?= e($property['title']) ?></strong><br>
                            <small style="color: var(--text-medium);">
                                <?= $property['bedrooms'] ?> háló, <?= $property['bathrooms'] ?> fürdő, <?= formatArea($property['area_sqm']) ?>
                            </small>
                        </td>
                        <td>
                            <?= e($property['city']) ?><br>
                            <small style="color: var(--text-medium);"><?= e($property['region']) ?></small>
                        </td>
                        <td><?= e($property['property_type_name']) ?></td>
                        <td><?= formatPrice($property['price'], $property['currency']) ?></td>
                        <td>
                            <?php if ($property['is_active']): ?>
                                <span class="badge badge-success">Aktív</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inaktív</span>
                            <?php endif; ?>
                            <?php if ($property['is_featured']): ?>
                                <br><span class="badge badge-warning" style="margin-top: 0.25rem;">Kiemelt</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="/property.php?id=<?= $property['id'] ?>" target="_blank"
                                   class="btn btn-outline btn-sm" title="Megtekintés">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/admin-uk0i3/property-edit.php?id=<?= $property['id'] ?>"
                                   class="btn btn-secondary btn-sm" title="Szerkesztés">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteProperty(<?= $property['id'] ?>)"
                                        class="btn btn-outline btn-sm"
                                        style="color: var(--error-red); border-color: var(--error-red);"
                                        title="Törlés">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div style="padding: var(--spacing-lg); border-top: 1px solid #e0e0e0;">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="fas fa-chevron-left"></i> Előző
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    for ($i = $startPage; $i <= $endPage; $i++):
                        $class = ($i === $page) ? 'active' : '';
                    ?>
                        <?php if ($i === $page): ?>
                            <span class="<?= $class ?>"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            Következő <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function deleteProperty(id) {
    if (!confirm('Biztosan törölni szeretné ezt az ingatlant?')) {
        return;
    }

    fetch('/admin-uk0i3/api/properties.php?id=' + id, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ingatlan sikeresen törölve!');
            location.reload();
        } else {
            alert('Hiba: ' + (data.message || 'Ismeretlen hiba történt'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hiba történt a törlés során');
    });
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
