<?php
require_once __DIR__ . '/../../config/config.php';
requireAdmin();

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Admin | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 65px;
        }

        body {
            background: #f5f6fa;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--text-dark);
            color: var(--white);
            overflow-y: auto;
            z-index: 1000;
            transition: var(--transition-medium);
        }

        .sidebar-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            color: var(--white);
            text-align: center;
        }

        .sidebar-menu {
            padding: var(--spacing-md) 0;
        }

        .menu-item {
            display: block;
            padding: var(--spacing-md) var(--spacing-lg);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition-fast);
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border-left-color: var(--accent-gold);
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            border-left-color: var(--accent-gold);
        }

        .menu-item i {
            width: 20px;
            margin-right: var(--spacing-sm);
        }

        /* Main Content */
        .admin-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .admin-topbar {
            height: var(--topbar-height);
            background: var(--white);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--spacing-lg);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar-title {
            font-size: 1.5rem;
            color: var(--text-dark);
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-role {
            font-size: 0.875rem;
            color: var(--text-medium);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
        }

        .admin-content {
            padding: var(--spacing-xl);
            flex: 1;
        }

        /* Mobile Sidebar Toggle */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }

        .stat-card {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        }

        .stat-icon.gold {
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-orange));
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
        }

        .stat-icon.red {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .stat-content h3 {
            font-size: 2rem;
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            color: var(--text-medium);
            margin: 0;
            font-size: 0.875rem;
        }

        /* Table */
        .data-table {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-header {
            padding: var(--spacing-lg);
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        table th {
            background: var(--sand-light);
            font-weight: 600;
            color: var(--text-dark);
        }

        table tr:hover {
            background: var(--sand-light);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
        }

        @media (max-width: 968px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.active {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-home"></i> Besthomesespana
                </div>
                <p style="text-align: center; color: rgba(255, 255, 255, 0.6); font-size: 0.875rem; margin-top: 0.5rem;">
                    Admin Panel
                </p>
            </div>

            <nav class="sidebar-menu">
                <a href="/admin/index.php" class="menu-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="/admin/properties.php" class="menu-item <?= $currentPage === 'properties' || $currentPage === 'property-edit' ? 'active' : '' ?>">
                    <i class="fas fa-building"></i> Ingatlanok
                </a>
                <a href="/admin/inquiries.php" class="menu-item <?= $currentPage === 'inquiries' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Érdeklődések
                </a>
                <a href="/" target="_blank" class="menu-item">
                    <i class="fas fa-external-link-alt"></i> Weboldal megtekintése
                </a>
                <a href="/admin/logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Kijelentkezés
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <div class="admin-topbar">
                <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="topbar-title"><?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?></h1>
                </div>

                <div class="topbar-user">
                    <div class="user-info">
                        <div class="user-name"><?= e($adminName) ?></div>
                        <div class="user-role">Adminisztrátor</div>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($adminName, 0, 1)) ?>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="admin-content">
