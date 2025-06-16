<?php

/**
 * Modern Router Class for Loove Dating App
 */
class Router {
    private $routes = [];
    private $params = [];
    
    public function __construct() {
        // Initialize router
    }
    
    /**
     * Add a route to the routing table
     */
    public function add($route, $params = []) {
        // Convert route pattern to regex
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = '/^' . $route . '$/i';
        
        $this->routes[$route] = $params;
    }
    
    /**
     * Match the current URL to a route
     */
    public function match($url) {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                
                $this->params = $params;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Dispatch the route - execute the controller action
     */
    public function dispatch($url) {
        // Remove query string if present
        $url = strtok($url, '?');
        
        // Debug: Log the URL being processed
        if (defined('APP_ENV') && APP_ENV === 'development') {
            error_log("Router: Processing URL: '$url'");
            error_log("Router: Available routes: " . print_r(array_keys($this->routes), true));
        }
        
        if ($this->match($url)) {
            $controller = $this->params['controller'] ?? 'AuthController';
            $action = $this->params['action'] ?? 'landing';
            
            // Debug information
            if (defined('APP_ENV') && APP_ENV === 'development') {
                error_log("Router: Dispatching to {$controller}::{$action}");
            }
            
            // CRITICAL: Double-check if controller class exists
            if (!class_exists($controller)) {
                // Try to load it manually one more time
                $controllerFile = BASE_PATH . '/app/controllers/' . $controller . '.php';
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                }
            }
            
            // Check if controller class exists NOW
            if (class_exists($controller)) {
                $controllerInstance = new $controller($this->params);
                
                // Check if action method exists
                if (method_exists($controllerInstance, $action)) {
                    $controllerInstance->$action();
                    return;
                } else {
                    if (defined('APP_ENV') && APP_ENV === 'development') {
                        throw new Exception("Action '{$action}' not found in controller '{$controller}'");
                    } else {
                        $this->handle404();
                        return;
                    }
                }
            } else {
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    throw new Exception("Controller '{$controller}' not found");
                } else {
                    $this->handle404();
                    return;
                }
            }
        } else {
            // No route matched - show 404
            $this->handle404();
        }
    }
    
    /**
     * Handle 404 errors with fallback content
     */
    private function handle404() {
        http_response_code(404);
        
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        // Instead of showing 404, redirect to landing page for main routes
        $url = $_GET['url'] ?? '';
        if (empty($url) || $url === '/') {
            // This should be the home page, try to load AuthController directly
            if (class_exists('AuthController')) {
                $auth = new AuthController([]);
                $auth->landing();
                return;
            }
        }
        
        echo $this->generate404Page();
        exit;
    }
    
    /**
     * Generate a 404 error page
     */
    private function generate404Page() {
        $currentUrl = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '');
        
        return "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Loove - Page non trouvée</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { 
            max-width: 600px; 
            background: rgba(255,255,255,0.1); 
            padding: 40px; 
            border-radius: 20px; 
            backdrop-filter: blur(10px);
        }
        h1 { color: #ff6b9d; font-size: 4rem; margin: 0; }
        h2 { margin-bottom: 20px; }
        p { font-size: 1.2rem; color: rgba(255,255,255,0.9); }
        a { 
            color: #ff6b9d; 
            text-decoration: none; 
            font-weight: bold;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        a:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        .debug {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 0.9rem;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>404</h1>
        <h2>Page non trouvée</h2>
        <p>La page que vous recherchez n'existe pas.</p>
        <p>URL demandée: {$currentUrl}</p>
        
        <a href='/loove/public/'>🏠 Retour à l'accueil</a>
        
        " . (defined('APP_ENV') && APP_ENV === 'development' ? "
        <div class='debug'>
            <strong>Debug Info:</strong><br>
            • URL: {$currentUrl}<br>
            • Router routes: " . count($this->routes) . " registered<br>
            • AuthController exists: " . (class_exists('AuthController') ? 'YES' : 'NO') . "<br>
        </div>" : "") . "
    </div>
</body>
</html>";
    }
    
    /**
     * Get all registered routes
     */
    public function getRoutes() {
        return $this->routes;
    }
}
?>
