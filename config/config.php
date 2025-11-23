<?php
/**
 * Besthomesespana Configuration File
 * Database connection and global settings
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'c88384bhenew');
define('DB_USER', 'c88384bhe');
define('DB_PASS', 'Eszter2009');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Besthomesespana');
define('SITE_URL', 'http://localhost'); // Change to your domain
define('ADMIN_EMAIL', 'info@besthomesespana.com');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_PATH', '/assets');
define('UPLOAD_PATH', BASE_PATH . '/public/assets/images/properties');

// Security
define('SESSION_LIFETIME', 3600 * 2); // 2 hours
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Get PDO database connection
 * @return PDO
 */
function getPDO(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Start secure session
 */
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize output for HTML
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format price with currency
 */
function formatPrice(float $price, string $currency = 'EUR'): string {
    $formatted = number_format($price, 0, ',', ' ');

    switch ($currency) {
        case 'EUR':
            return '€' . $formatted;
        case 'USD':
            return '$' . $formatted;
        default:
            return $formatted . ' ' . $currency;
    }
}

/**
 * Format area in square meters
 */
function formatArea(int $area): string {
    return number_format($area, 0, ',', ' ') . ' m²';
}

/**
 * Redirect helper
 */
function redirect(string $url, int $statusCode = 302): void {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Check if user is admin
 */
function isAdmin(): bool {
    startSession();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin authentication
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        redirect('/admin/login.php');
    }
}

/**
 * Get current URL path
 */
function getCurrentPath(): string {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

/**
 * Generate slug from title
 */
function generateSlug(string $title): string {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Get content from site_content table by key
 * Uses in-memory cache to avoid multiple DB queries for the same key
 *
 * @param string $key Content key (e.g., 'home.hero_title')
 * @param string $default Default value if key not found
 * @return string Content value or default
 */
function get_content(string $key, string $default = ''): string {
    static $cache = [];

    // Return from cache if already loaded
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT value FROM site_content WHERE content_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $result = $stmt->fetch();

        if ($result) {
            $cache[$key] = $result['value'];
            return $result['value'];
        } else {
            $cache[$key] = $default;
            return $default;
        }
    } catch (PDOException $e) {
        error_log('get_content error for key "' . $key . '": ' . $e->getMessage());
        return $default;
    }
}

/**
 * Update or insert content in site_content table
 * Will be used later by admin panel to update homepage content
 *
 * @param string $key Content key
 * @param string $value Content value
 * @return bool Success status
 */
function update_content(string $key, string $value): bool {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            INSERT INTO site_content (content_key, value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE value = ?, updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$key, $value, $value]);
        return true;
    } catch (PDOException $e) {
        error_log('update_content error for key "' . $key . '": ' . $e->getMessage());
        return false;
    }
}
