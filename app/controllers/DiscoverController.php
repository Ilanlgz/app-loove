<?php
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/models/User.php';

class DiscoverController extends BaseController {
    private $userModel;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireAuth();
        $this->userModel = new User();
    }
    
    public function index() {
        $this->view('discover/index', [
            'title' => 'Découvrir - Loove',
            'success' => $this->getFlash('success')
        ]);
    }
    
    public function like() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            return;
        }
        
        $profileId = (int)($_POST['profile_id'] ?? 0);
        $profileName = $_POST['profile_name'] ?? '';
        
        if ($profileId <= 0) {
            echo json_encode(['success' => false]);
            return;
        }
        
        // Simuler un match (33% de chance)
        $isMatch = rand(1, 100) <= 33;
        
        echo json_encode(['success' => true, 'isMatch' => $isMatch]);
    }
    
    public function pass() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            return;
        }
        
        echo json_encode(['success' => true]);
    }
}
?>
