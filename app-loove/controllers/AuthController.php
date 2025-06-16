<?php
class AuthController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->addCss('/css/auth.css');
    }
    
    public function handleRequest() {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        if (strpos($request_uri, '/register') !== false) {
            $this->register();
        } elseif (strpos($request_uri, '/login') !== false) {
            $this->login();
        } elseif (strpos($request_uri, '/logout') !== false) {
            $this->logout();
        } else {
            $this->login(); // Default to login
        }
    }
    
    private function register() {
        $this->setTitle('Register - Loove Dating App');
        
        $errors = [];
        $success = false;
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate inputs
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Perform validation
            if (empty($name)) {
                $errors[] = "Name is required";
            }
            
            if (empty($email)) {
                $errors[] = "Email is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Please enter a valid email address";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            } elseif (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters long";
            }
            
            if ($password !== $confirm_password) {
                $errors[] = "Passwords do not match";
            }
            
            // If no errors, proceed with registration
            if (empty($errors)) {
                $db = getDatabaseConnection();
                
                // Check if email already exists
                $check_sql = "SELECT id FROM users WHERE email = ?";
                $check_stmt = $db->prepare($check_sql);
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = "Email already exists. Please use a different email or login.";
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $insert_sql = "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())";
                    $insert_stmt = $db->prepare($insert_sql);
                    $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
                    
                    if ($insert_stmt->execute()) {
                        $success = true;
                        // Redirect to login after successful registration
                        header("Location: /loove/app-loove/public/login?registered=1");
                        exit;
                    } else {
                        $errors[] = "Registration failed: " . $db->error;
                    }
                }
                
                $db->close();
            }
        }
        
        $this->render('auth/register', [
            'errors' => $errors,
            'success' => $success
        ]);
    }
    
    private function login() {
        $this->setTitle('Login - Loove Dating App');
        
        $errors = [];
        $registered = isset($_GET['registered']) && $_GET['registered'] == 1;
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Validate inputs
            if (empty($email) || empty($password)) {
                $errors[] = "Email and password are required";
            } else {
                $db = getDatabaseConnection();
                
                // Get the user with the provided email
                $sql = "SELECT id, name, email, password FROM users WHERE email = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        
                        // Redirection après connexion réussie
                        header("Location: /loove/app-loove/public/profile");
                        exit;
                    } else {
                        $errors[] = "Invalid email or password";
                    }
                } else {
                    $errors[] = "Invalid email or password";
                }
                
                $db->close();
            }
        }
        
        $this->render('auth/login', [
            'errors' => $errors,
            'registered' => $registered
        ]);
    }
    
    private function logout() {
        // Destroy the session
        session_unset();
        session_destroy();
        
        // Redirect to login page
        header("Location: /loove/app-loove/public/login");
        exit;
    }
}