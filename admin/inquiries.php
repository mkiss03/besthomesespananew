<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Érdeklődések';

// Handle mark as read for contact inquiries
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $inquiryId = (int)$_GET['mark_read'];
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE contact_inquiries SET is_read = 1 WHERE id = ?");
        $stmt->execute([$inquiryId]);
    } catch (PDOException $e) {
        error_log('Mark read error: ' . $e->getMessage());
    }
    header('Location: /admin/inquiries.php#contact-inquiries');
    exit;
}

// Get property inquiries
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

    // Get contact inquiries
    $stmt = $pdo->query("
        SELECT *
        FROM contact_inquiries
        ORDER BY created_at DESC
        LIMIT 100
    ");
    $contactInquiries = $stmt->fetchAll();

    // Count unread contact inquiries
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_inquiries WHERE is_read = 0");
    $unreadContactCount = $stmt->fetch()['count'];

} catch (PDOException $e) {
    error_log('Inquiries error: ' . $e->getMessage());
    $inquiries = [];
    $statusCounts = [];
    $contactInquiries = [];
    $unreadContactCount = 0;
}

include __DIR__ . '/partials/header.php';
?>

<style>
.tabs {
    display: flex;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-xl);
    border-bottom: 2px solid var(--gray-200);
}

.tab {
    padding: var(--spacing-md) var(--spacing-lg);
    background: none;
    border: none;
    color: var(--text-medium);
    font-weight: 600;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: var(--transition-fast);
}

.tab:hover {
    color: var(--primary-blue);
}

.tab.active {
    color: var(--primary-blue);
    border-bottom-color: var(--primary-blue);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.contact-table tr.unread {
    background: #fff8e1;
}

.contact-table tr.unread:hover {
    background: #fff3cd;
}

.badge-new {
    background: #fff3cd;
    color: #856404;
}

.badge-read {
    background: #d4edda;
    color: #155724;
}

.message-preview {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.btn-view-details {
    padding: 0.4rem 1rem;
    font-size: 0.875rem;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-lg);
}

.modal.active {
    display: flex;
}

.modal-content {
    background: var(--white);
    border-radius: var(--radius-lg);
    max-width: 700px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-xl);
}

.modal-header {
    padding: var(--spacing-lg);
    background: var(--primary-blue);
    color: var(--white);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.modal-header h2 {
    margin: 0;
    color: var(--white);
}

.modal-body {
    padding: var(--spacing-xl);
}

.detail-row {
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-lg);
    border-bottom: 1px solid var(--gray-200);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: var(--text-medium);
    font-size: 0.875rem;
    margin-bottom: var(--spacing-xs);
}

.detail-value {
    color: var(--text-dark);
}

.message-content {
    background: var(--gray-50);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    border-left: 3px solid var(--primary-blue);
    white-space: pre-wrap;
    word-wrap: break-word;
}

.btn-close-modal {
    background: var(--white);
    color: var(--primary-blue);
    border: none;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-weight: 600;
}
</style>

<div class="tabs">
    <button class="tab active" onclick="switchTab('property-inquiries')">
        <i class="fas fa-building"></i> Ingatlan érdeklődések (<?= count($inquiries) ?>)
    </button>
    <button class="tab" onclick="switchTab('contact-inquiries')">
        <i class="fas fa-envelope"></i> Kapcsolatfelvételi űrlapok (<?= count($contactInquiries) ?>)
        <?php if ($unreadContactCount > 0): ?>
            <span class="badge badge-new" style="margin-left: 0.5rem;"><?= $unreadContactCount ?> új</span>
        <?php endif; ?>
    </button>
</div>

<!-- Property Inquiries Tab -->
<div class="tab-content active" id="property-inquiries">
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
            <h2>Ingatlan érdeklődések (<?= count($inquiries) ?> db)</h2>
        </div>

        <?php if (empty($inquiries)): ?>
            <div style="padding: var(--spacing-xxl); text-align: center; color: var(--text-medium);">
                <i class="fas fa-envelope-open" style="font-size: 3rem; margin-bottom: var(--spacing-md);"></i>
                <p>Még nincsenek ingatlan érdeklődések.</p>
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
</div>

