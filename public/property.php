<?php
require_once __DIR__ . '/../config/config.php';

// Get property ID from URL
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$propertyId) {
    redirect('/properties.php');
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
        redirect('/properties.php');
    }

    // Page meta data
    $pageTitle = $property['meta_title'] ?? $property['title'];
    $pageDescription = $property['meta_description'] ?? strip_tags(substr($property['description'], 0, 160));

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    redirect('/properties.php');
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
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: var(--spacing-lg);
}

.property-main-image {
    width: 100%;
    height: 500px;
    object-fit: cover;
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
                    $imageSrc = $property['main_image'] ?? '/assets/images/properties/default.jpg';
                    if (strpos($imageSrc, 'http') === false && !file_exists(__DIR__ . $imageSrc)) {
                        $imageSrc = 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200';
                    }
                    ?>
                    <img src="<?= e($imageSrc) ?>" alt="<?= e($property['title']) ?>" class="property-main-image">
                </div>

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
