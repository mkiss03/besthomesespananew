<?php
require_once __DIR__ . '/../config/config.php';

// Page meta data
$pageTitle = 'Kapcsolat';
$pageDescription = 'Vegye fel velünk a kapcsolatot! Segítünk megtalálni álmai spanyol ingatlanát.';

// Handle form submission
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
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
            $pdo = getPDO();
            $stmt = $pdo->prepare("
                INSERT INTO contact_inquiries (name, email, phone, message, source_page)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $phone, $message, 'contact']);
            $formSuccess = true;

            // Clear form data on success
            $_POST = [];
        } catch (PDOException $e) {
            error_log('Contact form error: ' . $e->getMessage());
            $formError = 'Hiba történt az üzenet elküldése során. Kérjük, próbálja újra később.';
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<style>
.contact-page {
    margin-top: 90px;
}

.contact-hero {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: var(--white);
    padding: var(--spacing-xxl) 0;
    text-align: center;
}

.contact-hero h1 {
    color: var(--white);
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
}

.contact-content {
    padding: var(--spacing-xxl) 0;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xxl);
    margin-top: var(--spacing-xl);
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.info-card {
    background: var(--sand-light);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-md);
}

.info-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1.25rem;
    flex-shrink: 0;
}

.info-content h3 {
    font-size: 1.25rem;
    margin-bottom: var(--spacing-xs);
}

.info-content p {
    color: var(--text-medium);
    margin-bottom: 0.5rem;
}

.contact-form-container {
    background: var(--white);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
}

.contact-form-container h2 {
    margin-bottom: var(--spacing-lg);
}

.contact-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.contact-form .form-group {
    margin-bottom: var(--spacing-md);
}

.contact-form label {
    display: block;
    font-weight: 600;
    margin-bottom: var(--spacing-xs);
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: var(--radius-md);
    font-family: var(--font-primary);
    transition: var(--transition-fast);
}

.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.contact-form textarea {
    resize: vertical;
    min-height: 150px;
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

.map-section {
    margin-top: var(--spacing-xxl);
}

.map-container {
    height: 400px;
    background: var(--sand-light);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-medium);
}

@media (max-width: 968px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }

    .contact-form .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="contact-page">
    <!-- Hero -->
    <div class="contact-hero">
        <div class="container">
            <h1>Kapcsolat</h1>
            <p>Forduljon hozzánk bizalommal! Segítünk megtalálni álmai spanyol ingatlanát.</p>
        </div>
    </div>

    <!-- Contact Content -->
    <div class="contact-content">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Information -->
                <div class="contact-info">
                    <div>
                        <h2>Lépjen kapcsolatba velünk!</h2>
                        <p style="color: var(--text-medium); font-size: 1.125rem; margin-top: var(--spacing-sm);">
                            Tapasztalt csapatunk magyar nyelven áll rendelkezésére minden kérdésben.
                        </p>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h3>Telefonszámok</h3>
                            <p><strong>Spanyolország:</strong> +34 123 456 789</p>
                            <p><strong>Magyarország:</strong> +36 20 123 4567</p>
                            <p style="font-size: 0.875rem; color: var(--text-light);">
                                H-P: 9:00 - 18:00
                            </p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h3>Email</h3>
                            <p><a href="mailto:<?= ADMIN_EMAIL ?>"><?= ADMIN_EMAIL ?></a></p>
                            <p style="font-size: 0.875rem; color: var(--text-light);">
                                24 órán belül válaszolunk
                            </p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h3>Címünk</h3>
                            <p>Alicante, Costa Blanca</p>
                            <p>Spanyolország</p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h3>Nyitvatartás</h3>
                            <p><strong>Hétfő - Péntek:</strong> 9:00 - 18:00</p>
                            <p><strong>Szombat:</strong> 10:00 - 14:00</p>
                            <p><strong>Vasárnap:</strong> Zárva</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-container">
                    <h2>Küldjön üzenetet</h2>

                    <?php if ($formSuccess): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Köszönjük üzenetét!</strong><br>
                            Hamarosan felvesszük Önnel a kapcsolatot.
                        </div>
                    <?php endif; ?>

                    <?php if ($formError): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= e($formError) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="contact-form">
                        <div class="form-row">
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
                        </div>

                        <div class="form-group">
                            <label for="phone">Telefonszám</label>
                            <input type="tel" id="phone" name="phone"
                                   value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="message">Üzenet *</label>
                            <textarea id="message" name="message" required><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" name="submit_contact" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-paper-plane"></i> Üzenet küldése
                        </button>
                    </form>
                </div>
            </div>

            <!-- Map Placeholder -->
            <div class="map-section">
                <h2 class="text-center mb-4">Találjon meg minket</h2>
                <div class="map-container">
                    <div style="text-align: center;">
                        <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>Alicante, Costa Blanca, Spanyolország</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
