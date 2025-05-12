<?php
// Main application entry point
require_once '../app/config/config.php';
require_once '../app/config/database.php';

// Autoload classes
spl_autoload_register(function ($class_name) {
    // Try to load from models directory
    if (file_exists(APP_PATH . '/models/' . $class_name . '.php')) {
        require_once APP_PATH . '/models/' . $class_name . '.php';
        return;
    }
    
    // Try to load from controllers directory
    if (file_exists(APP_PATH . '/controllers/' . $class_name . '.php')) {
        require_once APP_PATH . '/controllers/' . $class_name . '.php';
        return;
    }
    
    // Try to load from helpers directory
    if (file_exists(APP_PATH . '/helpers/' . $class_name . '.php')) {
        require_once APP_PATH . '/helpers/' . $class_name . '.php';
        return;
    }
});

// Start session
session_start();

// Handle static files (CSS, JS, images)
$request_uri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|webp)$/', $request_uri)) {
    // Get the requested file path
    $file_path = PUBLIC_PATH . parse_url($request_uri, PHP_URL_PATH);
    
    // Check if file exists
    if (file_exists($file_path) && is_readable($file_path)) {
        // Set appropriate content type
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
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
            case 'svg':
                header('Content-Type: image/svg+xml');
                break;
            case 'webp':
                header('Content-Type: image/webp');
                break;
        }
        
        // Disable caching during development
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        // Serve the file
        readfile($file_path);
        exit;
    }
}

// Simple routing based on request URI
$route = trim(parse_url($request_uri, PHP_URL_PATH), '/');
$route = $route ?: 'home'; // Default route

// Determine controller and action
if (strpos($route, '/') !== false) {
    [$controller_name, $action] = explode('/', $route, 2);
} else {
    $controller_name = $route;
    $action = 'index';
}

// Convert controller name to proper case
$controller_name = ucfirst(strtolower($controller_name)) . 'Controller';

// Default to HomeController if not specified
if ($controller_name === 'HomeController') {
    $controller_name = 'AuthController';
    $action = 'login';
}

// Check if controller exists
if (!file_exists(APP_PATH . '/controllers/' . $controller_name . '.php')) {
    // Fallback to Error controller
    $controller_name = 'ErrorController';
    $action = 'notFound';
}

// Instantiate the controller
$controller = new $controller_name();

// Call the action or default to index
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    // Fallback to index or show 404
    if (method_exists($controller, 'index')) {
        $controller->index();
    } else {
        $error_controller = new ErrorController();
        $error_controller->notFound();
    }
}
