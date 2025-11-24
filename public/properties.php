<?php
require_once __DIR__ . '/../config/config.php';

// Page meta data
$pageTitle = 'Ingatlanok';
$pageDescription = 'Böngésszen prémium spanyol ingatlanjaink között. Villák, apartmanok, penthouse-ok a Costa Blanca és Costa del Sol régióban.';

// Get filter parameters
$location = $_GET['location'] ?? '';
$type = $_GET['type'] ?? '';
$priceMin = $_GET['price_min'] ?? '';
$priceMax = $_GET['price_max'] ?? '';
$bedrooms = $_GET['bedrooms'] ?? '';
$propertyId = $_GET['property_id'] ?? '';
$areaMin = $_GET['area_min'] ?? '';
$hasPool = isset($_GET['has_pool']) ? 1 : 0;
$hasSeaView = isset($_GET['has_sea_view']) ? 1 : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

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

// Get properties
try {
    $pdo = getPDO();

    // Count total properties
    $countSql = "
        SELECT COUNT(*) as total
        FROM properties p
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE $whereClause
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
        WHERE $whereClause
        ORDER BY p.is_featured DESC, p.created_at DESC
        LIMIT $perPage OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $properties = [];
    $totalProperties = 0;
    $totalPages = 0;
}

include __DIR__ . '/partials/header.php';
?>

<style>
.properties-page {
    margin-top: 90px;
    padding: var(--spacing-xl) 0;
}

