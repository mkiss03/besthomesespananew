<?php
require_once __DIR__ . '/../config/config.php';

// Get property ID from URL
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$propertyId) {
    redirect('properties.php');
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
        redirect('properties.php');
    }

    // Fetch property images
    $stmt = $pdo->prepare("
        SELECT id, filename, is_primary, sort_order
        FROM property_images
        WHERE property_id = ?
        ORDER BY is_primary DESC, sort_order ASC
    ");
    $stmt->execute([$propertyId]);
    $propertyImages = $stmt->fetchAll();

    // Page meta data
    $pageTitle = $property['meta_title'] ?? $property['title'];
    $pageDescription = $property['meta_description'] ?? strip_tags(substr($property['description'], 0, 160));

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    redirect('properties.php');
}

// Handle inquiry form submission
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $formError = 'Kérjük, töltse ki az összes kötelező mezőt.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Érvénytelen email cím.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO inquiries (property_id, name, email, phone, message)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$propertyId, $name, $email, $phone, $message]);
            $formSuccess = true;
        } catch (PDOException $e) {
            error_log('Inquiry error: ' . $e->getMessage());
            $formError = 'Hiba történt az üzenet elküldése során. Kérjük, próbálja újra később.';
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<style>
.property-detail {
    margin-top: 90px;
    padding: var(--spacing-xl) 0;
}

.property-header {
    margin-bottom: var(--spacing-xl);
}

.property-header h1 {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
}

.property-meta {
    display: flex;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
    color: var(--text-medium);
    font-size: 1.125rem;
}

.property-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.property-main {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
}

.property-gallery {
    margin-bottom: var(--spacing-lg);
}

.gallery-hero {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: var(--spacing-md);
    cursor: pointer;
    transition: var(--transition-fast);
}

.gallery-hero:hover {
    box-shadow: var(--shadow-lg);
}

.property-main-image {
    width: 100%;
    height: 500px;
    object-fit: cover;
    display: block;
}

.gallery-thumbnails {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: var(--spacing-sm);
}

.gallery-thumbnail {
    position: relative;
    border-radius: var(--radius-md);
    overflow: hidden;
    cursor: pointer;
    border: 3px solid transparent;
    transition: var(--transition-fast);
}

.gallery-thumbnail:hover {
    border-color: var(--primary-blue);
}

.gallery-thumbnail.active {
    border-color: var(--accent-gold);
}

.gallery-thumbnail img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    display: block;
}

.gallery-lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.gallery-lightbox.active {
    display: flex;
}

.lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.lightbox-image {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-fast);
}

.lightbox-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-fast);
}

.lightbox-nav:hover {
    background: rgba(255, 255, 255, 0.3);
}

.lightbox-nav.prev {
    left: 20px;
}

.lightbox-nav.next {
    right: 20px;
}

.property-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-md);
    padding: var(--spacing-lg);
    background: var(--sand-light);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-lg);
}

.detail-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.detail-icon {
    width: 40px;
    height: 40px;
    background: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-blue);
}

.detail-content h4 {
    font-size: 0.875rem;
    color: var(--text-medium);
    margin-bottom: 0.25rem;
}

.detail-content p {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
}

.property-description {
    line-height: 1.8;
    color: var(--text-medium);
}

.property-description h2,
.property-description h3 {
    color: var(--text-dark);
    margin-top: var(--spacing-lg);
}

.property-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.price-box {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: var(--white);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    text-align: center;
    margin-bottom: var(--spacing-lg);
}

.price-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.price-value {
    font-size: 2.5rem;
    font-weight: 700;
}

.inquiry-form {
    background: var(--white);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}

.inquiry-form h3 {
    margin-bottom: var(--spacing-md);
}

.inquiry-form .form-group {
    margin-bottom: var(--spacing-md);
}

.inquiry-form label {
    display: block;
    font-weight: 600;
    margin-bottom: var(--spacing-xs);
}

.inquiry-form input,
.inquiry-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: var(--radius-md);
    font-family: var(--font-primary);
    transition: var(--transition-fast);
}

.inquiry-form input:focus,
.inquiry-form textarea:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.inquiry-form textarea {
    resize: vertical;
    min-height: 120px;
}

.alert {
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-md);
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.features-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-sm);
    margin: var(--spacing-lg) 0;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: var(--spacing-sm);
    background: var(--sand-light);
    border-radius: var(--radius-md);
}

.feature-item i {
    color: var(--success-green);
}

