<?php
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/models/User.php';

class ProfileController extends BaseController {
    private $userModel;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireAuth();
        $this->userModel = new User();
    }
    
    public function index() {
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        $this->view('profile/index', [
            'title' => 'Mon Profil - Loove',
            'user' => $user,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ]);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/loove/public/profile');
        }
        
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'age' => (int)($_POST['age'] ?? 0),
            'phone' => $_POST['phone'] ?? '',
            'location' => $_POST['location'] ?? '',
            'height' => $_POST['height'] ?? '',
            'occupation' => $_POST['occupation'] ?? '',
            'relationship_status' => $_POST['relationship_status'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'interests' => $_POST['interests'] ?? ''
        ];
        
        $success = $this->userModel->updateProfile($_SESSION['user_id'], $data);
        
        if ($success) {
            $_SESSION['first_name'] = $data['first_name'];
            $_SESSION['last_name'] = $data['last_name'];
            $this->setFlash('success', 'Profil mis à jour avec succès !');
        } else {
            $this->setFlash('error', 'Erreur lors de la mise à jour du profil.');
        }
        
        $this->redirect('/loove/public/profile');
    }
}
?>
