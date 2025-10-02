<?php
// Load environment variables
require_once __DIR__ . '/env_loader.php';

try {
    loadEnv();
} catch (Exception $e) {
    die('Error loading environment variables: ' . $e->getMessage());
}

// Email Configuration from environment variables
define('RECIPIENT_EMAIL', env('RECIPIENT_EMAIL', 'your-email@example.com'));
define('FROM_DOMAIN', env('FROM_DOMAIN', 'vertexlabs.com'));

// SMTP Configuration
define('USE_SMTP', env('USE_SMTP', false));
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', env('SMTP_PORT', 587));
define('SMTP_USERNAME', env('SMTP_USERNAME', ''));
define('SMTP_PASSWORD', env('SMTP_PASSWORD', ''));
define('SMTP_ENCRYPTION', env('SMTP_ENCRYPTION', 'tls'));
define('SMTP_AUTH', env('SMTP_AUTH', true));

// Redirect URLs
define('SUCCESS_REDIRECT', env('SUCCESS_REDIRECT', 'index.html#contact'));
define('ERROR_REDIRECT', env('ERROR_REDIRECT', 'index.html#contact'));
?>