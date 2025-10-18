<?php
/**
 * Main Configuration File
 * Tridah Budget Tracker
 */

// Error Reporting (disable in production)
// For API endpoints, errors should be logged, not displayed
error_reporting(E_ALL);
ini_set('display_errors', 0); // Changed to 0 to prevent HTML output in API responses
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// Timezone
date_default_timezone_set('UTC');

// Application Settings
define('APP_NAME', 'Tridah Budget Tracker');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_VERSION', '1.0.0');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Set to 1 in production with HTTPS
ini_set('session.cookie_samesite', 'Lax');
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI', APP_URL . '/api/auth/google-callback.php');

// Security
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'change-this-to-random-key-in-production');
define('HASH_ALGORITHM', 'sha256');

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// API Rate Limiting
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_PERIOD', 60); // seconds

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('API_PATH', ROOT_PATH . '/api');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Auto-load classes
spl_autoload_register(function ($class) {
    $file = ROOT_PATH . '/api/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Include database configuration
require_once __DIR__ . '/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

