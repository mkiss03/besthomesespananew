<?php
require_once __DIR__ . '/../config/config.php';

// Start session for flash messages
startSession();

// Page meta data
$pageTitle = 'Főoldal';
$pageDescription = get_content('home.hero_subtitle', 'Prémium spanyol ingatlanok magyar ügyfeleknek. Villák, apartmanok, penthouse-ok a Costa Blanca és Costa del Sol régióban.');

// Get filter parameters from GET
$filterLocation = $_GET['location'] ?? '';
$filterType = $_GET['type'] ?? '';
$filterPriceMin = $_GET['price_min'] ?? '';
$filterPriceMax = $_GET['price_max'] ?? '';
$filterBedroomsMin = $_GET['bedrooms'] ?? '';
$filterPropertyId = $_GET['property_id'] ?? '';
$filterAreaMin = $_GET['area_min'] ?? '';
$filterHasPool = isset($_GET['has_pool']) ? 1 : 0;
$filterHasSeaView = isset($_GET['has_sea_view']) ? 1 : 0;

// Build WHERE clause for properties
$where = ['p.is_active = 1'];
$params = [];
$hasFilters = false;

// If there are ANY filters, show all active properties, not just featured
if ($filterLocation || $filterType || $filterPriceMin || $filterPriceMax || $filterBedroomsMin || $filterPropertyId || $filterAreaMin || $filterHasPool || $filterHasSeaView) {
    $hasFilters = true;
} else {
    // No filters = show only featured
    $where[] = 'p.is_featured = 1';
}

if ($filterPropertyId) {
    $where[] = 'p.id = ?';
    $params[] = $filterPropertyId;
}

if ($filterLocation) {
    $where[] = 'l.city = ?';
    $params[] = $filterLocation;
}

if ($filterType) {
    $where[] = 'p.property_type_id = ?';
    $params[] = $filterType;
}

if ($filterPriceMin) {
    $where[] = 'p.price >= ?';
    $params[] = $filterPriceMin;
}

if ($filterPriceMax) {
    $where[] = 'p.price <= ?';
    $params[] = $filterPriceMax;
}

if ($filterAreaMin) {
    $where[] = 'p.area_sqm >= ?';
    $params[] = $filterAreaMin;
}

if ($filterBedroomsMin) {
    $where[] = 'p.bedrooms >= ?';
    $params[] = $filterBedroomsMin;
}

if ($filterHasPool) {
    $where[] = 'p.has_pool = 1';
}

if ($filterHasSeaView) {
    $where[] = 'p.has_sea_view = 1';
}

$whereClause = implode(' AND ', $where);

// Get properties (featured or filtered)
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
        WHERE $whereClause
        ORDER BY p.is_featured DESC, p.created_at DESC
        LIMIT 12
    ");
    $stmt->execute($params);
    $featuredProperties = $stmt->fetchAll();

    $totalResults = count($featuredProperties);

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $featuredProperties = [];
    $totalResults = 0;
}

