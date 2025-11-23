<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Dashboard';

// Get statistics
try {
    $pdo = getPDO();

    // Total properties
    $totalProperties = $pdo->query("SELECT COUNT(*) as count FROM properties")->fetch()['count'];

    // Active properties
    $activeProperties = $pdo->query("SELECT COUNT(*) as count FROM properties WHERE is_active = 1")->fetch()['count'];

    // Featured properties
    $featuredProperties = $pdo->query("SELECT COUNT(*) as count FROM properties WHERE is_featured = 1")->fetch()['count'];

    // Pending inquiries
    $pendingInquiries = $pdo->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'new'")->fetch()['count'];

    // Recent properties
    $recentStmt = $pdo->query("
        SELECT
            p.id,
            p.title,
            p.price,
            p.currency,
            p.is_active,
            p.is_featured,
            p.created_at,
            l.city
        FROM properties p
        LEFT JOIN locations l ON p.location_id = l.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $recentProperties = $recentStmt->fetchAll();

    // Top locations
    $locationsStmt = $pdo->query("
        SELECT
            l.city,
            l.region,
            COUNT(p.id) as property_count
        FROM locations l
        LEFT JOIN properties p ON l.id = p.location_id AND p.is_active = 1
        GROUP BY l.id
        HAVING property_count > 0
        ORDER BY property_count DESC
        LIMIT 5
    ");
    $topLocations = $locationsStmt->fetchAll();

    // Recent inquiries
    $inquiriesStmt = $pdo->query("
        SELECT
            i.id,
            i.name,
            i.email,
            i.message,
            i.status,
            i.created_at,
            p.title as property_title
        FROM inquiries i
        LEFT JOIN properties p ON i.property_id = p.id
        ORDER BY i.created_at DESC
        LIMIT 5
    ");
    $recentInquiries = $inquiriesStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Dashboard error: ' . $e->getMessage());
    $totalProperties = $activeProperties = $featuredProperties = $pendingInquiries = 0;
    $recentProperties = $topLocations = $recentInquiries = [];
}

include __DIR__ . '/partials/header.php';
?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($totalProperties, 0, ',', ' ') ?></h3>
            <p>Összes ingatlan</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($activeProperties, 0, ',', ' ') ?></h3>
            <p>Aktív ingatlan</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon gold">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($featuredProperties, 0, ',', ' ') ?></h3>
            <p>Kiemelt ingatlan</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($pendingInquiries, 0, ',', ' ') ?></h3>
            <p>Új érdeklődés</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-xl); margin-bottom: var(--spacing-xl);">
    <!-- Recent Properties -->
    <div class="data-table">
        <div class="table-header">
            <h2>Legutóbbi ingatlanok</h2>
            <a href="/admin/properties.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-eye"></i> Összes
            </a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Cím</th>
                    <th>Ár</th>
                    <th>Státusz</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentProperties)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-medium);">
                            Nincsenek ingatlanok
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentProperties as $prop): ?>
                        <tr>
                            <td>
                                <strong><?= e($prop['title']) ?></strong><br>
                                <small style="color: var(--text-medium);">
                                    <?= e($prop['city']) ?>
                                </small>
                            </td>
                            <td><?= formatPrice($prop['price'], $prop['currency']) ?></td>
                            <td>
                                <?php if ($prop['is_active']): ?>
                                    <span class="badge badge-success">Aktív</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inaktív</span>
                                <?php endif; ?>
                                <?php if ($prop['is_featured']): ?>
                                    <span class="badge badge-warning">Kiemelt</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Locations -->
    <div class="data-table">
        <div class="table-header">
            <h2>Top helyszínek</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Város</th>
                    <th>Régió</th>
                    <th>Ingatlanok száma</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topLocations)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-medium);">
                            Nincsenek helyszínek
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topLocations as $loc): ?>
                        <tr>
                            <td><strong><?= e($loc['city']) ?></strong></td>
                            <td><?= e($loc['region']) ?></td>
                            <td>
                                <span class="badge badge-success">
                                    <?= $loc['property_count'] ?> db
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Inquiries -->
<div class="data-table">
    <div class="table-header">
        <h2>Legutóbbi érdeklődések</h2>
        <a href="/admin/inquiries.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-eye"></i> Összes
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Név</th>
                <th>Email</th>
                <th>Ingatlan</th>
                <th>Üzenet</th>
                <th>Státusz</th>
                <th>Dátum</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recentInquiries)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-medium);">
                        Nincsenek érdeklődések
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($recentInquiries as $inq): ?>
                    <tr>
                        <td><strong><?= e($inq['name']) ?></strong></td>
                        <td><?= e($inq['email']) ?></td>
                        <td>
                            <?php if ($inq['property_title']): ?>
                                <small><?= e($inq['property_title']) ?></small>
                            <?php else: ?>
                                <small style="color: var(--text-light);">Általános</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?= e(substr($inq['message'], 0, 50)) . (strlen($inq['message']) > 50 ? '...' : '') ?></small>
                        </td>
                        <td>
                            <?php if ($inq['status'] === 'new'): ?>
                                <span class="badge badge-warning">Új</span>
                            <?php elseif ($inq['status'] === 'contacted'): ?>
                                <span class="badge badge-success">Kapcsolatba lépve</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Lezárva</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?= date('Y-m-d H:i', strtotime($inq['created_at'])) ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
@media (max-width: 968px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }

    table {
        font-size: 0.875rem;
    }

    table th,
    table td {
        padding: var(--spacing-sm);
    }
}
</style>

<?php include __DIR__ . '/partials/footer.php'; ?>
