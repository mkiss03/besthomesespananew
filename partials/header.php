<?php
if (!function_exists('getPDO')) {
    require_once __DIR__ . '/../../config/config.php';
}
$currentPath = getCurrentPath();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . SITE_NAME : SITE_NAME . ' - Prémium Spanyol Ingatlanok' ?></title>
    <meta name="description" content="<?= e($pageDescription ?? 'Prémium spanyol ingatlanok magyar ügyfeleknek. Costa Blanca, Costa del Sol - villák, apartmanok, penthouse-ok.') ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">

    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php if (isset($additionalHead)): ?>
        <?= $additionalHead ?>
    <?php endif; ?>
</head>
<body>

<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="container">
        <a href="/index.php" class="navbar-logo">
            <span class="navbar-logo-text">Besthomesespana</span>
        </a>

        <button class="navbar-toggle" id="navbar-toggle" aria-label="Menu megnyitása">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <ul class="navbar-menu" id="navbar-menu">
            <li><a href="/index.php" class="<?= $currentPath === '/index.php' || $currentPath === '/' ? 'active' : '' ?>">Főoldal</a></li>
            <li><a href="/#ingatlanok-section" class="<?= strpos($currentPath, '/properties') === 0 ? 'active' : '' ?>">Ingatlanok</a></li>
            <li><a href="/about.php" class="<?= $currentPath === '/about.php' ? 'active' : '' ?>">Rólunk</a></li>
            <li><a href="/#kapcsolat">Kapcsolat</a></li>
        </ul>
    </div>
</nav>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.getElementById('navbar-menu');

    if (navbarToggle) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
        });
    }

    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
});
</script>
