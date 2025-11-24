<?php
require_once __DIR__ . '/../config/config.php';

// Get property ID from URL
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$propertyId) {
    redirect('/properties');
}

// Fetch property details
try {
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        SELECT
            p.*,
            pt.name_hu as property_type_name,
            l.city,
            l.region,
            l.province
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$propertyId]);
    $property = $stmt->fetch();

    if (!$property) {
        redirect('/properties');
    }

    // Fetch property images (optional - if table doesn't exist, skip)
    $propertyImages = [];
    try {
        $stmt = $pdo->prepare("
            SELECT id, filename, is_primary, sort_order
            FROM property_images
            WHERE property_id = ?
            ORDER BY is_primary DESC, sort_order ASC
        ");
        $stmt->execute([$propertyId]);
        $propertyImages = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Table doesn't exist or other error - continue without images
        error_log("Property images query failed (table may not exist): " . $e->getMessage());
    }

    // Page meta data
    $pageTitle = $property['meta_title'] ?? $property['title'];
    $pageDescription = $property['meta_description'] ?? strip_tags(substr($property['description'], 0, 160));

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    redirect('/properties');
}

// Handle inquiry form submission
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $formError = 'Kérjük, töltse ki a kötelező mezőket.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Kérjük, adjon meg érvényes email címet.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO inquiries (property_id, name, email, phone, message, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$propertyId, $name, $email, $phone, $message]);
            $formSuccess = true;

            // TODO: Send email notification
        } catch (PDOException $e) {
            error_log('Inquiry submission error: ' . $e->getMessage());
            $formError = 'Hiba történt az üzenet küldése során. Kérjük, próbálja újra később.';
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<!-- Property Detail Page -->
<section class="property-detail">
    <div class="container">
        <div class="property-header">
            <h1><?= e($property['title']) ?></h1>
            <div class="property-meta">
                <span class="property-meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= e($property['city']) ?>, <?= e($property['region']) ?>
                </span>
                <span class="property-meta-item">
                    <i class="fas fa-home"></i>
                    <?= e($property['property_type_name']) ?>
                </span>
                <?php if ($property['built_year']): ?>
                    <span class="property-meta-item">
                        <i class="fas fa-calendar"></i>
                        Építés éve: <?= e($property['built_year']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="property-main">
            <!-- Main Content -->
            <div class="property-content">
                <!-- Gallery -->
                <div class="property-gallery">
                    <?php
                    // Determine main image to display
                    $mainImageUrl = null;
                    $galleryImages = [];

                    if (!empty($propertyImages)) {
                        // Use images from property_images table
                        foreach ($propertyImages as $img) {
                            $imgUrl = ASSETS_PATH . '/images/properties/' . $img['filename'];
                            $galleryImages[] = [
                                'url' => $imgUrl,
                                'id' => $img['id']
                            ];
                        }
                        $mainImageUrl = $galleryImages[0]['url'];
                    } elseif (!empty($property['main_image'])) {
                        // Fallback to main_image field
                        $mainImageUrl = $property['main_image'];
                        $galleryImages[] = ['url' => $mainImageUrl, 'id' => 0];
                    } else {
                        // Ultimate fallback
                        $mainImageUrl = 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200';
                        $galleryImages[] = ['url' => $mainImageUrl, 'id' => 0];
                    }
                    ?>

                    <!-- Main Image -->
                    <div class="gallery-main">
                        <img src="<?= e($mainImageUrl) ?>" alt="<?= e($property['title']) ?>" class="property-main-image" id="main-image">
                    </div>

                    <!-- Thumbnail Gallery -->
                    <?php if (count($galleryImages) > 1): ?>
                        <div class="gallery-thumbnails">
                            <?php foreach ($galleryImages as $index => $img): ?>
                                <img src="<?= e($img['url']) ?>"
                                     alt="<?= e($property['title']) ?> - Kép <?= $index + 1 ?>"
                                     class="gallery-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                     data-index="<?= $index ?>"
                                     onclick="changeMainImage(<?= $index ?>)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Property Description -->
                <div class="property-description">
                    <h2>Leírás</h2>
                    <div class="description-text">
                        <?= nl2br(e($property['description'] ?? 'Nincs leírás.')) ?>
                    </div>
                </div>

                <!-- Property Features -->
                <div class="property-features">
                    <h2>Jellemzők</h2>
                    <div class="features-grid">
                        <?php if ($property['area']): ?>
                            <div class="feature-item">
                                <i class="fas fa-ruler-combined"></i>
                                <span class="feature-label">Terület</span>
                                <span class="feature-value"><?= number_format($property['area'], 0, ',', ' ') ?> m²</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($property['bedrooms']): ?>
                            <div class="feature-item">
                                <i class="fas fa-bed"></i>
                                <span class="feature-label">Hálószobák</span>
                                <span class="feature-value"><?= $property['bedrooms'] ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($property['bathrooms']): ?>
                            <div class="feature-item">
                                <i class="fas fa-bath"></i>
                                <span class="feature-label">Fürdőszobák</span>
                                <span class="feature-value"><?= $property['bathrooms'] ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($property['has_pool']): ?>
                            <div class="feature-item">
                                <i class="fas fa-swimming-pool"></i>
                                <span class="feature-label">Medence</span>
                                <span class="feature-value">Igen</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($property['has_garage']): ?>
                            <div class="feature-item">
                                <i class="fas fa-warehouse"></i>
                                <span class="feature-label">Garázs</span>
                                <span class="feature-value">Igen</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($property['has_garden']): ?>
                            <div class="feature-item">
                                <i class="fas fa-tree"></i>
                                <span class="feature-label">Kert</span>
                                <span class="feature-value">Igen</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($property['has_terrace']): ?>
                            <div class="feature-item">
                                <i class="fas fa-umbrella-beach"></i>
                                <span class="feature-label">Terasz</span>
                                <span class="feature-value">Igen</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($property['has_sea_view']): ?>
                            <div class="feature-item">
                                <i class="fas fa-water"></i>
                                <span class="feature-label">Tengerre néző</span>
                                <span class="feature-value">Igen</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="property-sidebar">
                <!-- Price Box -->
                <div class="price-box">
                    <h3>Ár</h3>
                    <div class="price">
                        <?= formatPrice($property['price'], $property['currency']) ?>
                    </div>
                    <div class="property-id">
                        <strong>Azonosító:</strong> <?= $property['id'] ?>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-box">
                    <h3>Érdeklődés</h3>

                    <?php if ($formSuccess): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Köszönjük érdeklődését! Hamarosan felvesszük Önnel a kapcsolatot.
                        </div>
                    <?php endif; ?>

                    <?php if ($formError): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= e($formError) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Név *</label>
                            <input type="text" id="name" name="name" required
                                   value="<?= e($_POST['name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?= e($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Telefon</label>
                            <input type="tel" id="phone" name="phone"
                                   value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="message">Üzenet *</label>
                            <textarea id="message" name="message" rows="4" required><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" name="submit_inquiry" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Érdeklődés küldése
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Gallery image switching
const galleryImages = <?= json_encode(array_column($galleryImages, 'url')) ?>;

function changeMainImage(index) {
    document.getElementById('main-image').src = galleryImages[index];

    // Update active thumbnail
    document.querySelectorAll('.gallery-thumbnail').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
