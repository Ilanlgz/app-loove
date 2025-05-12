<?php
/**
 * Auth Controller - Handles authentication
 */
class AuthController extends BaseController {
    private $user_model;
    
    public function __construct() {
        parent::__construct();
        $this->user_model = new UserModel();
        
        // Add auth-specific CSS
        $this->addStyle('/css/auth.css');
    }
    
    // Default action
    public function index() {
        $this->login();
    }
    
    // Login page
    public function login() {
        // Check if already logged in
        if ($this->isLoggedIn()) {
            $this->redirect('profile');
        }
        
        $this->setTitle('Login');
        
        // Process login form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Validate input
            if (empty($email) || empty($password)) {
                $this->setFlash('error', 'Please enter both email and password.');
            } else {
                // Attempt login
                $user = $this->user_model->login($email, $password);
                
                if ($user) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    
                    // Redirect to profile
                    $this->redirect('profile');
                } else {
                    $this->setFlash('error', 'Invalid email or password.');
                }
            }
        }
        
        $this->render('auth/login');
    }
    
    // Registration page
    public function register() {
        // Check if already logged in
        if ($this->isLoggedIn()) {
            $this->redirect('profile');
        }
        
        $this->setTitle('Register');
        
        // Process registration form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $birthdate = $_POST['birthdate'] ?? '';
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required.';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format.';
            } elseif ($this->user_model->findByField('email', $email)) {
                $errors[] = 'Email is already registered.';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm_password) {
                $errors[] = 'Passwords do not match.';
            }
            
            if (empty($gender)) {
                $errors[] = 'Gender is required.';
            }
            
            if (empty($birthdate)) {
                $errors[] = 'Birthdate is required.';
            } else {
                // Calculate age
                $birthdate_obj = new DateTime($birthdate);
                $today = new DateTime();
                $age = $birthdate_obj->diff($today)->y;
                
                if ($age < 18) {
                    $errors[] = 'You must be at least 18 years old to register.';
                }
            }
            
            // If no errors, create user
            if (empty($errors)) {
                $user_id = $this->user_model->register([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'gender' => $gender,
                    'birthdate' => $birthdate
                ]);
                
                if ($user_id) {
                    // Create profile
                    $profile_model = new ProfileModel();
                    $profile_model->saveProfile($user_id, [
                        'about_me' => '',
                        'preference' => 'both', // Default preference
                        'location' => '',
                        'interests' => '[]',
                        'photos' => '[]'
                    ]);
                    
                    // Set success message
                    $this->setFlash('success', 'Registration successful! Please login.');
                    
                    // Redirect to login
                    $this->redirect('auth/login');
                } else {
                    $this->setFlash('error', 'Error creating account. Please try again.');
                }
            } else {
                // Set error message
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $this->render('auth/register');
    }
    
    // Logout
    public function logout() {
        // Destroy session
        session_unset();
        session_destroy();
        
        // Redirect to login
        $this->redirect('auth/login');
    }
    
    // Forgot password page
    public function forgotPassword() {
        $this->setTitle('Forgot Password');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                $this->setFlash('error', 'Please enter your email address.');
            } else {
                $user = $this->user_model->findByField('email', $email);
                
                if ($user) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Update user with reset token
                    $this->user_model->update($user['id'], [
                        'reset_token' => $token,
                        'reset_expiry' => $expiry
                    ]);
                    
                    // TODO: Send email with reset link
                    // For now, just display the token
                    $this->setFlash('success', 'Password reset link sent to your email.');
                } else {
                    $this->setFlash('error', 'Email not found.');
                }
            }
        }
        
        $this->render('auth/forgot_password');
    }
    
    // Reset password page
    public function resetPassword() {
        $this->setTitle('Reset Password');
        
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->setFlash('error', 'Invalid reset token.');
            $this->redirect('auth/login');
        }
        
        // Find user by token
        $user = $this->user_model->findByField('reset_token', $token);
        
        if (!$user || strtotime($user['reset_expiry']) < time()) {
            $this->setFlash('error', 'Invalid or expired reset token.');
            $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($password)) {
                $this->setFlash('error', 'Password is required.');
            } elseif (strlen($password) < 6) {
                $this->setFlash('error', 'Password must be at least 6 characters.');
            } elseif ($password !== $confirm_password) {
                $this->setFlash('error', 'Passwords do not match.');
            } else {
                // Update password
                $this->user_model->update($user['id'], [
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'reset_token' => null,
                    'reset_expiry' => null
                ]);
                
                $this->setFlash('success', 'Password reset successfully. Please login with your new password.');
                $this->redirect('auth/login');
            }
        }
        
        $this->render('auth/reset_password', ['token' => $token]);
    }
}
