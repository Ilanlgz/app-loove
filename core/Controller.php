<?php

/**
 * Base Controller Class for Loove Dating App
 * Provides common functionality for all controllers
 */
abstract class Controller {
    protected $db;
    protected $params;
    
    public function __construct($params = []) {
        $this->params = $params;
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Load a view file
     * @param string $view View name (without .php extension)
     * @param array $data Data to pass to the view
     */
    protected function view($view, $data = []) {
        // Extract data array to variables
        if (!empty($data)) {
            extract($data);
        }
        
        // Build view file path
        $viewFile = BASE_PATH . '/app/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("View file not found: {$view}");
        }
    }
    
    /**
     * Load a model
     * @param string $model Model name
     * @return object Model instance
     */
    protected function model($model) {
        $modelFile = BASE_PATH . '/app/models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            throw new Exception("Model not found: {$model}");
        }
    }
    
    /**
     * Redirect to another URL
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code
     */
    protected function redirect($url, $statusCode = 302) {
        if (!headers_sent()) {
            // Handle relative URLs
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = APP_URL . '/' . ltrim($url, '/');
            }
            
            header("Location: {$url}", true, $statusCode);
            exit;
        }
    }
    
    /**
     * Send JSON response
     * @param array $data Data to send
     * @param int $statusCode HTTP status code
     */
    protected function json($data, $statusCode = 200) {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=UTF-8');
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Ensure user is logged in, redirect if not
     */
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
            $this->redirect('');
        }
    }
    
    /**
     * Check if user is admin
     * @return bool
     */
    protected function isAdmin() {
        return $this->isLoggedIn() && 
               isset($_SESSION['user_role']) && 
               $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Ensure user is admin, redirect if not
     */
    protected function requireAdmin() {
        if (!$this->isAdmin()) {
            $_SESSION['error_message'] = "Accès refusé. Droits administrateur requis.";
            $this->redirect('dashboard');
        }
    }
    
    /**
     * Get current user data
     * @return array|null
     */
    protected function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? '',
            'name' => $_SESSION['user_name'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
    }
    
    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @return bool
     */
    protected function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF token
     * @return string
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Sanitize input data
     * @param array $data Data to sanitize
     * @return array
     */
    protected function sanitize($data) {
        return filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
    
    /**
     * Set flash message
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     */
    protected function setFlash($type, $message) {
        $_SESSION["flash_{$type}"] = $message;
    }
    
    /**
     * Get and clear flash message
     * @param string $type Message type
     * @return string|null
     */
    protected function getFlash($type) {
        $message = $_SESSION["flash_{$type}"] ?? null;
        unset($_SESSION["flash_{$type}"]);
        return $message;
    }
}
?>
