<?php
// Configuration settings for the application

// Application constants
define('APP_NAME', 'App Loove');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // Change to 'production' in live environment

// Base URL of the application
define('BASE_URL', 'http://localhost/app-loove');

// Error reporting settings
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Other configuration settings can be added here
?>