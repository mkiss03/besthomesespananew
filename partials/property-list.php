<?php
/**
 * Property List Partial
 * Megjeleníti az ingatlanok listáját kártyákkal
 * Vár egy $properties tömböt
 */

// Ha nincs megadva $properties, üres tömböt használunk
if (!isset($properties)) {
    $properties = [];
}

$totalProperties = count($properties);
?>

<!-- Results Count -->
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
    </div>
<?php else: ?>
    <div class="properties-grid">
        <?php foreach ($properties as $property): ?>
            <a href="/property?id=<?= (int)$property['id'] ?>" class="property-card" data-property-id="<?= (int)$property['id'] ?>" style="display: block; text-decoration: none;">
                <div class="property-card-image">
                    <?php
                    $imageSrc = $property['main_image'] ?? '/assets/images/properties/default.jpg';
                    if (strpos($imageSrc, 'http') === false && !file_exists(__DIR__ . '/../' . $imageSrc)) {
                        $imageSrc = 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800';
                    }
                    ?>
                    <img src="<?= e($imageSrc) ?>" alt="<?= e($property['title']) ?>" loading="lazy">
                    <?php if ($property['is_featured'] ?? false): ?>
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
<?php endif; ?>
