<?php
namespace App\Controllers;

use Controller;
use App\Models\User; // Assuming User model will be created

class UserController extends Controller {

    private $userModel;

    public function __construct() {
        parent::__construct(); // Call parent constructor to initialize $db
        $this->userModel = $this->model('User');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $email = trim($_POST['email']);
            $password = $_POST['password']; // Password will be hashed and compared

            $errors = [];
            if (empty($email)) {
                $errors['email_err'] = 'Please enter email.';
            }
            if (empty($password)) {
                $errors['password_err'] = 'Please enter password.';
            }

            if (empty($errors)) {
                $loggedInUser = $this->userModel->login($email, $password);
                if ($loggedInUser) {
                    $this->createUserSession($loggedInUser);
                    \ErrorHandler::logAppAction("User logged in: {$email}");
                    $this->redirect('dashboard'); // Redirect to dashboard
                } else {
                    $errors['login_err'] = 'Invalid email or password.';
                    // Pass errors back to the landing page's login form
                    $_SESSION['error_message_login'] = $errors['login_err'];
                    $_SESSION['form_data_login'] = ['email' => $email]; // To repopulate form
                    $this->redirect('?form=login'); // Redirect to landing page, login tab
                }
            } else {
                // Pass errors back to the landing page's login form
                $_SESSION['error_message_login'] = implode(' ', $errors);
                $_SESSION['form_data_login'] = ['email' => $email];
                $this->redirect('?form=login'); // Redirect to landing page, login tab
            }

        } else {
            // If GET request to /login, redirect to landing page
            $this->redirect('');
        }
    }

    // Ajout de la méthode processLogin pour compatibilité
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Traitement direct de la connexion
            require_once __DIR__ . '/../../classes/User.php';
            
            $user = new \User();
            
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']);
            
            // Validation des données
            if (empty($email) || empty($password)) {
                $_SESSION['login_error'] = "Veuillez remplir tous les champs.";
                header("location: ../../login.php");
                exit();
            }
            
            // Vérifier les identifiants
            $user->email = $email;
            
            if ($user->emailExists()) {
                // Vérifier si le compte est actif
                if (!$user->is_active) {
                    $_SESSION['login_error'] = "Votre compte a été désactivé.";
                    header("location: ../../login.php");
                    exit();
                }
                
                // Vérifier le mot de passe
                if (password_verify($password, $user->password)) {
                    // Démarrer la session
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $user->id;
                    $_SESSION["email"] = $user->email;
                    $_SESSION["first_name"] = $user->first_name;
                    $_SESSION["last_name"] = $user->last_name;
                    $_SESSION["is_premium"] = $user->is_premium;
                    $_SESSION["profile_picture"] = $user->profile_picture;
                    
                    // Gestion du "Se souvenir de moi"
                    if ($remember) {
                        $cookie_name = "loove_remember";
                        $cookie_value = base64_encode($user->id . ":" . $user->email);
                        setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
                    }
                    
                    // Mettre à jour la dernière activité
                    $user->updateLastActive();
                    
                    // Rediriger vers la page d'accueil principale
                    header("location: ../../");
                    exit();
                } else {
                    $_SESSION['login_error'] = "Email ou mot de passe incorrect.";
                }
            } else {
                $_SESSION['login_error'] = "Email ou mot de passe incorrect.";
            }
            
            // Rediriger vers la page de connexion avec l'erreur
            header("location: ../../login.php");
            exit();
        }
        
        // Si ce n'est pas POST, rediriger vers login
        header("location: ../../login.php");
        exit();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'confirm_password' => $_POST['confirm_password'],
                'gender' => $_POST['gender'] ?? '',
                'sexual_orientation' => $_POST['sexual_orientation'] ?? '',
                'birth_date' => $_POST['birth_date'],
                // 'profile_picture_path' => '', // Handle file upload separately
                'errors_reg' => [] // Use a different key for registration errors to avoid conflicts
            ];

            // Validate input (basic example)
            if (empty($data['first_name'])) $data['errors_reg']['first_name_err'] = 'Please enter first name.';
            if (empty($data['last_name'])) $data['errors_reg']['last_name_err'] = 'Please enter last name.';
            if (empty($data['email'])) {
                $data['errors_reg']['email_err'] = 'Please enter email.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['errors_reg']['email_err'] = 'Invalid email format.';
            } elseif ($this->userModel->findUserByEmail($data['email'])) {
                $data['errors_reg']['email_err'] = 'Email is already taken.';
            }
            if (empty($data['password'])) {
                $data['errors_reg']['password_err'] = 'Please enter password.';
            } elseif (strlen($data['password']) < 6) {
                $data['errors_reg']['password_err'] = 'Password must be at least 6 characters.';
            }
            if (empty($data['confirm_password'])) {
                $data['errors_reg']['confirm_password_err'] = 'Please confirm password.';
            } elseif ($data['password'] !== $data['confirm_password']) {
                $data['errors_reg']['confirm_password_err'] = 'Passwords do not match.';
            }
            if (empty($data['birth_date'])) $data['errors_reg']['birth_date_err'] = 'Please enter your birth date.';
            // Add more validation for gender, orientation, etc.

            if (empty($data['errors_reg'])) {
                // Hash password
                $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT); // Correct key for model

                // Handle profile picture upload (simplified)
                $data['profile_picture_path'] = null; // Initialize
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                    $targetDir = BASE_PATH . "/public/img/profiles/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
                    $fileName = uniqid() . '_' . basename($_FILES["profile_picture"]["name"]);
                    $targetFilePath = $targetDir . $fileName;
                    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
                        $data['profile_picture_path'] = "img/profiles/" . $fileName;
                    } else {
                        $data['errors_reg']['profile_picture_err'] = "Sorry, there was an error uploading your file.";
                    }
                }


                if (empty($data['errors_reg']) && $this->userModel->register($data)) {
                    $_SESSION['success_message'] = 'You are registered! Please log in.';
                    \ErrorHandler::logAppAction("User registered: {$data['email']}");
                    $this->redirect('?form=login'); // Redirect to landing page, login tab with success message
                } else {
                    if(empty($data['errors_reg'])) $data['errors_reg']['register_err'] = 'Something went wrong. Please try again.';
                     // Pass errors and data back to the landing page's register form
                    $_SESSION['error_message_register'] = $data['errors_reg']['register_err'] ?? 'Please correct the errors below.';
                    $_SESSION['form_data_register'] = $data; // To repopulate form and show specific errors
                    $this->redirect('?form=register');
                }
            } else {
                // Pass errors and data back to the landing page's register form
                $_SESSION['error_message_register'] = 'Please correct the errors below.';
                $_SESSION['form_data_register'] = $data; // To repopulate form and show specific errors
                $this->redirect('?form=register');
            }

        } else {
            // If GET request to /register, redirect to landing page
            $this->redirect('?form=register');
        }
    }

    // Ajout de la méthode processRegister pour compatibilité
    public function processRegister() {
        return $this->register();
    }

    public function dashboard() {
        $this->ensureLoggedIn(); // This will redirect to '' (landing page) if not logged in
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId); // Fetch user data for potential use in the view

        // This is now the main page after login, showing the original homepage content.
        \ErrorHandler::logAppAction('User visited dashboard (authenticated homepage).');
        $data = [
            'title' => 'Welcome to Loove, ' . htmlspecialchars($user['first_name'] ?? 'User') . '!',
            'description' => 'Discover features, find matches, and connect.',
            'user' => $user // Pass user data to the view
        ];
        // Load the original homepage content as the dashboard view
        $this->view('home/index', $data); 
    }

    public function profile() {
        $this->ensureLoggedIn(); // This will redirect to '' (landing page) if not logged in
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            // Should not happen if ensureLoggedIn works
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('login');
            return;
        }
        
        // Remove password from user data before sending to view
        unset($user['password_hash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle profile update
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $userId,
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'description' => trim($_POST['description'] ?? ''),
                'preferences' => trim($_POST['preferences'] ?? ''),
                'relation_type' => trim($_POST['relation_type'] ?? ''),
                'interests' => trim($_POST['interests'] ?? ''),
                'errors' => []
            ];

            // Add validation as needed
            if (empty($data['first_name'])) $data['errors']['first_name_err'] = 'First name cannot be empty.';
            if (empty($data['last_name'])) $data['errors']['last_name_err'] = 'Last name cannot be empty.';

            // Handle profile picture update
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $targetDir = BASE_PATH . "/public/img/profiles/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
                $fileName = uniqid() . basename($_FILES["profile_picture"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
                    $data['profile_picture_path'] = "img/profiles/" . $fileName;
                    // Optionally, delete old picture
                    if ($user['profile_picture_path'] && file_exists(BASE_PATH . '/public/' . $user['profile_picture_path'])) {
                        // unlink(BASE_PATH . '/public/' . $user['profile_picture_path']);
                    }
                } else {
                    $data['errors']['profile_picture_err'] = "Sorry, there was an error uploading your file.";
                }
            } else {
                $data['profile_picture_path'] = $user['profile_picture_path']; // Keep old if not updated
            }


            if (empty($data['errors'])) {
                if ($this->userModel->updateProfile($data)) {
                    $_SESSION['success_message'] = 'Profile updated successfully.';
                    \ErrorHandler::logAppAction("User profile updated: {$userId}");
                    $this->redirect('profile'); // Redirect to refresh data
                } else {
                    $data['errors']['update_err'] = 'Could not update profile. Please try again.';
                    // Merge with existing user data for the view
                    $viewData = array_merge($user, $data);
                    $this->view('user/profile', $viewData);
                }
            } else {
                 // Merge with existing user data for the view
                $viewData = array_merge($user, $data);
                $this->view('user/profile', $viewData);
            }

        } else {
            $this->view('user/profile', (array)$user);
        }
    }
    
    public function logout() {
        $email = $_SESSION['user_email'] ?? 'Unknown';
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        // Unset form data from session if any
        unset($_SESSION['form_data_login']);
        unset($_SESSION['form_data_register']);
        unset($_SESSION['error_message_login']);
        unset($_SESSION['error_message_register']);
        unset($_SESSION['success_message']);


        session_destroy();
        \ErrorHandler::logAppAction("User logged out: {$email}");
        $this->redirect(''); // Redirect to landing page
    }

    private function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user'; // Assuming a 'role' column exists
    }

    protected function ensureLoggedIn() {
        if (!$this->isLoggedIn()) {
            // Store the intended URL before redirecting to login
            // $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Optional: for redirecting back after login
            
            $_SESSION['error_message'] = "You must be logged in to access this page.";
            $this->redirect(''); // Redirect to landing page
        }
    }
}
