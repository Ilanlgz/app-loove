<?php
namespace App\Controllers;

use Controller;
use App\Models\User;

class AdminController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->ensureAdmin(); // Ensure only admins can access these methods
    }
    
    public function index() {
        $this->login();
    }
    
    public function login() {
        if ($this->isAdminLoggedIn()) {
            $this->redirect('/loove/public/admin/dashboard');
        }
        
        $this->view('admin/login', [
            'title' => 'Connexion Admin - Loove',
            'error' => $this->getFlash('error')
        ]);
    }
    
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/loove/public/admin/login');
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $admin = $this->userModel->adminLogin($email, $password);
        
        if ($admin) {
            $_SESSION["admin_loggedin"] = true;
            $_SESSION["admin_id"] = $admin['id'];
            $_SESSION["admin_name"] = $admin['first_name'] . ' ' . $admin['last_name'];
            $_SESSION["admin_email"] = $admin['email'];
            
            $this->redirect('/loove/public/admin/dashboard');
        } else {
            $this->setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/loove/public/admin/login');
        }
    }
    
    public function dashboard() {
        $this->requireAdminAuth();
        
        $stats = $this->userModel->getAdminStats();
        
        $this->view('admin/dashboard', [
            'title' => 'Dashboard Admin - Loove',
            'stats' => $stats
        ]);
    }
    
    public function users() {
        $this->requireAdminAuth();
        
        $users = $this->userModel->getAllUsers();
        
        $this->view('admin/users', [
            'title' => 'Gestion Utilisateurs - Admin',
            'users' => $users
        ]);
    }
    
    public function broadcast() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = trim($_POST['message']);
            $type = $_POST['type'] ?? 'info';
            
            if (!empty($message)) {
                $conn = getDbConnection();
                
                // Enregistrer l'annonce en base
                $stmt = $conn->prepare("INSERT INTO announcements (message, type, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$message, $type]);
                
                // Ici vous pourriez ajouter l'envoi d'emails, notifications push, etc.
                
                $_SESSION['success'] = "Annonce envoyée avec succès !";
                $this->redirect('/admin/broadcast.php');
            }
        }
        
        $this->view('admin/broadcast');
    }
    
    public function logout() {
        unset($_SESSION["admin_loggedin"]);
        unset($_SESSION["admin_id"]);
        unset($_SESSION["admin_name"]);
        unset($_SESSION["admin_email"]);
        
        $this->redirect('/loove/public/admin/login');
    }
    
    private function isAdminLoggedIn() {
        return isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true;
    }
    
    private function requireAdminAuth() {
        if (!$this->isAdminLoggedIn()) {
            $this->setFlash('error', 'Accès administrateur requis.');
            $this->redirect('/loove/public/admin/login');
        }
    }
}
?>
