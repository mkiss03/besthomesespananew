<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Tartalom szerkesztés';

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content_key'], $_POST['content_value'])) {
    $key = trim($_POST['content_key']);
    $value = trim($_POST['content_value']);

    if (update_content($key, $value)) {
        $successMessage = 'A tartalom sikeresen frissítve: ' . htmlspecialchars($key);
        // Redirect to prevent form resubmission
        header('Location: /admin/content.php?success=' . urlencode($key));
        exit;
    } else {
        $errorMessage = 'Hiba történt a mentés során.';
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $successMessage = 'A tartalom sikeresen frissítve: ' . htmlspecialchars($_GET['success']);
}

// Fetch all content from database
try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM site_content ORDER BY content_key");
    $allContent = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Content fetch error: ' . $e->getMessage());
    $allContent = [];
    $errorMessage = 'Hiba történt a tartalom betöltése során.';
}

// Group content by section
$sections = [
    'Hero szekció' => [],
    'Kiemelt ingatlanok' => [],
    'Costa Blanca bemutató' => [],
    'Ügynök bemutatkozás' => [],
    'Kapcsolat szekció' => [],
    'Footer' => []
];

foreach ($allContent as $item) {
    $key = $item['content_key'];

    if (strpos($key, 'home.hero_') === 0) {
        $sections['Hero szekció'][] = $item;
    } elseif (strpos($key, 'home.featured_') === 0) {
        $sections['Kiemelt ingatlanok'][] = $item;
    } elseif (strpos($key, 'home.costa_') === 0) {
        $sections['Costa Blanca bemutató'][] = $item;
    } elseif (strpos($key, 'home.agent_') === 0) {
        $sections['Ügynök bemutatkozás'][] = $item;
    } elseif (strpos($key, 'home.contact_') === 0) {
        $sections['Kapcsolat szekció'][] = $item;
    } elseif (strpos($key, 'footer.') === 0) {
        $sections['Footer'][] = $item;
    }
}

// Helper function to get human-readable label from key
function getLabel($key) {
    $label = str_replace(['home.', 'footer.', '_'], ['', '', ' '], $key);
    return ucfirst($label);
}

include __DIR__ . '/partials/header.php';
?>

<style>
.content-section {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
}

.content-section h2 {
    color: var(--primary-blue);
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-md);
    border-bottom: 2px solid var(--gray-200);
}

.content-item {
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-md);
    background: var(--gray-50);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
}

.content-item:last-child {
    margin-bottom: 0;
}

.content-label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--spacing-xs);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.content-key {
    font-size: 0.75rem;
    color: var(--text-medium);
    font-family: 'Courier New', monospace;
    background: var(--white);
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
}

.content-form {
    display: flex;
    gap: var(--spacing-md);
    align-items: flex-start;
    margin-top: var(--spacing-sm);
}

.content-input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: 0.9375rem;
    font-family: inherit;
}

.content-input:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(0, 71, 171, 0.1);
}

.content-textarea {
    min-height: 100px;
    resize: vertical;
    font-family: inherit;
}

.btn-save {
    padding: 0.75rem 1.5rem;
    white-space: nowrap;
}

.alert {
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-lg);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
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

.empty-section {
    text-align: center;
    color: var(--text-medium);
    padding: var(--spacing-xl);
}
</style>

<?php if ($successMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= $successMessage ?>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= $errorMessage ?>
    </div>
<?php endif; ?>

<div style="margin-bottom: var(--spacing-lg);">
    <p style="color: var(--text-medium);">
        Itt szerkesztheti a főoldal tartalmait. A módosítások azonnal megjelennek a weboldalon.
    </p>
</div>

<?php foreach ($sections as $sectionName => $items): ?>
    <?php if (!empty($items)): ?>
        <div class="content-section">
            <h2><i class="fas fa-edit"></i> <?= e($sectionName) ?></h2>

            <?php foreach ($items as $item): ?>
                <div class="content-item">
                    <div class="content-label">
                        <span><?= e(getLabel($item['content_key'])) ?></span>
                        <span class="content-key"><?= e($item['content_key']) ?></span>
                    </div>

                    <form method="POST" class="content-form">
                        <input type="hidden" name="content_key" value="<?= e($item['content_key']) ?>">

                        <?php if (strlen($item['value']) > 100 || strpos($item['value'], "\n") !== false || strpos($item['content_key'], 'description') !== false): ?>
                            <textarea
                                name="content_value"
                                class="content-input content-textarea"
                                placeholder="Tartalom..."
                            ><?= e($item['value']) ?></textarea>
                        <?php else: ?>
                            <input
                                type="text"
                                name="content_value"
                                class="content-input"
                                value="<?= e($item['value']) ?>"
                                placeholder="Tartalom..."
                            >
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-save">
                            <i class="fas fa-save"></i> Mentés
                        </button>
                    </form>

                    <div style="font-size: 0.75rem; color: var(--text-light); margin-top: var(--spacing-xs);">
                        Utoljára frissítve: <?= date('Y-m-d H:i', strtotime($item['updated_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if (empty($allContent)): ?>
    <div class="content-section">
        <div class="empty-section">
            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: var(--spacing-md); color: var(--gray-400);"></i>
            <p>Nincs megjeleníthető tartalom.</p>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
