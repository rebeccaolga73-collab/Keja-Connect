<?php
/**
 * KejaConnect - System Configuration File
 * Contains path resolutions, security settings, and global constants
 */

// Establish error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent session hijacking by setting secure flags
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Uncomment on HTTPS servers:
    // ini_set('session.cookie_secure', 1);
    session_start();
}

// Global App Configuration
define('APP_NAME', 'KejaConnect');
define('APP_COMPANY', 'KejaConnect Property Solutions');
define('BRAND_PRIMARY', '#1a6b3c'); // Deep Green
define('BRAND_ACCENT', '#f0a500');  // Gold
define('SUPPORT_EMAIL', 'support@kejaconnect.co.ke');

// Auto-detect base URL - Calculate once from the application root
// The config file is always at: /app-root/config/config.php
// We need the URL to point to: /app-root
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Normalize document root and config path for consistent comparison on Windows
$doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/')) : '';
$config_file = str_replace('\\', '/', __FILE__);

// Remove PATH_INFO from script name to avoid polluted BASE_URL values
$script_name = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
$path_info = isset($_SERVER['PATH_INFO']) ? str_replace('\\', '/', $_SERVER['PATH_INFO']) : '';
if ($path_info !== '' && substr($script_name, -strlen($path_info)) === $path_info) {
    $script_name = substr($script_name, 0, -strlen($path_info));
}

// Calculate the app root path (one directory above config folder)
if ($doc_root && stripos($config_file, $doc_root) === 0) {
    $app_dir = dirname(dirname($config_file)); // Up from config/ to app root
    $rel_path = substr($app_dir, strlen($doc_root));
    $rel_path = str_replace('\\', '/', $rel_path);
    $rel_path = rtrim($rel_path, '/');
    if ($rel_path === '.') {
        $rel_path = '';
    }
} else {
    // Fallback: calculate from SCRIPT_NAME and strip PATH_INFO
    $rel_path = dirname($script_name);
}

// Normalize to root path style
$rel_path = rtrim($rel_path, '/');
if ($rel_path === '.' || $rel_path === '/') {
    $rel_path = '';
}

define('BASE_URL', $protocol . '://' . $host . $rel_path);

// Pagination default size
define('ITEMS_PER_PAGE', 20);

// File upload restrictions
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5 Megabytes
define('ALLOWED_EXT_PHOTOS', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_EXT_DOCS', ['pdf', 'doc', 'docx', 'png', 'jpg']);

/**
 * CSRF Protection Token Generator
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Validation Helper
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash Message Handler (Set alert)
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'text' => $message
    ];
}

/**
 * Flash Message Handler (Get & Clear)
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}