include __DIR__ . '/partials/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-background" style="background: linear-gradient(135deg, rgba(0, 71, 171, 0.85), rgba(0, 120, 215, 0.75)), url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1920') center/cover;"></div>
    <div class="hero-overlay"></div>

    <div class="hero-content">
        <h1 class="hero-title"><?= htmlspecialchars(get_content('home.hero_title', 'Találd meg álomotthonod Costa Blancán')) ?></h1>
        <p class="hero-subtitle">
            <?= htmlspecialchars(get_content('home.hero_subtitle', 'Prémium spanyol ingatlanok magyar ügyfeleknek. Villák, apartmanok, penthouse-ok a legszebb mediterrán helyszíneken.')) ?>
        </p>

        <!-- Search Form -->
        <form id="hero-search-form" action="/properties" method="GET" class="search-form">
            <div class="form-group">
                <label for="location"><?= htmlspecialchars(get_content('home.hero_search_location_label', 'Helyszín')) ?></label>
                <select name="location" id="location" class="form-control">
                    <option value="">Összes helyszín</option>
                    <?php
                    // Only show these 4 locations
                    $allowedCities = ['Benidorm', 'Alicante', 'Torrevieja', 'Calpe'];
                    try {
                        $placeholders = implode(',', array_fill(0, count($allowedCities), '?'));
                        $locationStmt = $pdo->prepare("SELECT DISTINCT city FROM locations WHERE city IN ($placeholders) ORDER BY city");
                        $locationStmt->execute($allowedCities);
                        while ($loc = $locationStmt->fetch()): ?>
                            <option value="<?= e($loc['city']) ?>"><?= e($loc['city']) ?></option>
                        <?php endwhile;
                    } catch (PDOException $e) {
                        error_log($e->getMessage());
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="type"><?= htmlspecialchars(get_content('home.hero_search_type_label', 'Ingatlantípus')) ?></label>
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
                <label for="price_min">Minimum ár (€)</label>
                <select name="price_min" id="price_min" class="form-control">
                    <option value="">Nincs minimum</option>
                    <option value="100000">100,000 €</option>
                    <option value="200000">200,000 €</option>
                    <option value="300000">300,000 €</option>
                    <option value="400000">400,000 €</option>
                    <option value="500000">500,000 €</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price_max">Maximum ár (€)</label>
                <select name="price_max" id="price_max" class="form-control">
                    <option value="">Nincs maximum</option>
                    <option value="300000">300,000 €</option>
                    <option value="500000">500,000 €</option>
                    <option value="700000">700,000 €</option>
                    <option value="1000000">1,000,000 €</option>
                    <option value="1500000">1,500,000 €</option>
                </select>
            </div>

            <div class="form-group">
                <label for="bedrooms"><?= htmlspecialchars(get_content('home.hero_search_bedrooms_label', 'Hálószobák')) ?></label>
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
                    <i class="fas fa-search"></i> <?= htmlspecialchars(get_content('home.hero_search_button', 'Keresés')) ?>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Properties Section with AJAX Filtering -->
<section class="section" id="ingatlanok-section">
    <div class="container">
        <div class="section-header text-center">
            <h2>Ingatlanjaink</h2>
            <p class="section-subtitle">
                Találja meg álmai otthonát Spanyolországban
            </p>
        </div>

        <!-- Detailed Filters (AJAX, no submit button) -->
        <?php include __DIR__ . '/partials/property-filters.php'; ?>

        <!-- Property Results Container (AJAX updates this) -->
        <div id="property-results">
            <?php
            // Initial load - show all active properties
            $properties = $featuredProperties; // Or fetch all properties
            include __DIR__ . '/partials/property-list.php';
            ?>
        </div>
    </div>
</section>

<!-- Costa Blanca Section -->
<section class="section section-alt">
    <div class="container">
        <div class="two-column-layout">
            <!-- Left Column: Text Content -->
            <div class="content-column">
                <h2><?= htmlspecialchars(get_content('home.costa_title', 'Fedezd fel Costa Blancát')) ?></h2>
                <p><?= nl2br(htmlspecialchars(get_content('home.costa_description_1', 'A Costa Blanca Spanyolország egyik legkedveltebb régiója...'))) ?></p>
                <p><?= nl2br(htmlspecialchars(get_content('home.costa_description_2', 'Ez a varázslatos régió ideális helyszín...'))) ?></p>
                <p><?= nl2br(htmlspecialchars(get_content('home.costa_description_3', 'Alicante, Benidorm, Torrevieja és Calpe...'))) ?></p>

                <!-- Feature Bulletpoints -->
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-item-icon">
                            <i class="fas <?= e(get_content('home.costa_feature_1_icon', 'fa-sun')) ?>"></i>
                        </div>
                        <div class="feature-item-content">
                            <h4><?= htmlspecialchars(get_content('home.costa_feature_1_title', '320+ napsütéses nap')) ?></h4>
                            <p><?= htmlspecialchars(get_content('home.costa_feature_1_text', 'Évente átlagosan több mint 320 napon süt a nap')) ?></p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-item-icon">
                            <i class="fas <?= e(get_content('home.costa_feature_2_icon', 'fa-water')) ?>"></i>
                        </div>
                        <div class="feature-item-content">
                            <h4><?= htmlspecialchars(get_content('home.costa_feature_2_title', 'Kristálytiszta víz')) ?></h4>
                            <p><?= htmlspecialchars(get_content('home.costa_feature_2_text', 'Zászlós díjas strandok')) ?></p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-item-icon">
                            <i class="fas <?= e(get_content('home.costa_feature_3_icon', 'fa-chart-line')) ?>"></i>
                        </div>
                        <div class="feature-item-content">
                            <h4><?= htmlspecialchars(get_content('home.costa_feature_3_title', 'Befektetési potenciál')) ?></h4>
                            <p><?= htmlspecialchars(get_content('home.costa_feature_3_text', 'Kiváló megtérülés')) ?></p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-item-icon">
                            <i class="fas <?= e(get_content('home.costa_feature_4_icon', 'fa-utensils')) ?>"></i>
                        </div>
                        <div class="feature-item-content">
                            <h4><?= htmlspecialchars(get_content('home.costa_feature_4_title', 'Mediterrán konyha')) ?></h4>
                            <p><?= htmlspecialchars(get_content('home.costa_feature_4_text', 'Friss tengeri herkentyűk')) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: 2x2 Image Grid -->
            <div class="image-grid-column">
                <div class="image-grid">
                    <div class="grid-image">
                        <img src="https://images.unsplash.com/photo-1580837119756-563d608dd119?w=600" alt="Costa Blanca beach" loading="lazy">
                    </div>
                    <div class="grid-image">
                        <img src="https://images.unsplash.com/photo-1583422409516-2895a77efded?w=600" alt="Alicante city" loading="lazy">
                    </div>
                    <div class="grid-image">
                        <img src="https://images.unsplash.com/photo-1571757767119-68b8dbed8c97?w=600" alt="Spanish villa" loading="lazy">
                    </div>
                    <div class="grid-image">
                        <img src="https://images.unsplash.com/photo-1551524164-687a55dd1126?w=600" alt="Mediterranean lifestyle" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Agent Section -->
<section class="section">
    <div class="container">
        <div class="agent-section">
            <!-- Agent Photo -->
            <div class="agent-photo">
                <img src="<?= e(get_content('home.agent_image', '/assets/images/agent-eszter.jpg')) ?>" alt="<?= e(get_content('home.agent_title', 'Balogh Eszter')) ?>">
            </div>

            <!-- Agent Content -->
            <div class="agent-content">
                <h2><?= htmlspecialchars(get_content('home.agent_title', 'Ismerd meg Balogh Esztert')) ?></h2>
                <p class="agent-subtitle"><?= htmlspecialchars(get_content('home.agent_subtitle', 'Megbízható ingatlanszakértőd Spanyolországban')) ?></p>

                <div class="agent-description">
                    <p><?= nl2br(htmlspecialchars(get_content('home.agent_description_1', 'Több mint 15 éve élek Spanyolországban...'))) ?></p>
                    <p><?= nl2br(htmlspecialchars(get_content('home.agent_description_2', 'Szakmai tapasztalatom és helyi kapcsolatrendszerem...'))) ?></p>
                    <p><?= nl2br(htmlspecialchars(get_content('home.agent_description_3', 'Célom, hogy ne csak egy ingatlant találj...'))) ?></p>
                </div>

                <!-- Agent Features -->
                <div class="agent-features">
                    <div class="agent-feature-box">
                        <i class="fas <?= e(get_content('home.agent_feature_1_icon', 'fa-certificate')) ?>"></i>
                        <h4><?= htmlspecialchars(get_content('home.agent_feature_1_title', 'Engedéllyel rendelkező szakember')) ?></h4>
                        <p><?= htmlspecialchars(get_content('home.agent_feature_1_text', 'Hivatalos spanyol ingatlanos engedély')) ?></p>
                    </div>

                    <div class="agent-feature-box">
                        <i class="fas <?= e(get_content('home.agent_feature_2_icon', 'fa-language')) ?>"></i>
                        <h4><?= htmlspecialchars(get_content('home.agent_feature_2_title', 'Többnyelvű szolgáltatás')) ?></h4>
                        <p><?= htmlspecialchars(get_content('home.agent_feature_2_text', 'Magyar, spanyol, angol és német nyelven')) ?></p>
                    </div>

                    <div class="agent-feature-box">
                        <i class="fas <?= e(get_content('home.agent_feature_3_icon', 'fa-clock')) ?>"></i>
                        <h4><?= htmlspecialchars(get_content('home.agent_feature_3_title', '24/7 elérhetőség')) ?></h4>
                        <p><?= htmlspecialchars(get_content('home.agent_feature_3_text', 'Mindig elérhető vagyok')) ?></p>
                    </div>

                    <div class="agent-feature-box">
                        <i class="fas <?= e(get_content('home.agent_feature_4_icon', 'fa-handshake')) ?>"></i>
                        <h4><?= htmlspecialchars(get_content('home.agent_feature_4_title', 'Személyes odafigyelés')) ?></h4>
                        <p><?= htmlspecialchars(get_content('home.agent_feature_4_text', 'Minden ügyféllel személyesen foglalkozom')) ?></p>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="agent-cta">
                    <a href="tel:<?= e(get_content('home.agent_phone', '+34123456789')) ?>" class="btn btn-primary">
                        <i class="fas fa-phone"></i> <?= htmlspecialchars(get_content('home.agent_phone_label', 'Hívj most')) ?>
                    </a>
                    <a href="/contact.php" class="btn btn-secondary">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars(get_content('home.agent_contact_label', 'Üzenet küldése')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="section section-alt" id="kapcsolat">
    <div class="container">
        <div class="section-header text-center">
            <h2><?= htmlspecialchars(get_content('home.contact_title', 'Vedd fel velünk a kapcsolatot')) ?></h2>
            <p class="section-subtitle">
                <?= htmlspecialchars(get_content('home.contact_subtitle', 'Válaszolunk minden kérdésedre')) ?>
            </p>
        </div>

        <div class="contact-layout">
            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <?php if (!empty($_SESSION['contact_success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= e($_SESSION['contact_success']) ?>
                    </div>
                    <?php unset($_SESSION['contact_success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['contact_errors'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            <?php foreach ($_SESSION['contact_errors'] as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['contact_errors']); ?>
                <?php endif; ?>

                <form action="/contact.php" method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name"><?= htmlspecialchars(get_content('home.contact_form_name', 'Teljes név')) ?></label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= e($_SESSION['contact_old']['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email"><?= htmlspecialchars(get_content('home.contact_form_email', 'E-mail cím')) ?></label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= e($_SESSION['contact_old']['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone"><?= htmlspecialchars(get_content('home.contact_form_phone', 'Telefonszám')) ?></label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($_SESSION['contact_old']['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="message"><?= htmlspecialchars(get_content('home.contact_form_message', 'Üzenet')) ?></label>
                        <textarea id="message" name="message" class="form-control" rows="5" required><?= e($_SESSION['contact_old']['message'] ?? '') ?></textarea>
                    </div>
                    <?php unset($_SESSION['contact_old']); ?>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> <?= htmlspecialchars(get_content('home.contact_form_submit', 'Üzenet küldése')) ?>
                    </button>
                </form>
            </div>

            <!-- Contact Info Boxes -->
            <div class="contact-info-boxes">
                <div class="contact-info-box">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4><?= htmlspecialchars(get_content('home.contact_office_title', 'Irodánk')) ?></h4>
                    <p><?= nl2br(htmlspecialchars(get_content('home.contact_office_address', 'Alicante, Spanyolország'))) ?></p>
                </div>

                <div class="contact-info-box">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h4>Telefon</h4>
                    <p>
                        <?= htmlspecialchars(get_content('home.contact_office_phone', '+34 123 456 789')) ?><br>
                        <?= htmlspecialchars(get_content('home.contact_office_phone_hu', '+36 20 123 4567')) ?>
                    </p>
                </div>

                <div class="contact-info-box">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4>E-mail</h4>
                    <p><?= htmlspecialchars(get_content('home.contact_office_email', ADMIN_EMAIL)) ?></p>
                </div>

                <div class="contact-info-box">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4><?= htmlspecialchars(get_content('home.contact_office_hours_title', 'Nyitvatartás')) ?></h4>
                    <p><?= get_content('home.contact_office_hours', 'Hétfő - Péntek: 9:00 - 18:00') ?></p>
                </div>
            </div>
        </div>

        <!-- Google Maps Embed -->
        <div class="map-container">
            <iframe
                src="<?= e(get_content('home.contact_map_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3131.234567890!2d-0.4906855!3d38.3452381!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzjCsDIwJzQyLjkiTiAwwrAyOSczMC41Ilc!5e0!3m2!1sen!2ses!4v1234567890')) ?>"
                width="100%"
                height="450"
                style="border:0;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Irodánk térképen">
            </iframe>
        </div>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
