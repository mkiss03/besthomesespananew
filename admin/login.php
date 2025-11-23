<?php
require_once __DIR__ . '/../config/config.php';

startSession();

// If already logged in, redirect to dashboard
if (isAdmin()) {
    redirect('/admin/index.php');
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Kérjük, adja meg a felhasználónevet és jelszót.';
    } else {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];

                redirect('/admin/index.php');
            } else {
                $error = 'Helytelen felhasználónév vagy jelszó.';
            }
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'Hiba történt a bejelentkezés során.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Bejelentkezés | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            padding: var(--spacing-md);
        }

        .login-container {
            background: var(--white);
            padding: var(--spacing-xxl) var(--spacing-xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 450px;
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }

        .login-logo {
            font-family: var(--font-heading);
            font-size: 2rem;
            color: var(--primary-blue);
            margin-bottom: var(--spacing-sm);
        }

        .login-title {
            color: var(--text-medium);
            font-size: 1.125rem;
        }

        .login-form .form-group {
            margin-bottom: var(--spacing-md);
        }

        .login-form label {
            display: block;
            font-weight: 600;
            margin-bottom: var(--spacing-xs);
        }

        .login-form input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: var(--transition-fast);
        }

        .login-form input:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
            border: 1px solid #f5c6cb;
        }

        .login-footer {
            text-align: center;
            margin-top: var(--spacing-lg);
            color: var(--text-medium);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-home"></i> Besthomesespana
                </div>
                <h1 class="login-title">Admin Bejelentkezés</h1>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Felhasználónév
                    </label>
                    <input type="text" id="username" name="username" required autofocus
                           value="<?= e($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Jelszó
                    </label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Bejelentkezés
                </button>
            </form>

            <div class="login-footer">
                <p>
                    <i class="fas fa-shield-alt"></i>
                    Csak adminisztrátorok számára
                </p>
                <p style="margin-top: var(--spacing-sm);">
                    <small>Alapértelmezett: admin / admin123</small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
