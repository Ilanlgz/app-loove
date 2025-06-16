<?php
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/models/User.php';

/**
 * Dashboard Controller for Loove Dating App
 * Main page after user login
 */
class DashboardController extends BaseController {
    private $userModel;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireAuth();
        $this->userModel = new User();
    }
    
    /**
     * Show main dashboard
     */
    public function index() {
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        $this->view('dashboard/index', [
            'title' => 'Tableau de bord - Loove',
            'user' => $user,
            'success' => $this->getFlash('success')
        ]);
    }
}
?>
