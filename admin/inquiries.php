<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Érdeklődések';

// Get inquiries
try {
    $pdo = getPDO();

    $stmt = $pdo->query("
        SELECT
            i.*,
            p.title as property_title,
            p.id as property_id
        FROM inquiries i
        LEFT JOIN properties p ON i.property_id = p.id
        ORDER BY
            CASE i.status
                WHEN 'new' THEN 1
                WHEN 'contacted' THEN 2
                WHEN 'closed' THEN 3
            END,
            i.created_at DESC
    ");
    $inquiries = $stmt->fetchAll();

    // Count by status
    $statusCounts = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM inquiries
        GROUP BY status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    error_log('Inquiries error: ' . $e->getMessage());
    $inquiries = [];
    $statusCounts = [];
}

include __DIR__ . '/partials/header.php';
?>

<div class="stats-grid" style="margin-bottom: var(--spacing-xl);">
    <div class="stat-card">
        <div class="stat-icon gold">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-content">
            <h3><?= $statusCounts['new'] ?? 0 ?></h3>
            <p>Új érdeklődés</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-phone"></i>
        </div>
        <div class="stat-content">
            <h3><?= $statusCounts['contacted'] ?? 0 ?></h3>
            <p>Kapcsolatba lépve</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= $statusCounts['closed'] ?? 0 ?></h3>
            <p>Lezárva</p>
        </div>
    </div>
</div>

<div class="data-table">
    <div class="table-header">
        <h2>Összes érdeklődés (<?= count($inquiries) ?> db)</h2>
    </div>

    <?php if (empty($inquiries)): ?>
        <div style="padding: var(--spacing-xxl); text-align: center; color: var(--text-medium);">
            <i class="fas fa-envelope-open" style="font-size: 3rem; margin-bottom: var(--spacing-md);"></i>
            <p>Még nincsenek érdeklődések.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Dátum</th>
                        <th>Név</th>
                        <th>Elérhetőség</th>
                        <th>Ingatlan</th>
                        <th>Üzenet</th>
                        <th>Státusz</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inq): ?>
                        <tr style="<?= $inq['status'] === 'new' ? 'background: #fff3cd;' : '' ?>">
                            <td>
                                <small><?= date('Y-m-d H:i', strtotime($inq['created_at'])) ?></small>
                            </td>
                            <td><strong><?= e($inq['name']) ?></strong></td>
                            <td>
                                <i class="fas fa-envelope"></i> <?= e($inq['email']) ?><br>
                                <?php if ($inq['phone']): ?>
                                    <i class="fas fa-phone"></i> <?= e($inq['phone']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($inq['property_title']): ?>
                                    <a href="/admin/property-edit.php?id=<?= $inq['property_id'] ?>" target="_blank">
                                        <?= e($inq['property_title']) ?>
                                    </a>
                                <?php else: ?>
                                    <small style="color: var(--text-light);">Általános érdeklődés</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?= nl2br(e(substr($inq['message'], 0, 100))) ?><?= strlen($inq['message']) > 100 ? '...' : '' ?></small>
                            </td>
                            <td>
                                <select onchange="updateInquiryStatus(<?= $inq['id'] ?>, this.value)"
                                        style="padding: 0.5rem; border-radius: var(--radius-sm);">
                                    <option value="new" <?= $inq['status'] === 'new' ? 'selected' : '' ?>>Új</option>
                                    <option value="contacted" <?= $inq['status'] === 'contacted' ? 'selected' : '' ?>>Kapcsolatba lépve</option>
                                    <option value="closed" <?= $inq['status'] === 'closed' ? 'selected' : '' ?>>Lezárva</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function updateInquiryStatus(id, status) {
    fetch('/admin/api/inquiries.php?id=' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Hiba: ' + (data.message || 'Ismeretlen hiba történt'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hiba történt a státusz frissítése során');
    });
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
