<?php
require_once __DIR__ . '/../config/config.php';

$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $propertyId > 0;

$pageTitle = $isEdit ? 'Ingatlan szerkesztése' : 'Új ingatlan';

// Initialize form data
$formData = [
    'title' => '',
    'description' => '',
    'property_type_id' => '',
    'location_id' => '',
    'price' => '',
    'currency' => 'EUR',
    'area_sqm' => '',
    'plot_size_sqm' => '',
    'bedrooms' => '',
    'bathrooms' => '',
    'built_year' => '',
    'has_pool' => false,
    'has_garden' => false,
    'has_terrace' => false,
    'has_garage' => false,
    'has_sea_view' => false,
    'distance_to_beach_m' => '',
    'main_image' => '',
    'is_featured' => false,
    'is_active' => true,
    'status' => 'available'
];

$error = '';
$success = '';

try {
    $pdo = getPDO();

    // Load property data if editing
    if ($isEdit) {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
        $property = $stmt->fetch();

        if (!$property) {
            redirect('/admin/properties.php');
        }

        $formData = array_merge($formData, $property);
    }

    // Get property types and locations
    $propertyTypes = $pdo->query("SELECT * FROM property_types ORDER BY name_hu")->fetchAll();
    $locations = $pdo->query("SELECT * FROM locations ORDER BY city")->fetchAll();

} catch (PDOException $e) {
    error_log('Property edit error: ' . $e->getMessage());
    $error = 'Hiba történt az adatok betöltése során.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_property'])) {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $property_type_id = (int)($_POST['property_type_id'] ?? 0);
    $location_id = (int)($_POST['location_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $currency = $_POST['currency'] ?? 'EUR';
    $area_sqm = (int)($_POST['area_sqm'] ?? 0);
    $plot_size_sqm = !empty($_POST['plot_size_sqm']) ? (int)$_POST['plot_size_sqm'] : null;
    $bedrooms = (int)($_POST['bedrooms'] ?? 0);
    $bathrooms = (int)($_POST['bathrooms'] ?? 0);
    $built_year = !empty($_POST['built_year']) ? (int)$_POST['built_year'] : null;
    $has_pool = isset($_POST['has_pool']) ? 1 : 0;
    $has_garden = isset($_POST['has_garden']) ? 1 : 0;
    $has_terrace = isset($_POST['has_terrace']) ? 1 : 0;
    $has_garage = isset($_POST['has_garage']) ? 1 : 0;
    $has_sea_view = isset($_POST['has_sea_view']) ? 1 : 0;
    $distance_to_beach_m = !empty($_POST['distance_to_beach_m']) ? (int)$_POST['distance_to_beach_m'] : null;
    $main_image = trim($_POST['main_image'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $status = $_POST['status'] ?? 'available';

    // Validation
    if (empty($title) || empty($description) || !$property_type_id || !$location_id || !$price || !$area_sqm || !$bedrooms || !$bathrooms) {
        $error = 'Kérjük, töltse ki az összes kötelező mezőt.';
    } else {
        try {
            $slug = generateSlug($title);

            if ($isEdit) {
                // Update existing property
                $stmt = $pdo->prepare("
                    UPDATE properties SET
                        title = ?,
                        slug = ?,
                        description = ?,
                        property_type_id = ?,
                        location_id = ?,
                        price = ?,
                        currency = ?,
                        area_sqm = ?,
                        plot_size_sqm = ?,
                        bedrooms = ?,
                        bathrooms = ?,
                        built_year = ?,
                        has_pool = ?,
                        has_garden = ?,
                        has_terrace = ?,
                        has_garage = ?,
                        has_sea_view = ?,
                        distance_to_beach_m = ?,
                        main_image = ?,
                        is_featured = ?,
                        is_active = ?,
                        status = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title, $slug, $description, $property_type_id, $location_id,
                    $price, $currency, $area_sqm, $plot_size_sqm, $bedrooms, $bathrooms, $built_year,
                    $has_pool, $has_garden, $has_terrace, $has_garage, $has_sea_view, $distance_to_beach_m,
                    $main_image, $is_featured, $is_active, $status, $propertyId
                ]);
                $success = 'Ingatlan sikeresen frissítve!';
            } else {
                // Insert new property
                $stmt = $pdo->prepare("
                    INSERT INTO properties (
                        title, slug, description, property_type_id, location_id,
                        price, currency, area_sqm, plot_size_sqm, bedrooms, bathrooms, built_year,
                        has_pool, has_garden, has_terrace, has_garage, has_sea_view, distance_to_beach_m,
                        main_image, is_featured, is_active, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title, $slug, $description, $property_type_id, $location_id,
                    $price, $currency, $area_sqm, $plot_size_sqm, $bedrooms, $bathrooms, $built_year,
                    $has_pool, $has_garden, $has_terrace, $has_garage, $has_sea_view, $distance_to_beach_m,
                    $main_image, $is_featured, $is_active, $status
                ]);
                $newId = $pdo->lastInsertId();
                $success = 'Ingatlan sikeresen létrehozva!';
                redirect('/admin/property-edit.php?id=' . $newId . '&success=1');
            }

            // Reload form data
            if ($isEdit) {
                $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
                $stmt->execute([$propertyId]);
                $formData = array_merge($formData, $stmt->fetch());
            }

        } catch (PDOException $e) {
            error_log('Property save error: ' . $e->getMessage());
            $error = 'Hiba történt a mentés során: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    $success = 'Ingatlan sikeresen létrehozva!';
}

include __DIR__ . '/partials/header.php';
?>

<style>
.form-section {
    background: var(--white);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    margin-bottom: var(--spacing-lg);
}

.form-section h3 {
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 2px solid var(--primary-blue);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-md);
}

.form-grid-3 {
    grid-template-columns: repeat(3, 1fr);
}

.form-group-full {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: var(--spacing-xs);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: var(--radius-md);
    font-family: var(--font-primary);
    transition: var(--transition-fast);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.form-group textarea {
    min-height: 200px;
    resize: vertical;
}

.checkbox-group {
    display: flex;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-item input[type="checkbox"] {
    width: auto;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: var(--spacing-sm);
    justify-content: flex-end;
    padding-top: var(--spacing-md);
    border-top: 1px solid #e0e0e0;
}

@media (max-width: 968px) {
    .form-grid,
    .form-grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>

<?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom: var(--spacing-lg);">
        <i class="fas fa-check-circle"></i> <?= e($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom: var(--spacing-lg);">
        <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <!-- Basic Information -->
    <div class="form-section">
        <h3><i class="fas fa-info-circle"></i> Alapadatok</h3>

        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="title">Cím *</label>
                <input type="text" id="title" name="title" required
                       value="<?= e($formData['title']) ?>">
            </div>

            <div class="form-group">
                <label for="property_type_id">Típus *</label>
                <select id="property_type_id" name="property_type_id" required>
                    <option value="">Válasszon típust</option>
                    <?php foreach ($propertyTypes as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= $formData['property_type_id'] == $type['id'] ? 'selected' : '' ?>>
                            <?= e($type['name_hu']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="location_id">Helyszín *</label>
                <select id="location_id" name="location_id" required>
                    <option value="">Válasszon helyszínt</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['id'] ?>" <?= $formData['location_id'] == $location['id'] ? 'selected' : '' ?>>
                            <?= e($location['city']) ?> (<?= e($location['region']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group form-group-full">
                <label for="description">Leírás *</label>
                <textarea id="description" name="description" required><?= e($formData['description']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Price and Size -->
    <div class="form-section">
        <h3><i class="fas fa-euro-sign"></i> Ár és méret</h3>

        <div class="form-grid form-grid-3">
            <div class="form-group">
                <label for="price">Ár *</label>
                <input type="number" id="price" name="price" step="0.01" required
                       value="<?= e($formData['price']) ?>">
            </div>

            <div class="form-group">
                <label for="currency">Pénznem *</label>
                <select id="currency" name="currency" required>
                    <option value="EUR" <?= $formData['currency'] === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                    <option value="USD" <?= $formData['currency'] === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Státusz</label>
                <select id="status" name="status">
                    <option value="available" <?= $formData['status'] === 'available' ? 'selected' : '' ?>>Elérhető</option>
                    <option value="reserved" <?= $formData['status'] === 'reserved' ? 'selected' : '' ?>>Foglalt</option>
                    <option value="sold" <?= $formData['status'] === 'sold' ? 'selected' : '' ?>>Eladva</option>
                </select>
            </div>

            <div class="form-group">
                <label for="area_sqm">Alapterület (m²) *</label>
                <input type="number" id="area_sqm" name="area_sqm" required
                       value="<?= e($formData['area_sqm']) ?>">
            </div>

            <div class="form-group">
                <label for="plot_size_sqm">Telekméret (m²)</label>
                <input type="number" id="plot_size_sqm" name="plot_size_sqm"
                       value="<?= e($formData['plot_size_sqm']) ?>">
            </div>

            <div class="form-group">
                <label for="distance_to_beach_m">Távolság a strandtól (m)</label>
                <input type="number" id="distance_to_beach_m" name="distance_to_beach_m"
                       value="<?= e($formData['distance_to_beach_m']) ?>">
            </div>
        </div>
    </div>

    <!-- Property Details -->
    <div class="form-section">
        <h3><i class="fas fa-home"></i> Részletek</h3>

        <div class="form-grid form-grid-3">
            <div class="form-group">
                <label for="bedrooms">Hálószobák *</label>
                <input type="number" id="bedrooms" name="bedrooms" required min="0"
                       value="<?= e($formData['bedrooms']) ?>">
            </div>

            <div class="form-group">
                <label for="bathrooms">Fürdőszobák *</label>
                <input type="number" id="bathrooms" name="bathrooms" required min="0"
                       value="<?= e($formData['bathrooms']) ?>">
            </div>

            <div class="form-group">
                <label for="built_year">Építés éve</label>
                <input type="number" id="built_year" name="built_year" min="1900" max="2050"
                       value="<?= e($formData['built_year']) ?>">
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="form-section">
        <h3><i class="fas fa-star"></i> Jellemzők</h3>

        <div class="checkbox-group">
            <div class="checkbox-item">
                <input type="checkbox" id="has_pool" name="has_pool" <?= $formData['has_pool'] ? 'checked' : '' ?>>
                <label for="has_pool" style="margin: 0;">Medence</label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" id="has_garden" name="has_garden" <?= $formData['has_garden'] ? 'checked' : '' ?>>
                <label for="has_garden" style="margin: 0;">Kert</label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" id="has_terrace" name="has_terrace" <?= $formData['has_terrace'] ? 'checked' : '' ?>>
                <label for="has_terrace" style="margin: 0;">Terasz</label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" id="has_garage" name="has_garage" <?= $formData['has_garage'] ? 'checked' : '' ?>>
                <label for="has_garage" style="margin: 0;">Garázs</label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" id="has_sea_view" name="has_sea_view" <?= $formData['has_sea_view'] ? 'checked' : '' ?>>
                <label for="has_sea_view" style="margin: 0;">Tengerre néző</label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" id="is_featured" name="is_featured" <?= $formData['is_featured'] ? 'checked' : '' ?>>
                <label for="is_featured" style="margin: 0; color: var(--accent-gold); font-weight: 600;">Kiemelt ingatlan</label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" id="is_active" name="is_active" <?= $formData['is_active'] ? 'checked' : '' ?>>
                <label for="is_active" style="margin: 0; color: var(--success-green); font-weight: 600;">Aktív (látható a weboldalon)</label>
            </div>
        </div>
    </div>

    <!-- Images -->
    <div class="form-section">
        <h3><i class="fas fa-image"></i> Kép</h3>

        <div class="form-group">
            <label for="main_image">Fő kép URL</label>
            <input type="text" id="main_image" name="main_image"
                   value="<?= e($formData['main_image']) ?>"
                   placeholder="https://example.com/image.jpg">
            <small style="color: var(--text-medium); display: block; margin-top: 0.5rem;">
                Ha üresen hagyja, alapértelmezett placeholder kép lesz használva.
            </small>
        </div>

        <?php if ($formData['main_image']): ?>
            <div style="margin-top: var(--spacing-md);">
                <img src="<?= e($formData['main_image']) ?>" alt="Property image"
                     style="max-width: 300px; border-radius: var(--radius-md);">
            </div>
        <?php endif; ?>
    </div>

    <!-- Image Gallery -->
    <?php if ($isEdit): ?>
        <?php
        // Load existing images
        try {
            $stmt = $pdo->prepare("
                SELECT id, filename, is_primary, sort_order
                FROM property_images
                WHERE property_id = ?
                ORDER BY sort_order ASC
            ");
            $stmt->execute([$propertyId]);
            $propertyImages = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Load images error: ' . $e->getMessage());
            $propertyImages = [];
        }
        ?>

        <div class="form-section" id="gallery-section">
            <h3><i class="fas fa-images"></i> Képgaléria</h3>

            <!-- Upload Area -->
            <div class="upload-area">
                <input type="file" id="image-upload" name="images[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('image-upload').click();">
                    <i class="fas fa-upload"></i> Képek feltöltése
                </button>
                <small style="color: var(--text-medium); display: block; margin-top: 0.5rem;">
                    Max 5MB képenként. Támogatott formátumok: JPG, PNG, WEBP
                </small>
            </div>

            <!-- Gallery Grid -->
            <div id="gallery-grid" class="gallery-grid">
                <?php if (empty($propertyImages)): ?>
                    <p style="color: var(--text-medium); text-align: center; padding: var(--spacing-lg);">
                        Még nincsenek feltöltött képek. Használja a fenti gombot képek feltöltéséhez.
                    </p>
                <?php else: ?>
                    <?php foreach ($propertyImages as $img): ?>
                        <div class="gallery-item" data-image-id="<?= $img['id'] ?>" draggable="true">
                            <img src="<?= ASSETS_PATH ?>/images/properties/<?= e($img['filename']) ?>" alt="Property image">
                            <div class="gallery-item-actions">
                                <button type="button" class="btn-icon btn-danger" onclick="deleteImage(<?= $img['id'] ?>)" title="Törlés">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="gallery-item-drag">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Upload Progress -->
            <div id="upload-progress" style="display: none; margin-top: var(--spacing-md);">
                <div class="progress-bar">
                    <div class="progress-bar-fill" id="progress-bar-fill"></div>
                </div>
                <p id="upload-status" style="text-align: center; margin-top: 0.5rem; color: var(--text-medium);"></p>
            </div>
        </div>

        <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: var(--spacing-md);
            margin-top: var(--spacing-lg);
        }

        .gallery-item {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            cursor: move;
            border: 2px solid #e0e0e0;
            transition: var(--transition-fast);
        }

        .gallery-item:hover {
            border-color: var(--primary-blue);
            box-shadow: var(--shadow-md);
        }

        .gallery-item.dragging {
            opacity: 0.5;
        }

        .gallery-item.drag-over {
            border-color: var(--accent-gold);
            border-style: dashed;
        }

        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .gallery-item-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.25rem;
        }

        .gallery-item-drag {
            position: absolute;
            bottom: 0.5rem;
            right: 0.5rem;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            pointer-events: none;
        }

        .btn-icon {
            background: rgba(0, 0, 0, 0.6);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
        }

        .btn-icon:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .btn-danger:hover {
            background: #dc3545 !important;
        }

        .progress-bar {
            width: 100%;
            height: 24px;
            background: #e0e0e0;
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--primary-blue);
            transition: width 0.3s ease;
            width: 0%;
        }

        .upload-area {
            padding: var(--spacing-md);
            border: 2px dashed #e0e0e0;
            border-radius: var(--radius-md);
            text-align: center;
        }

        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
        </style>

        <script>
        // Image upload
        document.getElementById('image-upload').addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length === 0) return;

            const formData = new FormData();
            formData.append('property_id', <?= $propertyId ?>);

            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }

            // Show progress
            const progressDiv = document.getElementById('upload-progress');
            const progressBar = document.getElementById('progress-bar-fill');
            const statusText = document.getElementById('upload-status');
            progressDiv.style.display = 'block';
            statusText.textContent = 'Feltöltés...';

            // Upload via AJAX
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    progressBar.style.width = percent + '%';
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        statusText.textContent = response.message;
                        progressBar.style.width = '100%';

                        // Reload page after short delay
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert('Hiba: ' + response.message);
                        progressDiv.style.display = 'none';
                    }
                } else {
                    alert('Feltöltési hiba történt.');
                    progressDiv.style.display = 'none';
                }
            });

            xhr.addEventListener('error', function() {
                alert('Hálózati hiba történt.');
                progressDiv.style.display = 'none';
            });

            xhr.open('POST', '/admin/api/property-image-upload.php');
            xhr.send(formData);

            // Reset file input
            e.target.value = '';
        });

        // Delete image
        function deleteImage(imageId) {
            if (!confirm('Biztosan törölni szeretné ezt a képet?')) {
                return;
            }

            const formData = new FormData();
            formData.append('image_id', imageId);

            fetch('/admin/api/property-image-delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    const item = document.querySelector('[data-image-id="' + imageId + '"]');
                    if (item) {
                        item.remove();
                    }

                    // Check if gallery is empty
                    const gallery = document.getElementById('gallery-grid');
                    if (gallery.children.length === 0) {
                        gallery.innerHTML = '<p style="color: var(--text-medium); text-align: center; padding: var(--spacing-lg);">Még nincsenek feltöltött képek.</p>';
                    }
                } else {
                    alert('Hiba: ' + data.message);
                }
            })
            .catch(error => {
                alert('Hálózati hiba történt.');
                console.error(error);
            });
        }

        // Drag and drop for reordering
        const gallery = document.getElementById('gallery-grid');
        let draggedItem = null;

        gallery.addEventListener('dragstart', function(e) {
            if (e.target.classList.contains('gallery-item')) {
                draggedItem = e.target;
                e.target.classList.add('dragging');
            }
        });

        gallery.addEventListener('dragend', function(e) {
            if (e.target.classList.contains('gallery-item')) {
                e.target.classList.remove('dragging');
            }
        });

        gallery.addEventListener('dragover', function(e) {
            e.preventDefault();
            const afterElement = getDragAfterElement(gallery, e.clientX);
            const dragging = document.querySelector('.dragging');

            if (afterElement == null) {
                gallery.appendChild(dragging);
            } else {
                gallery.insertBefore(dragging, afterElement);
            }
        });

        gallery.addEventListener('drop', function(e) {
            e.preventDefault();

            // Get new order
            const items = gallery.querySelectorAll('.gallery-item');
            const order = Array.from(items).map(item => item.getAttribute('data-image-id'));

            // Send to server
            fetch('/admin/api/property-image-reorder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Hiba a sorrend mentésekor: ' + data.message);
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Reorder error:', error);
            });
        });

        function getDragAfterElement(container, x) {
            const draggableElements = [...container.querySelectorAll('.gallery-item:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = x - box.left - box.width / 2;

                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
        </script>
    <?php endif; ?>

    <!-- Form Actions -->
    <div class="form-section">
        <div class="form-actions">
            <a href="/admin/properties.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Mégse
            </a>
            <button type="submit" name="save_property" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Mentés' : 'Létrehozás' ?>
            </button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/partials/footer.php'; ?>
