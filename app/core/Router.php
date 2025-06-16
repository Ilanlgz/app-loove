<?php
class Router {
    public function handleRequest() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $url = $_GET['url'] ?? '';
        $url = rtrim($url, '/');
        $urlParts = $url ? explode('/', $url) : [''];
        
        $controller = $urlParts[0] ?: 'dashboard';
        $action = $urlParts[1] ?? 'index';
        
        $this->route($controller, $action);
    }
    
    private function route($controller, $action) {
        switch($controller) {
            case 'auth':
                require_once BASE_PATH . '/app/controllers/AuthController.php';
                $ctrl = new AuthController();
                $this->callAction($ctrl, $action);
                break;
                
            case 'dashboard':
                require_once BASE_PATH . '/app/controllers/DashboardController.php';
                $ctrl = new DashboardController();
                $this->callAction($ctrl, $action);
                break;
                
            case 'discover':
                require_once BASE_PATH . '/app/controllers/DiscoverController.php';
                $ctrl = new DiscoverController();
                $this->callAction($ctrl, $action);
                break;
                
            case 'profile':
                require_once BASE_PATH . '/app/controllers/ProfileController.php';
                $ctrl = new ProfileController();
                $this->callAction($ctrl, $action);
                break;
                
            case 'messages':
                require_once BASE_PATH . '/app/controllers/MessageController.php';
                $ctrl = new MessageController();
                $this->callAction($ctrl, $action);
                break;
                
            case 'premium':
                require_once BASE_PATH . '/app/controllers/PremiumController.php';
                $ctrl = new PremiumController();
                $this->callAction($ctrl, $action);
                break;
                
            case 'admin':
                require_once BASE_PATH . '/app/controllers/AdminController.php';
                $ctrl = new AdminController();
                $this->callAction($ctrl, $action);
                break;
                
            default:
                // Si pas connecté, rediriger vers login
                if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
                    require_once BASE_PATH . '/app/controllers/AuthController.php';
                    $ctrl = new AuthController();
                    $ctrl->login();
                } else {
                    // Si connecté, rediriger vers dashboard
                    require_once BASE_PATH . '/app/controllers/DashboardController.php';
                    $ctrl = new DashboardController();
                    $ctrl->index();
                }
                break;
        }
    }
    
    private function callAction($controller, $action) {
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
    }
}
?>
