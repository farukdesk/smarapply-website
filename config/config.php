<?php
/**
 * Configuration settings for SmartApply Pro
 */

// Initialize error logging first
require_once __DIR__ . '/../utils/ErrorLogger.php';
ErrorLogger::init();

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Configuration constants
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? 'sk_test_your_stripe_secret_key_here');
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? 'pk_test_your_stripe_key_here');
define('STRIPE_WEBHOOK_SECRET', $_ENV['STRIPE_WEBHOOK_SECRET'] ?? 'whsec_your_webhook_secret_here');

define('EMAIL_FROM', $_ENV['EMAIL_FROM'] ?? 'no_reply@smartapplypro.com');
define('EMAIL_SMTP_HOST', $_ENV['EMAIL_SMTP_HOST'] ?? 'smtp.hostinger.com');
define('EMAIL_SMTP_PORT', $_ENV['EMAIL_SMTP_PORT'] ?? 587);
define('EMAIL_SMTP_USERNAME', $_ENV['EMAIL_SMTP_USERNAME'] ?? 'no_reply@smartapplypro.com');
define('EMAIL_SMTP_PASSWORD', $_ENV['EMAIL_SMTP_PASSWORD'] ?? '');

define('LICENSE_KEY_PREFIX', $_ENV['LICENSE_KEY_PREFIX'] ?? 'SMARTAPPLY-PRO-');
define('LICENSE_KEY_LENGTH', intval($_ENV['LICENSE_KEY_LENGTH'] ?? 20));

// CORS settings
define('ALLOWED_ORIGINS', [
    'chrome-extension://your-extension-id',
    'https://smartapplypro.com',
    'https://www.smartapplypro.com',
    'http://localhost:3000',
    'http://localhost:8080'
]);

// Set CORS headers
function setCORSHeaders() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Set JSON response headers
function setJSONHeaders() {
    header('Content-Type: application/json');
}

// Send JSON response
function sendJSONResponse($data, $status_code = 200) {
    http_response_code($status_code);
    setJSONHeaders();
    echo json_encode($data);
    exit();
}
?>