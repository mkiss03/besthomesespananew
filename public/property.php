<?php
require_once __DIR__ . '/../config/config.php';

// SUPER DEBUG MODE - Show everything before any redirect
echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>DEBUG</title></head><body style='padding:40px;font-family:Arial;background:#f0f0f0;'>";
echo "<div style='background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);'>";
echo "<h1 style='color:#e74c3c;'>🔍 PROPERTY DEBUG MODE</h1>";
echo "<hr>";
echo "<h2>1. URL Information:</h2>";
echo "<p><strong>Teljes URL:</strong> <code>" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</code></p>";
echo "<p><strong>GET paraméterek:</strong></p><pre style='background:#f8f8f8;padding:15px;border-radius:5px;'>" . print_r($_GET, true) . "</pre>";

// Get property ID from URL
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

echo "<h2>2. Property ID Ellenőrzés:</h2>";
echo "<p><strong>Talált Property ID:</strong> <span style='font-size:24px;color:" . ($propertyId ? "green" : "red") . ";'>" . ($propertyId ?: 'NINCS') . "</span></p>";

if (!$propertyId) {
    echo "<div style='background:#ffe6e6;padding:20px;border-left:4px solid #e74c3c;margin:20px 0;'>";
    echo "<h3 style='color:#e74c3c;margin-top:0;'>❌ HIBA: Nincs property ID a URL-ben!</h3>";
    echo "<p><strong>Ok:</strong> Az ingatlan linkben nincs ?id=X paraméter</p>";
    echo "<p><strong>Példa helyes link:</strong> <code>/property?id=1</code></p>";
    echo "</div>";
    echo "<p><a href='/' style='background:#3498db;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>← Vissza a főoldalra</a></p>";
    echo "</div></body></html>";
    exit;
}

echo "<div style='background:#e6ffe6;padding:20px;border-left:4px solid #27ae60;margin:20px 0;'>";
echo "<h3 style='color:#27ae60;margin-top:0;'>✅ Property ID rendben!</h3>";
echo "<p>Folytatás az ingatlan adatainak betöltésével...</p>";
echo "</div>";

echo "<h2>3. Adatbázis Lekérdezés:</h2>";

// Fetch property details
try {
    $pdo = getPDO();
    echo "<p style='color:green;'>✅ Adatbázis kapcsolat OK</p>";

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

    echo "<p><strong>SQL lekérdezés lefutott</strong></p>";

    if (!$property) {
        echo "<div style='background:#ffe6e6;padding:20px;border-left:4px solid #e74c3c;margin:20px 0;'>";
        echo "<h3 style='color:#e74c3c;margin-top:0;'>❌ HIBA: Az ingatlan nem található!</h3>";
        echo "<p><strong>Property ID:</strong> $propertyId</p>";
        echo "<p><strong>Lehetséges okok:</strong></p>";
        echo "<ul>";
        echo "<li>Az ingatlan nem létezik az adatbázisban</li>";
        echo "<li>Az ingatlan nincs aktív állapotban (is_active = 0)</li>";
        echo "</ul>";

        // Check if property exists at all (ignoring is_active)
        $checkStmt = $pdo->prepare("SELECT id, is_active FROM properties WHERE id = ?");
        $checkStmt->execute([$propertyId]);
        $check = $checkStmt->fetch();

        if ($check) {
            echo "<p><strong>Extra info:</strong> Az ingatlan létezik az adatbázisban (ID: {$check['id']}), de is_active = {$check['is_active']}</p>";
        } else {
            echo "<p><strong>Extra info:</strong> Az ingatlan egyáltalán nem létezik az adatbázisban ezzel az ID-vel</p>";
        }

        echo "</div>";
        echo "<p><a href='/' style='background:#3498db;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>← Vissza a főoldalra</a></p>";
        echo "</div></body></html>";
        exit;
    }

    echo "<div style='background:#e6ffe6;padding:20px;border-left:4px solid #27ae60;margin:20px 0;'>";
    echo "<h3 style='color:#27ae60;margin-top:0;'>✅ Ingatlan megtalálva!</h3>";
    echo "<p><strong>Cím:</strong> " . htmlspecialchars($property['title']) . "</p>";
    echo "<p><strong>Helyszín:</strong> " . htmlspecialchars($property['city'] ?? 'N/A') . "</p>";
    echo "<p><strong>Ár:</strong> " . number_format($property['price'], 0, ',', ' ') . " " . $property['currency'] . "</p>";
    echo "</div>";

    error_log("Property found: " . $property['title'] . " (ID: " . $propertyId . ")");

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
        echo "<p style='color:green;'>✅ Képek betöltve: " . count($propertyImages) . " db</p>";
    } catch (PDOException $e) {
        // Table doesn't exist or other error - continue without images
        echo "<p style='color:orange;'>⚠️ property_images tábla nem létezik - folytatás main_image-dzsel</p>";
        error_log("Property images query failed (table may not exist): " . $e->getMessage());
    }

    // Page meta data
    $pageTitle = $property['meta_title'] ?? $property['title'];
    $pageDescription = $property['meta_description'] ?? strip_tags(substr($property['description'], 0, 160));

    // DEBUG: If we got here, everything works!
    echo "<h2>4. ✅ TESZT SIKERES!</h2>";
    echo "<div style='background:#d4edda;padding:20px;border-left:4px solid #28a745;margin:20px 0;'>";
    echo "<h3 style='color:#155724;margin-top:0;'>🎉 Minden rendben!</h3>";
    echo "<p><strong>Az ingatlan oldal betöltése sikeres lenne!</strong></p>";
    echo "<p>Az összes adat megvan:</p>";
    echo "<ul>";
    echo "<li>✅ Property ID: $propertyId</li>";
    echo "<li>✅ Ingatlan adatok: Megtalálva</li>";
    echo "<li>✅ Képek száma: " . count($propertyImages) . "</li>";
    echo "<li>✅ Meta adatok: Rendben</li>";
    echo "</ul>";
    echo "<p style='margin-top:20px;'><strong>Most már eltávolíthatjuk a debug kódot és betölthetjük a valódi ingatlan oldalt!</strong></p>";
    echo "</div>";
    echo "</div></body></html>";
    exit;

} catch (PDOException $e) {
    echo "<div style='background:#ffe6e6;padding:20px;border-left:4px solid #e74c3c;margin:20px 0;'>";
    echo "<h3 style='color:#e74c3c;margin-top:0;'>❌ Adatbázis HIBA!</h3>";
    echo "<p><strong>Hibaüzenet:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    echo "</div></body></html>";
    exit;
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
