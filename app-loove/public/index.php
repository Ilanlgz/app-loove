<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Autoload classes
spl_autoload_register(function ($class_name) {
    // Check if file exists in models directory
    if (file_exists('../models/' . $class_name . '.php')) {
        include '../models/' . $class_name . '.php';
        return;
    }
    
    // Check if file exists in controllers directory
    if (file_exists('../controllers/' . $class_name . '.php')) {
        include '../controllers/' . $class_name . '.php';
        return;
    }
});

// Start session
session_start();

// Static files handling (CSS, JS, images)
$request_uri = $_SERVER['REQUEST_URI'];

// Check if this is a request for a static file
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg)$/', $request_uri)) {
    // Get the relative path within the app
    $base_path = '/loove/app-loove/public';
    $relative_path = $request_uri;
    
    // Remove base path if present
    if (strpos($request_uri, $base_path) === 0) {
        $relative_path = substr($request_uri, strlen($base_path));
    }
    
    // Make sure the path starts with a slash
    if (substr($relative_path, 0, 1) !== '/') {
        $relative_path = '/' . $relative_path;
    }
    
    // Try several possible locations for the file
    $possible_paths = [
        __DIR__ . $relative_path,                      // Direct path
        __DIR__ . '/css' . $relative_path,             // In css subdirectory
        __DIR__ . '/assets' . $relative_path,          // In assets subdirectory
        __DIR__ . '/assets/css' . $relative_path       // In assets/css subdirectory
    ];
    
    // For CSS files, also check the specific filename in css and assets/css directories
    if (preg_match('/\.css$/', $relative_path)) {
        $filename = basename($relative_path);
        $possible_paths[] = __DIR__ . '/css/' . $filename;
        $possible_paths[] = __DIR__ . '/assets/css/' . $filename;
    }
    
    // Try each path
    foreach ($possible_paths as $path) {
        if (file_exists($path) && is_readable($path)) {
            // Get file extension
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            
            // Set content type based on file extension
            switch ($extension) {
                case 'css':
                    header('Content-Type: text/css');
                    break;
                case 'js':
                    header('Content-Type: application/javascript');
                    break;
                case 'jpg':
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'gif':
                    header('Content-Type: image/gif');
                    break;
                case 'ico':
                    header('Content-Type: image/x-icon');
                    break;
                case 'svg':
                    header('Content-Type: image/svg+xml');
                    break;
            }
            
            // Disable caching for development
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output the file and exit
            readfile($path);
            exit;
        }
    }
    
    // If we get here, the file was not found
    header('HTTP/1.0 404 Not Found');
    echo '404 - File not found';
    exit;
}

// Routing logic
$controller = 'AuthController'; // Default controller

if (preg_match('/^\/admin/', $request_uri)) {
    $controller = 'AdminController';
} elseif (preg_match('/^\/profile/', $request_uri)) {
    $controller = 'ProfileController';
} elseif (preg_match('/^\/search/', $request_uri)) {
    $controller = 'SearchController';
} elseif (preg_match('/^\/match/', $request_uri)) {
    $controller = 'MatchController';
} elseif (preg_match('/^\/message/', $request_uri)) {
    $controller = 'MessageController';
} elseif (preg_match('/^\/subscription/', $request_uri)) {
    $controller = 'SubscriptionController';
}

// Instantiate the controller
$controller_instance = new $controller();
$controller_instance->handleRequest();
?>