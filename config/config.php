<?php
// Configuration principale de l'application Loove

// Environment
define('APP_ENV', 'development');

// Application
define('APP_NAME', 'Loove');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'http://localhost/loove');
define('BASE_PATH', dirname(__DIR__));

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'loove_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security
define('SESSION_LIFETIME', 7200);
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);

// File Upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('PROFILE_IMAGES_PATH', '/public/uploads/profiles/');

// Pagination
define('PROFILES_PER_PAGE', 12);
define('MESSAGES_PER_PAGE', 50);

// Premium Features
define('PREMIUM_MONTHLY_PRICE', 19.99);
define('PREMIUM_YEARLY_PRICE', 199.99);

// Geolocation
define('DEFAULT_SEARCH_RADIUS', 50);
define('MAX_SEARCH_RADIUS', 500);

// Notifications
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_PUSH_NOTIFICATIONS', true);

// Admin
define('ADMIN_EMAIL', 'admin@loove.com');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
}

// Session Configuration - Only configure if session is not started yet
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_start();
}

// Timezone
date_default_timezone_set('Europe/Paris');
?>
