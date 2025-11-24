<?php
/**
 * Property Filters Partial
 * Részletes kereső form az ingatlanok szűréséhez
 * AJAX-szal működik, nincs submit gomb!
 */
?>

<div class="filters">
    <form id="property-filters-form" class="filters-grid">
        <!-- Azonosító -->
        <div class="form-group">
            <label for="filter-property-id">Azonos

ító</label>
            <input type="text"
                   name="property_id"
                   id="filter-property-id"
                   class="form-control"
                   placeholder="Ingatlan ID...">
        </div>

        <!-- Helyszín -->
        <div class="form-group">
            <label for="filter-location">Helyszín</label>
            <select name="location" id="filter-location" class="form-control">
                <option value="">Összes helyszín</option>
                <?php
                $allowedCities = ['Benidorm', 'Alicante', 'Torrevieja', 'Calpe'];
                foreach ($allowedCities as $city): ?>
                    <option value="<?= e($city) ?>"><?= e($city) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Típus -->
        <div class="form-group">
            <label for="filter-type">Típus</label>
            <select name="type" id="filter-type" class="form-control">
                <option value="">Minden típus</option>
                <?php
                try {
                    $pdo = getPDO();
                    $typeStmt = $pdo->query("SELECT id, name_hu FROM property_types ORDER BY name_hu");
                    while ($t = $typeStmt->fetch()): ?>
                        <option value="<?= e($t['id']) ?>"><?= e($t['name_hu']) ?></option>
                    <?php endwhile;
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                }
                ?>
            </select>
        </div>

        <!-- Min. ár -->
        <div class="form-group">
            <label for="filter-price-min">Min. ár (€)</label>
            <input type="number"
                   name="price_min"
                   id="filter-price-min"
                   class="form-control"
                   placeholder="0">
        </div>

        <!-- Max. ár -->
        <div class="form-group">
            <label for="filter-price-max">Max. ár (€)</label>
            <input type="number"
                   name="price_max"
                   id="filter-price-max"
                   class="form-control"
                   placeholder="0">
        </div>

        <!-- Min. alapterület -->
        <div class="form-group">
            <label for="filter-area-min">Min. alapterület (m²)</label>
            <input type="number"
                   name="area_min"
                   id="filter-area-min"
                   class="form-control"
                   placeholder="0">
        </div>

        <!-- Min. hálószobák -->
        <div class="form-group">
            <label for="filter-bedrooms">Min. hálószobák</label>
            <input type="number"
                   name="bedrooms"
                   id="filter-bedrooms"
                   class="form-control"
                   placeholder="0">
        </div>

        <!-- Medence és Tengerre néző -->
        <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem; padding-top: 1.8rem;">
            <label style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="has_pool" id="filter-has-pool">
                Medence
            </label>
            <label style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="has_sea_view" id="filter-has-sea-view">
                Tengerre néző
            </label>
        </div>

        <!-- Szűrők törlése gomb (opcionális) -->
        <div class="form-group" style="align-self: flex-end;">
            <button type="button" id="clear-filters-btn" class="btn btn-outline btn-block">
                <i class="fas fa-times"></i> Szűrők törlése
            </button>
        </div>
    </form>
</div>
