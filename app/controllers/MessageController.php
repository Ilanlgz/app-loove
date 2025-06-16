<?php
require_once BASE_PATH . '/app/controllers/BaseController.php';

class MessageController extends BaseController {
    public function __construct() {
        $this->requireAuth();
    }
    
    public function index() {
        $conversationId = (int)($_GET['conversation'] ?? 0);
        
        $this->view('messages/index', [
            'title' => 'Messages - Loove',
            'activeConversationId' => $conversationId
        ]);
    }
    
    public function send() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            return;
        }
        
        $convId = (int)($_POST['conversation_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        
        if ($convId <= 0 || empty($message)) {
            echo json_encode(['success' => false]);
            return;
        }
        
        // Simuler l'envoi de message
        echo json_encode(['success' => true]);
    }
}
?>
