<?php
/**
 * Message Controller - Handles messaging between users
 */
class MessageController extends BaseController {
    private $message_model;
    private $user_model;
    private $like_model;
    
    public function __construct() {
        parent::__construct();
        $this->message_model = new MessageModel();
        $this->user_model = new UserModel();
        $this->like_model = new LikeModel();
        
        // Add message-specific CSS
        $this->addStyle('/css/message.css');
        
        // Require login for all message actions
        $this->requireLogin();
    }
    
    // Default action - show conversations
    public function index() {
        $this->setTitle('Messages');
        
        $user_id = $_SESSION['user_id'];
        
        // Get all conversations
        $conversations = $this->message_model->getConversations($user_id);
        
        $this->render('message/index', [
            'conversations' => $conversations
        ]);
    }
    
    // View a conversation
    public function view() {
        $user_id = $_SESSION['user_id'];
        $other_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($other_user_id <= 0) {
            $this->setFlash('error', 'Invalid user ID.');
            $this->redirect('message');
        }
        
        // Check if users are matched (mutual like required to message)
        if (!$this->like_model->checkLike($user_id, $other_user_id) || 
            !$this->like_model->checkLike($other_user_id, $user_id)) {
            $this->setFlash('error', 'You can only message users you matched with.');
            $this->redirect('match');
        }
        
        // Get other user
        $other_user = $this->user_model->findById($other_user_id);
        
        if (!$other_user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('message');
        }
        
        // Set title
        $this->setTitle('Chat with ' . $other_user['name']);
        
        // Get conversation
        $messages = $this->message_model->getConversation($user_id, $other_user_id);
        
        // Mark messages as read
        $this->message_model->markAsRead($other_user_id, $user_id);
        
        $this->render('message/view', [
            'other_user' => $other_user,
            'messages' => $messages
        ]);
    }
    
    // Send a message
    public function send() {
        $user_id = $_SESSION['user_id'];
        $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
        $message_text = $_POST['message'] ?? '';
        
        if ($receiver_id <= 0) {
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid receiver ID.'
                ]);
            } else {
                $this->setFlash('error', 'Invalid receiver ID.');
                $this->redirect('message');
            }
        }
        
        if (empty($message_text)) {
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Message cannot be empty.'
                ]);
            } else {
                $this->setFlash('error', 'Message cannot be empty.');
                $this->redirect('message/view?id=' . $receiver_id);
            }
        }
        
        // Check if users are matched (mutual like required to message)
        if (!$this->like_model->checkLike($user_id, $receiver_id) || 
            !$this->like_model->checkLike($receiver_id, $user_id)) {
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'You can only message users you matched with.'
                ]);
            } else {
                $this->setFlash('error', 'You can only message users you matched with.');
                $this->redirect('match');
            }
        }
        
        // Send the message
        $message_id = $this->message_model->sendMessage($user_id, $receiver_id, $message_text);
        
        if ($message_id) {
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Message sent successfully!'
                ]);
            } else {
                $this->redirect('message/view?id=' . $receiver_id);
            }
        } else {
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to send message.'
                ]);
            } else {
                $this->setFlash('error', 'Failed to send message.');
                $this->redirect('message/view?id=' . $receiver_id);
            }
        }
    }
    
    // Get new messages (for AJAX polling)
    public function getNew() {
        $user_id = $_SESSION['user_id'];
        $other_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
        
        if ($other_user_id <= 0) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid user ID.'
            ]);
        }
        
        // Get messages newer than last_id
        $sql = "SELECT * FROM messages 
                WHERE ((sender_id = :user_id AND receiver_id = :other_user_id)
                OR (sender_id = :other_user_id AND receiver_id = :user_id))
                AND id > :last_id
                ORDER BY created_at ASC";
        
        $stmt = $this->message_model->query($sql, [
            'user_id' => $user_id,
            'other_user_id' => $other_user_id,
            'last_id' => $last_id
        ]);
        
        $messages = $stmt->fetchAll();
        
        // Mark messages as read
        $this->message_model->markAsRead($other_user_id, $user_id);
        
        $this->jsonResponse([
            'success' => true,
            'messages' => $messages
        ]);
    }
}