@media (max-width: 968px) {
    .property-main {
        grid-template-columns: 1fr;
    }

    .property-sidebar {
        position: static;
    }

    .property-details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<article class="property-detail">
    <div class="container">
        <!-- Property Header -->
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

                    <!-- Hero Image -->
                    <div class="gallery-hero" onclick="openLightbox(0)">
                        <img src="<?= e($mainImageUrl) ?>" alt="<?= e($property['title']) ?>" class="property-main-image" id="main-image">
                    </div>

                    <!-- Thumbnail Gallery -->
                    <?php if (count($galleryImages) > 1): ?>
                        <div class="gallery-thumbnails">
                            <?php foreach ($galleryImages as $index => $img): ?>
                                <div class="gallery-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                     data-index="<?= $index ?>"
                                     onclick="changeMainImage(<?= $index ?>)">
                                    <img src="<?= e($img['url']) ?>" alt="<?= e($property['title']) ?> - Kép <?= $index + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Lightbox -->
                <div class="gallery-lightbox" id="lightbox" onclick="closeLightbox(event)">
                    <button class="lightbox-close" onclick="closeLightbox(event)">
                        <i class="fas fa-times"></i>
                    </button>
                    <button class="lightbox-nav prev" onclick="navigateLightbox(-1); event.stopPropagation();">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="lightbox-content">
                        <img src="" alt="" class="lightbox-image" id="lightbox-image">
                    </div>
                    <button class="lightbox-nav next" onclick="navigateLightbox(1); event.stopPropagation();">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <script>
                // Gallery data
                const galleryImages = <?= json_encode(array_map(function($img) { return $img['url']; }, $galleryImages)) ?>;
                let currentImageIndex = 0;
                let currentLightboxIndex = 0;

                // Change main image
                function changeMainImage(index) {
                    currentImageIndex = index;
                    document.getElementById('main-image').src = galleryImages[index];

                    // Update active thumbnail
                    document.querySelectorAll('.gallery-thumbnail').forEach((thumb, i) => {
                        thumb.classList.toggle('active', i === index);
                    });
                }

                // Open lightbox
                function openLightbox(index) {
                    currentLightboxIndex = index;
                    document.getElementById('lightbox-image').src = galleryImages[index];
                    document.getElementById('lightbox').classList.add('active');
                    document.body.style.overflow = 'hidden';
                }

                // Close lightbox
                function closeLightbox(event) {
                    if (event.target.id === 'lightbox' || event.target.closest('.lightbox-close')) {
                        document.getElementById('lightbox').classList.remove('active');
                        document.body.style.overflow = '';
                    }
                }

                // Navigate lightbox
                function navigateLightbox(direction) {
                    currentLightboxIndex += direction;
                    if (currentLightboxIndex < 0) {
                        currentLightboxIndex = galleryImages.length - 1;
                    } else if (currentLightboxIndex >= galleryImages.length) {
                        currentLightboxIndex = 0;
                    }
                    document.getElementById('lightbox-image').src = galleryImages[currentLightboxIndex];
                }

                // Keyboard navigation
                document.addEventListener('keydown', function(e) {
                    const lightbox = document.getElementById('lightbox');
                    if (lightbox.classList.contains('active')) {
                        if (e.key === 'Escape') {
                            lightbox.classList.remove('active');
                            document.body.style.overflow = '';
                        } else if (e.key === 'ArrowLeft') {
                            navigateLightbox(-1);
                        } else if (e.key === 'ArrowRight') {
                            navigateLightbox(1);
                        }
                    }
                });
                </script>

                <!-- Details Grid -->
                <div class="property-details-grid">
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Hálószobák</h4>
                            <p><?= $property['bedrooms'] ?> db</p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-bath"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Fürdőszobák</h4>
                            <p><?= $property['bathrooms'] ?> db</p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-ruler-combined"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Alapterület</h4>
                            <p><?= formatArea($property['area_sqm']) ?></p>
                        </div>
                    </div>

                    <?php if ($property['plot_size_sqm']): ?>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-tree"></i>
                            </div>
                            <div class="detail-content">
                                <h4>Telekméret</h4>
                                <p><?= formatArea($property['plot_size_sqm']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($property['distance_to_beach_m']): ?>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-umbrella-beach"></i>
                            </div>
                            <div class="detail-content">
                                <h4>Távolság a strandtól</h4>
                                <p><?= number_format($property['distance_to_beach_m'], 0, ',', ' ') ?> m</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Features -->
                <?php
                $features = [];
                if ($property['has_pool']) $features[] = ['icon' => 'swimming-pool', 'text' => 'Medence'];
                if ($property['has_garden']) $features[] = ['icon' => 'leaf', 'text' => 'Kert'];
                if ($property['has_terrace']) $features[] = ['icon' => 'home', 'text' => 'Terasz'];
                if ($property['has_garage']) $features[] = ['icon' => 'car', 'text' => 'Garázs'];
                if ($property['has_sea_view']) $features[] = ['icon' => 'water', 'text' => 'Tengerre néző'];

                if (!empty($features)):
                ?>
                    <h3>Főbb jellemzők</h3>
                    <div class="features-list">
                        <?php foreach ($features as $feature): ?>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span><?= $feature['text'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="property-description">
                    <h3>Részletes leírás</h3>
                    <?= $property['description'] ?>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="property-sidebar">
                <!-- Price Box -->
                <div class="price-box">
                    <div class="price-label">Ár</div>
                    <div class="price-value"><?= formatPrice($property['price'], $property['currency']) ?></div>
                </div>

                <!-- Inquiry Form -->
                <div class="inquiry-form">
                    <h3>Érdeklődöm az ingatlanról</h3>

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
                            <textarea id="message" name="message" required><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" name="submit_inquiry" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Üzenet küldése
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</article>

<?php include __DIR__ . '/partials/footer.php'; ?>
