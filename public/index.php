<?php
require_once __DIR__ . '/../config/config.php';

// Start session for flash messages
startSession();

// Page meta data
$pageTitle = 'Főoldal';
$pageDescription = get_content('home.hero_subtitle', 'Prémium spanyol ingatlanok magyar ügyfeleknek. Villák, apartmanok, penthouse-ok a Costa Blanca és Costa del Sol régióban.');

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
    <div class="hero-background" style="background: linear-gradient(135deg, rgba(0, 71, 171, 0.85), rgba(0, 120, 215, 0.75)), url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1920') center/cover;"></div>
    <div class="hero-overlay"></div>

    <div class="hero-content">
        <h1 class="hero-title"><?= htmlspecialchars(get_content('home.hero_title', 'Találd meg álomotthonod Costa Blancán')) ?></h1>
        <p class="hero-subtitle">
            <?= htmlspecialchars(get_content('home.hero_subtitle', 'Prémium spanyol ingatlanok magyar ügyfeleknek. Villák, apartmanok, penthouse-ok a legszebb mediterrán helyszíneken.')) ?>
        </p>

        <!-- Search Form -->
        <form action="/properties.php" method="GET" class="search-form">
            <div class="form-group">
                <label for="location"><?= htmlspecialchars(get_content('home.hero_search_location_label', 'Helyszín')) ?></label>
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
                <label for="price_max"><?= htmlspecialchars(get_content('home.hero_search_price_label', 'Maximum ár')) ?> (€)</label>
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

<!-- Featured Properties Section -->
<section class="section">
    <div class="container">
        <div class="section-header text-center">
            <h2><?= htmlspecialchars(get_content('home.featured_title', 'Kiemelt Ingatlanok')) ?></h2>
            <p class="section-subtitle">
                <?= htmlspecialchars(get_content('home.featured_subtitle', 'Válogatott ajánlataink a legjobb spanyol ingatlanokból')) ?>
            </p>
        </div>

        <!-- Detailed Filter Row (Static for now, functionality to be added later) -->
        <div class="filter-bar">
            <div class="filter-group">
                <label><?= htmlspecialchars(get_content('home.featured_filter_id', 'Azonosító')) ?></label>
                <input type="text" class="filter-input" placeholder="ID...">
            </div>
            <div class="filter-group">
                <label><?= htmlspecialchars(get_content('home.featured_filter_area', 'Alapterület')) ?></label>
                <input type="text" class="filter-input" placeholder="m²...">
            </div>
            <div class="filter-group">
                <label><?= htmlspecialchars(get_content('home.featured_filter_rooms', 'Szobaszám')) ?></label>
                <input type="number" class="filter-input" placeholder="0">
            </div>
            <div class="filter-group">
                <label>
                    <input type="checkbox" class="filter-checkbox">
                    <?= htmlspecialchars(get_content('home.featured_filter_pool', 'Medence')) ?>
                </label>
            </div>
            <div class="filter-group">
                <label>
                    <input type="checkbox" class="filter-checkbox">
                    <?= htmlspecialchars(get_content('home.featured_filter_seaview', 'Tengerre néző')) ?>
                </label>
            </div>
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
                    <?= htmlspecialchars(get_content('home.featured_load_more', 'Összes ingatlan megtekintése')) ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
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