.page-header {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: var(--white);
    padding: var(--spacing-xl) 0;
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.page-header h1 {
    color: var(--white);
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
}

.filters {
    background: var(--white);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: var(--spacing-xl);
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.results-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.results-count {
    font-size: 1.125rem;
    color: var(--text-medium);
}

.pagination {
    display: flex;
    justify-content: center;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-xl);
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    padding: 0.5rem 1rem;
    border: 2px solid var(--primary-blue);
    border-radius: var(--radius-md);
    color: var(--primary-blue);
    text-decoration: none;
    transition: var(--transition-fast);
}

.pagination a:hover {
    background: var(--primary-blue);
    color: var(--white);
}

.pagination .active {
    background: var(--primary-blue);
    color: var(--white);
}

.pagination .disabled {
    opacity: 0.5;
    cursor: not-allowed;
    border-color: var(--text-light);
    color: var(--text-light);
}

.no-results {
    text-align: center;
    padding: var(--spacing-xxl) 0;
}

.no-results i {
    font-size: 4rem;
    color: var(--text-light);
    margin-bottom: var(--spacing-md);
}
</style>

<div class="properties-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Ingatlanjaink</h1>
            <p>Találja meg álmai otthonát Spanyolországban</p>
        </div>
    </div>

    <div class="container">
        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="/properties.php">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="property_id">Azonosító</label>
                        <input type="text" name="property_id" id="property_id" class="form-control" placeholder="Ingatlan ID..." value="<?= e($propertyId) ?>">
                    </div>

                    <div class="form-group">
                        <label for="location">Helyszín</label>
                        <select name="location" id="location" class="form-control">
                            <option value="">Összes helyszín</option>
                            <?php
                            $allowedCities = ['Benidorm', 'Alicante', 'Torrevieja', 'Calpe'];
                            foreach ($allowedCities as $city):
                                $selected = ($city === $location) ? 'selected' : '';
                            ?>
                                <option value="<?= e($city) ?>" <?= $selected ?>><?= e($city) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="type">Típus</label>
                        <select name="type" id="type" class="form-control">
                            <option value="">Minden típus</option>
                            <?php
                            try {
                                $typeStmt = $pdo->query("SELECT id, name_hu FROM property_types ORDER BY name_hu");
                                while ($t = $typeStmt->fetch()):
                                    $selected = ($t['id'] == $type) ? 'selected' : '';
                                ?>
                                    <option value="<?= e($t['id']) ?>" <?= $selected ?>>
                                        <?= e($t['name_hu']) ?>
                                    </option>
                                <?php endwhile;
                            } catch (PDOException $e) {
                                error_log($e->getMessage());
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price_min">Min. ár (€)</label>
                        <input type="number" name="price_min" id="price_min" class="form-control" placeholder="0" value="<?= e($priceMin) ?>">
                    </div>

                    <div class="form-group">
                        <label for="price_max">Max. ár (€)</label>
                        <input type="number" name="price_max" id="price_max" class="form-control" placeholder="0" value="<?= e($priceMax) ?>">
                    </div>

                    <div class="form-group">
                        <label for="area_min">Min. alapterület (m²)</label>
                        <input type="number" name="area_min" id="area_min" class="form-control" placeholder="0" value="<?= e($areaMin) ?>">
                    </div>

                    <div class="form-group">
                        <label for="bedrooms">Min. hálószobák</label>
                        <input type="number" name="bedrooms" id="bedrooms" class="form-control" placeholder="0" value="<?= e($bedrooms) ?>">
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 1rem; padding-top: 1.8rem;">
                        <label style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="has_pool" <?= $hasPool ? 'checked' : '' ?>>
                            Medence
                        </label>
                        <label style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="has_sea_view" <?= $hasSeaView ? 'checked' : '' ?>>
                            Tengerre néző
                        </label>
                    </div>

                    <div class="form-group" style="align-self: flex-end;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Keresés
                        </button>
                    </div>

                    <?php if ($location || $type || $priceMin || $priceMax || $bedrooms || $propertyId || $areaMin || $hasPool || $hasSeaView): ?>
                        <div class="form-group" style="align-self: flex-end;">
                            <a href="/properties.php" class="btn btn-outline btn-block">
                                <i class="fas fa-times"></i> Szűrők törlése
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="results-info">
            <div class="results-count">
                <strong><?= number_format($totalProperties, 0, ',', ' ') ?></strong> ingatlan található
            </div>
        </div>

        <!-- Properties Grid -->
        <?php if (empty($properties)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h2>Nem találtunk ingatlant a megadott feltételekkel</h2>
                <p>Próbálja meg módosítani a keresési feltételeket.</p>
                <a href="/properties.php" class="btn btn-primary mt-3">
                    Összes ingatlan megtekintése
                </a>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($properties as $property): ?>
                    <a href="/property.php?id=<?= $property['id'] ?>" class="property-card">
                        <div class="property-card-image">
                            <?php
                            $imageSrc = $property['main_image'] ?? '/assets/images/properties/default.jpg';
                            if (strpos($imageSrc, 'http') === false && !file_exists(__DIR__ . $imageSrc)) {
                                $imageSrc = 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800';
                            }
                            ?>
                            <img src="<?= e($imageSrc) ?>" alt="<?= e($property['title']) ?>" loading="lazy">
                            <?php if ($property['is_featured']): ?>
                                <span class="property-badge">Kiemelt</span>
                            <?php endif; ?>
                        </div>

                        <div class="property-card-content">
                            <div class="property-price">
                                <?= formatPrice($property['price'], $property['currency']) ?>
                            </div>

                            <h3 class="property-title"><?= e($property['title']) ?></h3>

                            <p class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= e($property['city']) ?>, <?= e($property['region']) ?>
                            </p>

                            <div class="property-features">
                                <span class="property-feature">
                                    <i class="fas fa-bed property-feature-icon"></i>
                                    <?= $property['bedrooms'] ?> háló
                                </span>
                                <span class="property-feature">
                                    <i class="fas fa-bath property-feature-icon"></i>
                                    <?= $property['bathrooms'] ?> fürdő
                                </span>
                                <span class="property-feature">
                                    <i class="fas fa-ruler-combined property-feature-icon"></i>
                                    <?= formatArea($property['area_sqm']) ?>
                                </span>
                                <?php if ($property['has_pool']): ?>
                                    <span class="property-feature">
                                        <i class="fas fa-swimming-pool property-feature-icon"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
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
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= $class ?>">
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
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
