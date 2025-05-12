<?php
/**
 * Base Controller - All controllers extend this
 */
class BaseController {
    // View data container
    protected $view_data = [];
    
    // Constructor
    public function __construct() {
        // Initialize with default view data
        $this->view_data = [
            'app_name' => APP_NAME,
            'page_title' => APP_NAME,
            'user' => $this->getCurrentUser(),
            'styles' => ['/css/main.css', '/css/responsive.css'],
            'scripts' => ['/js/main.js'],
        ];
    }
    
    // Get current logged in user
    protected function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $user_model = new UserModel();
            return $user_model->findById($_SESSION['user_id']);
        }
        return null;
    }
    
    // Check if user is logged in
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Require authentication to access a page
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }
    
    // Redirect to another page
    protected function redirect($path) {
        header('Location: ' . SITE_URL . '/' . $path);
        exit;
    }
    
    // Set page title
    protected function setTitle($title) {
        $this->view_data['page_title'] = $title . ' - ' . APP_NAME;
    }
    
    // Add a CSS file to the page
    protected function addStyle($path) {
        if (!in_array($path, $this->view_data['styles'])) {
            $this->view_data['styles'][] = $path;
        }
    }
    
    // Add a JS file to the page
    protected function addScript($path) {
        if (!in_array($path, $this->view_data['scripts'])) {
            $this->view_data['scripts'][] = $path;
        }
    }
    
    // Set a flash message
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    // Get flash message and clear it
    protected function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
    
    // Render a view
    protected function render($view_path, $data = []) {
        // Merge custom data with default view data
        $this->view_data = array_merge($this->view_data, $data);
        
        // Add flash message if exists
        $this->view_data['flash'] = $this->getFlash();
        
        // Extract data to make it available in the view
        extract($this->view_data);
        
        // Get the view content
        ob_start();
        include APP_PATH . '/views/' . $view_path . '.php';
        $content = ob_get_clean();
        
        // Render with layout
        include APP_PATH . '/views/layouts/main.php';
    }
    
    // Return JSON response
    protected function jsonResponse($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
