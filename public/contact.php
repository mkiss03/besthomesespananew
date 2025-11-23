<?php
/**
 * Contact Form Handler
 * Processes contact form submissions and redirects back to homepage
 */
require_once __DIR__ . '/../config/config.php';
startSession();

// If not POST request, redirect to homepage contact section
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/#kapcsolat');
    exit;
}

// Get and trim form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// Initialize errors array
$errors = [];

// Validation
if (empty($name)) {
    $errors[] = 'A név mező kitöltése kötelező.';
}

if (empty($email)) {
    $errors[] = 'Az e-mail cím mező kitöltése kötelező.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Érvénytelen e-mail cím formátum.';
}

if (empty($message)) {
    $errors[] = 'Az üzenet mező kitöltése kötelező.';
}

// If there are validation errors
if (!empty($errors)) {
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_old'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message
    ];
    redirect('/#kapcsolat');
    exit;
}

// Try to insert into database
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        INSERT INTO contact_inquiries (name, email, phone, message, source_page)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $phone, $message, 'home']);

    // Success - set success message
    $_SESSION['contact_success'] = 'Köszönjük megkeresését! Hamarosan felvesszük Önnel a kapcsolatot.';

    // Clear old input data
    unset($_SESSION['contact_old']);

} catch (PDOException $e) {
    // Log the error
    error_log('Contact form error: ' . $e->getMessage());

    // Set error message for user
    $_SESSION['contact_errors'] = ['Váratlan hiba történt az üzenet küldése során. Kérjük, próbálja újra később.'];
    $_SESSION['contact_old'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message
    ];
}

// Always redirect back to homepage contact section
redirect('/#kapcsolat');
exit;
