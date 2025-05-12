<?php
/**
 * Profile Controller - Handles user profile operations
 */
class ProfileController extends BaseController {
    private $user_model;
    private $profile_model;
    
    public function __construct() {
        parent::__construct();
        $this->user_model = new UserModel();
        $this->profile_model = new ProfileModel();
        
        // Add profile-specific CSS
        $this->addStyle('/css/profile.css');
        
        // Require login for all profile actions
        $this->requireLogin();
    }
    
    // Default action - show profile
    public function index() {
        $this->view();
    }
    
    // View profile
    public function view() {
        $user_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];
        $this->setTitle('Profile');
        
        // Get complete profile data
        $profile = $this->profile_model->getCompleteProfile($user_id);
        
        // Check if viewing another user's profile
        $is_own_profile = $user_id === $_SESSION['user_id'];
        
        if (!$is_own_profile) {
            // Check if there's a like
            $like_model = new LikeModel();
            $has_liked = $like_model->checkLike($_SESSION['user_id'], $user_id);
            
            // Check if there's a mutual like (match)
            $mutual_like = $like_model->checkLike($user_id, $_SESSION['user_id']);
        }
        
        $this->render('profile/view', [
            'profile' => $profile,
            'is_own_profile' => $is_own_profile,
            'has_liked' => $has_liked ?? false,
            'is_match' => ($has_liked ?? false) && ($mutual_like ?? false)
        ]);
    }
    
    // Edit profile
    public function edit() {
        $this->setTitle('Edit Profile');
        
        // Get user data
        $user_id = $_SESSION['user_id'];
        $profile = $this->profile_model->getCompleteProfile($user_id);
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Prepare user data
            $user_data = [
                'name' => $_POST['name'] ?? '',
                'location' => $_POST['location'] ?? '',
                'preference' => $_POST['preference'] ?? 'both'
            ];
            
            // Prepare profile data
            $profile_data = [
                'about_me' => $_POST['about_me'] ?? '',
                'interests' => json_encode(
                    array_filter(
                        explode(',', $_POST['interests'] ?? '')
                    )
                ),
                'looking_for' => $_POST['looking_for'] ?? ''
            ];
            
            // Update user
            $this->user_model->update($user_id, $user_data);
            
            // Update profile
            $this->profile_model->saveProfile($user_id, $profile_data);
            
            $this->setFlash('success', 'Profile updated successfully!');
            $this->redirect('profile');
        }
        
        $this->render('profile/edit', [
            'profile' => $profile
        ]);
    }
    
    // Upload profile photo
    public function uploadPhoto() {
        $user_id = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            $file = $_FILES['photo'];
            
            // Validate file
            if (!in_array($file['type'], $allowed_types)) {
                $this->setFlash('error', 'Only JPG and PNG images are allowed.');
                $this->redirect('profile/photos');
            }
            
            if ($file['size'] > $max_size) {
                $this->setFlash('error', 'Maximum file size is 5MB.');
                $this->redirect('profile/photos');
            }
            
            // Generate unique filename
            $filename = uniqid() . '_' . $file['name'];
            $upload_path = UPLOAD_PATH . '/profiles/' . $filename;
            
            // Create directory if it doesn't exist
            if (!file_exists(UPLOAD_PATH . '/profiles')) {
                mkdir(UPLOAD_PATH . '/profiles', 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update profile photo
                $photo_url = '/uploads/profiles/' . $filename;
                
                if (isset($_POST['is_main']) && $_POST['is_main'] === '1') {
                    // Set as main profile photo
                    $this->user_model->updateProfilePhoto($user_id, $photo_url);
                } else {
                    // Add to photo gallery
                    $this->profile_model->addPhoto($user_id, $photo_url);
                }
                
                $this->setFlash('success', 'Photo uploaded successfully!');
            } else {
                $this->setFlash('error', 'Failed to upload photo.');
            }
        }
        
        $this->redirect('profile/photos');
    }
    
    // Manage photos
    public function photos() {
        $this->setTitle('My Photos');
        
        $user_id = $_SESSION['user_id'];
        $profile = $this->profile_model->getCompleteProfile($user_id);
        
        // Parse photos
        $photos = [];
        if (isset($profile['photos'])) {
            $photos = json_decode($profile['photos'], true) ?: [];
        }
        
        $this->render('profile/photos', [
            'profile' => $profile,
            'photos' => $photos
        ]);
    }
    
    // Remove photo
    public function removePhoto() {
        $user_id = $_SESSION['user_id'];
        $photo_index = isset($_GET['index']) ? intval($_GET['index']) : -1;
        
        if ($photo_index >= 0) {
            if ($this->profile_model->removePhoto($user_id, $photo_index)) {
                $this->setFlash('success', 'Photo removed successfully!');
            } else {
                $this->setFlash('error', 'Failed to remove photo.');
            }
        }
        
        $this->redirect('profile/photos');
    }
    
    // Account settings
    public function settings() {
        $this->setTitle('Account Settings');
        
        $user_id = $_SESSION['user_id'];
        $user = $this->user_model->findById($user_id);
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($current_password)) {
                $this->setFlash('error', 'Current password is required.');
            } elseif (!password_verify($current_password, $user['password'])) {
                $this->setFlash('error', 'Current password is incorrect.');
            } elseif (empty($new_password)) {
                $this->setFlash('error', 'New password is required.');
            } elseif (strlen($new_password) < 6) {
                $this->setFlash('error', 'New password must be at least 6 characters.');
            } elseif ($new_password !== $confirm_password) {
                $this->setFlash('error', 'Passwords do not match.');
            } else {
                // Update password
                $this->user_model->update($user_id, [
                    'password' => password_hash($new_password, PASSWORD_DEFAULT)
                ]);
                
                $this->setFlash('success', 'Password updated successfully!');
                $this->redirect('profile/settings');
            }
        }
        
        $this->render('profile/settings', [
            'user' => $user
        ]);
    }
}
