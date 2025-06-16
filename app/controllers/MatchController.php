<?php
/**
 * Match Controller - Handles likes and matches
 */
class MatchController extends BaseController {
    private $like_model;
    private $user_model;
    
    public function __construct() {
        parent::__construct();
        $this->like_model = new LikeModel();
        $this->user_model = new UserModel();
        
        // Add match-specific CSS
        $this->addStyle('/css/match.css');
        
        // Require login for all match actions
        $this->requireLogin();
    }
    
    // Default action - show matches
    public function index() {
        $this->setTitle('My Matches');
        
        $user_id = $_SESSION['user_id'];
        
        // Get all matches
        $matches = $this->like_model->getMatches($user_id);
        
        $this->render('match/index', [
            'matches' => $matches
        ]);
    }
    
    // Like a user
    public function like() {
        $user_id = $_SESSION['user_id'];
        $liked_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        
        if ($liked_user_id > 0) {
            // Check if already liked
            if (!$this->like_model->checkLike($user_id, $liked_user_id)) {
                // Add like
                $this->like_model->likeUser($user_id, $liked_user_id);
                
                // Check if mutual like (match)
                $mutual_like = $this->like_model->checkLike($liked_user_id, $user_id);
                
                if ($mutual_like) {
                    // It's a match!
                    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                        $this->jsonResponse([
                            'success' => true,
                            'message' => 'It\'s a match!',
                            'is_match' => true
                        ]);
                    } else {
                        $this->setFlash('success', 'It\'s a match! You can now message each other.');
                        $this->redirect('match');
                    }
                } else {
                    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                        $this->jsonResponse([
                            'success' => true,
                            'message' => 'User liked successfully!'
                        ]);
                    } else {
                        $this->setFlash('success', 'User liked successfully!');
                        $this->redirect('search/discover');
                    }
                }
            } else {
                if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'You already liked this user.'
                    ]);
                } else {
                    $this->setFlash('error', 'You already liked this user.');
                    $this->redirect('search/discover');
                }
            }
        } else {
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid user ID.'
                ]);
            } else {
                $this->setFlash('error', 'Invalid user ID.');
                $this->redirect('search/discover');
            }
        }
    }
    
    // Unlike a user
    public function unlike() {
        $user_id = $_SESSION['user_id'];
        $liked_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        
        if ($liked_user_id > 0) {
            // Remove like
            $this->like_model->unlikeUser($user_id, $liked_user_id);
            
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'User unliked successfully!'
                ]);
            } else {
                $this->setFlash('success', 'User unliked successfully!');
                $this->redirect('profile?id=' . $liked_user_id);
            }
        } else {
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid user ID.'
                ]);
            } else {
                $this->setFlash('error', 'Invalid user ID.');
                $this->redirect('match');
            }
        }
    }
    
    // View who likes you
    public function likedBy() {
        $this->setTitle('Who Likes Me');
        
        $user_id = $_SESSION['user_id'];
        
        // Get users who like the current user
        $admirers = $this->like_model->getUserLikes($user_id);
        
        $this->render('match/liked_by', [
            'admirers' => $admirers
        ]);
    }
}
