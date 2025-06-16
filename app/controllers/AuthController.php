<?php
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/models/User.php';

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function index() {
        $this->login();
    }
    
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('/loove/public/dashboard');
        }
        
        $this->view('auth/login', [
            'title' => 'Connexion - Loove',
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success')
        ]);
    }
    
    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('/loove/public/dashboard');
        }
        
        $this->view('auth/register', [
            'title' => 'Inscription - Loove',
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success')
        ]);
    }
    
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/loove/public/auth/login');
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Tous les champs sont requis.');
            $this->redirect('/loove/public/auth/login');
        }
        
        $user = $this->userModel->login($email, $password);
        
        if ($user) {
            $_SESSION["loggedin"] = true;
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["first_name"] = $user['first_name'];
            $_SESSION["last_name"] = $user['last_name'];
            $_SESSION["email"] = $user['email'];
            
            $this->setFlash('success', "Bon retour parmi nous, " . $user['first_name'] . " !");
            $this->redirect('/loove/public/dashboard');
        } else {
            $this->setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/loove/public/auth/login');
        }
    }
    
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/loove/public/auth/register');
        }
        
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'location' => trim($_POST['location'] ?? ''),
            'occupation' => trim($_POST['occupation'] ?? ''),
            'interests' => trim($_POST['interests'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'height' => !empty($_POST['height']) ? (int)$_POST['height'] : null,
        ];
        
        $userId = $this->userModel->register($data);
        
        if ($userId) {
            // Connexion automatique
            $_SESSION["loggedin"] = true;
            $_SESSION["user_id"] = $userId;
            $_SESSION["first_name"] = $data['first_name'];
            $_SESSION["last_name"] = $data['last_name'];
            $_SESSION["email"] = $data['email'];
            
            $this->setFlash('success', "Bienvenue sur Loove, " . $data['first_name'] . " !");
            $this->redirect('/loove/public/dashboard');
        } else {
            $this->setFlash('error', 'Erreur lors de l\'inscription.');
            $this->redirect('/loove/public/auth/register');
        }
    }
    
    public function logout() {
        session_destroy();
        $this->setFlash('success', 'Vous êtes maintenant déconnecté.');
        $this->redirect('/loove/public/auth/login');
    }
}
?>