<!-- Contact Inquiries Tab -->
<div class="tab-content" id="contact-inquiries">
    <div class="stats-grid" style="margin-bottom: var(--spacing-xl);">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
                <h3><?= count($contactInquiries) ?></h3>
                <p>Összes űrlap</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon gold">
                <i class="fas fa-envelope-open"></i>
            </div>
            <div class="stat-content">
                <h3><?= $unreadContactCount ?></h3>
                <p>Olvasatlan</p>
            </div>
        </div>
    </div>

    <div class="data-table">
        <div class="table-header">
            <h2>Kapcsolatfelvételi űrlapok (<?= count($contactInquiries) ?> db)</h2>
        </div>

        <?php if (empty($contactInquiries)): ?>
            <div style="padding: var(--spacing-xxl); text-align: center; color: var(--text-medium);">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: var(--spacing-md);"></i>
                <p>Még nincsenek kapcsolatfelvételi űrlapok.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="contact-table">
                    <thead>
                        <tr>
                            <th>Dátum</th>
                            <th>Név</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Üzenet</th>
                            <th>Státusz</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contactInquiries as $inquiry): ?>
                            <tr class="<?= $inquiry['is_read'] ? '' : 'unread' ?>">
                                <td style="white-space: nowrap;">
                                    <?= date('Y-m-d H:i', strtotime($inquiry['created_at'])) ?>
                                </td>
                                <td><strong><?= e($inquiry['name']) ?></strong></td>
                                <td><?= e($inquiry['email']) ?></td>
                                <td><?= e($inquiry['phone'] ?: '-') ?></td>
                                <td>
                                    <div class="message-preview">
                                        <?= e(substr($inquiry['message'], 0, 50)) ?><?= strlen($inquiry['message']) > 50 ? '...' : '' ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($inquiry['is_read']): ?>
                                        <span class="badge badge-read">Olvasott</span>
                                    <?php else: ?>
                                        <span class="badge badge-new">Új</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="viewContactDetails(<?= $inquiry['id'] ?>)" class="btn btn-primary btn-view-details">
                                        <i class="fas fa-eye"></i> Részletek
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Contact Detail Modal -->
<div class="modal" id="contactModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-envelope-open"></i> Érdeklődés részletei</h2>
            <button onclick="closeContactModal()" class="btn-close-modal">
                <i class="fas fa-times"></i> Bezárás
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<script>
// Tab switching
function switchTab(tabId) {
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

    event.target.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

// Update property inquiry status
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

// View contact inquiry details
const contactInquiries = <?= json_encode($contactInquiries) ?>;

function viewContactDetails(id) {
    const inquiry = contactInquiries.find(i => i.id == id);
    if (!inquiry) return;

    const modalBody = document.getElementById('modalBody');
    modalBody.innerHTML = `
        <div class="detail-row">
            <div class="detail-label">Beérkezett:</div>
            <div class="detail-value">${new Date(inquiry.created_at).toLocaleString('hu-HU')}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Név:</div>
            <div class="detail-value">${escapeHtml(inquiry.name)}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Email cím:</div>
            <div class="detail-value">
                <a href="mailto:${escapeHtml(inquiry.email)}">${escapeHtml(inquiry.email)}</a>
            </div>
        </div>
        ${inquiry.phone ? `
        <div class="detail-row">
            <div class="detail-label">Telefonszám:</div>
            <div class="detail-value">
                <a href="tel:${escapeHtml(inquiry.phone)}">${escapeHtml(inquiry.phone)}</a>
            </div>
        </div>
        ` : ''}
        ${inquiry.source_page ? `
        <div class="detail-row">
            <div class="detail-label">Oldal:</div>
            <div class="detail-value">${escapeHtml(inquiry.source_page)}</div>
        </div>
        ` : ''}
        <div class="detail-row">
            <div class="detail-label">Üzenet:</div>
            <div class="message-content">${escapeHtml(inquiry.message).replace(/\n/g, '<br>')}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Státusz:</div>
            <div class="detail-value">
                ${inquiry.is_read ? '<span class="badge badge-read">Olvasott</span>' : '<span class="badge badge-new">Új</span>'}
            </div>
        </div>
    `;

    document.getElementById('contactModal').classList.add('active');

    // Mark as read
    if (!inquiry.is_read) {
        fetch(`/admin/inquiries.php?mark_read=${id}`)
            .then(() => {
                inquiry.is_read = 1;
            });
    }
}

function closeContactModal() {
    document.getElementById('contactModal').classList.remove('active');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on backdrop click
document.getElementById('contactModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeContactModal();
    }
});

// Check if we should show contact inquiries tab (from hash or redirect)
if (window.location.hash === '#contact-inquiries') {
    switchTab('contact-inquiries');
    document.querySelectorAll('.tab')[1].click();
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
