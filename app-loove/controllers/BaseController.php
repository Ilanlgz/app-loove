<?php
class BaseController {
    protected $viewData = [];
    
    public function __construct() {
        // Initialize view data
        $this->viewData = [
            'title' => 'Loove Dating App',
            'cssFiles' => ['/loove/app-loove/public/css/style.css'], // Start with main CSS
            'baseUrl' => '/loove/app-loove/public'
        ];
    }
    
    protected function addCss($cssFile) {
        // Make sure we use absolute path
        if (strpos($cssFile, '/') === 0 && strpos($cssFile, '/loove/app-loove/public') !== 0) {
            $cssFile = '/loove/app-loove/public' . $cssFile;
        } elseif (strpos($cssFile, '/') !== 0) {
            $cssFile = '/loove/app-loove/public/' . $cssFile;
        }
        
        // Add CSS file if not already added
        if (!in_array($cssFile, $this->viewData['cssFiles'])) {
            $this->viewData['cssFiles'][] = $cssFile;
        }
    }
    
    protected function setTitle($title) {
        $this->viewData['title'] = $title;
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            // Stocker l'URL actuelle pour rediriger après la connexion
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            
            // Rediriger vers la page de connexion
            header('Location: /loove/app-loove/public/login');
            exit;
        }
    }

    protected function render($view, $data = []) {
        // Merge custom data with default data
        $viewData = array_merge($this->viewData, $data);
        
        // Extract variables for use in the view
        extract($viewData);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include('../views/' . $view . '.php');
        
        // Get the content
        $content = ob_get_clean();
        
        // Include the layout with the content
        include('../views/layouts/main.php');
    }
    
    // Gestionnaire par défaut pour les requêtes
    public function handleRequest() {
        $this->index();
    }
    
    // Méthode index par défaut
    public function index() {
        echo "Méthode index du contrôleur de base. Elle doit être remplacée dans les contrôleurs enfants.";
    }
}
