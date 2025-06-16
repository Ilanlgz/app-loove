<?php
// Application Configuration
define('APP_NAME', 'Loove');
define('APP_VERSION', '1.0.0');
define('SITE_URL', 'http://localhost/loove');
define('BASE_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Error reporting - turn off in production
error_reporting(E_ALL);
ini_set('display_errors', 1);
