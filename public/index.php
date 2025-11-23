<?php
require_once __DIR__ . '/../config/config.php';

// Page meta data
$pageTitle = 'Főoldal';
$pageDescription = 'Prémium spanyol ingatlanok magyar ügyfeleknek. Villák, apartmanok, penthouse-ok a Costa Blanca és Costa del Sol régióban.';

// Get featured properties
try {
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        SELECT
            p.*,
            pt.name_hu as property_type_name,
            l.city,
            l.region
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.is_featured = 1 AND p.is_active = 1
        ORDER BY p.created_at DESC
        LIMIT 6
    ");
    $stmt->execute();
    $featuredProperties = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $featuredProperties = [];
}

include __DIR__ . '/partials/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <!-- Background image - will be added via CSS or inline style -->
    <div class="hero-background" style="background: url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1920') center/cover;"></div>
    <div class="hero-overlay"></div>

    <div class="hero-content">
        <h1 class="hero-title">Prémium Spanyol Ingatlanok Magyar Ügyfeleknek</h1>
        <p class="hero-subtitle">
            Találja meg álmai otthonát a Costa Blanca és Costa del Sol gyönyörű régióiban
        </p>

        <!-- Search Form -->
        <form action="/properties.php" method="GET" class="search-form">
            <div class="form-group">
                <label for="location">Helyszín</label>
                <select name="location" id="location" class="form-control">
                    <option value="">Összes helyszín</option>
                    <?php
                    try {
                        $locationStmt = $pdo->query("SELECT DISTINCT city, region FROM locations ORDER BY city");
                        while ($loc = $locationStmt->fetch()): ?>
                            <option value="<?= e($loc['city']) ?>"><?= e($loc['city']) ?> (<?= e($loc['region']) ?>)</option>
                        <?php endwhile;
                    } catch (PDOException $e) {
                        error_log($e->getMessage());
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="type">Típus</label>
                <select name="type" id="type" class="form-control">
                    <option value="">Minden típus</option>
                    <?php
                    try {
                        $typeStmt = $pdo->query("SELECT id, name_hu FROM property_types ORDER BY name_hu");
                        while ($type = $typeStmt->fetch()): ?>
                            <option value="<?= e($type['id']) ?>"><?= e($type['name_hu']) ?></option>
                        <?php endwhile;
                    } catch (PDOException $e) {
                        error_log($e->getMessage());
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price_max">Max. ár (€)</label>
                <select name="price_max" id="price_max" class="form-control">
                    <option value="">Bármennyi</option>
                    <option value="200000">200,000 €</option>
                    <option value="400000">400,000 €</option>
                    <option value="600000">600,000 €</option>
                    <option value="800000">800,000 €</option>
                    <option value="1000000">1,000,000 €</option>
                </select>
            </div>

            <div class="form-group">
                <label for="bedrooms">Hálószobák</label>
                <select name="bedrooms" id="bedrooms" class="form-control">
                    <option value="">Bármennyi</option>
                    <option value="1">1+</option>
                    <option value="2">2+</option>
                    <option value="3">3+</option>
                    <option value="4">4+</option>
                </select>
            </div>

            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-search"></i> Keresés
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Featured Properties Section -->
<section class="section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Kiemelt Ingatlanok</h2>
            <p style="color: var(--text-medium); font-size: 1.125rem;">
                Válogatott ajánlataink a legjobb spanyol ingatlanokból
            </p>
        </div>

        <?php if (empty($featuredProperties)): ?>
            <p class="text-center">Jelenleg nincsenek kiemelt ingatlanok.</p>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($featuredProperties as $property): ?>
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

            <div class="text-center mt-5">
                <a href="/properties.php" class="btn btn-secondary btn-lg">
                    Összes ingatlan megtekintése <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section section-alt">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Miért Válasszon Minket?</h2>
            <p style="color: var(--text-medium); font-size: 1.125rem;">
                10+ év tapasztalat a spanyol ingatlanpiacon
            </p>
        </div>

        <div class="features">
            <div class="feature-box">
                <div class="feature-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="feature-title">Megbízható Partnerség</h3>
                <p class="feature-description">
                    Magyar nyelvű ügyintézés, teljes körű támogatás a vásárlástól az ügyintézésig.
                </p>
            </div>

            <div class="feature-box">
                <div class="feature-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="feature-title">Prémium Ingatlanok</h3>
                <p class="feature-description">
                    Csak a legmagasabb minőségű, gondosan kiválasztott ingatlanok portfóliónkban.
                </p>
            </div>

            <div class="feature-box">
                <div class="feature-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h3 class="feature-title">Jogi Segítség</h3>
                <p class="feature-description">
                    Tapasztalt jogi csapatunk végigkíséri a teljes vásárlási folyamatot.
                </p>
            </div>

            <div class="feature-box">
                <div class="feature-icon">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <h3 class="feature-title">Finanszírozás</h3>
                <p class="feature-description">
                    Segítünk a legjobb finanszírozási lehetőségek megtalálásában.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">Készen Áll Megtalálni Álmai Otthonát?</h2>
        <p class="cta-subtitle">
            Vegye fel velünk a kapcsolatot még ma, és kezdje el spanyolországi kalandját!
        </p>
        <a href="/contact.php" class="btn btn-primary btn-lg">
            <i class="fas fa-phone"></i> Kapcsolatfelvétel
        </a>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
