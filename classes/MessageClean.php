<?php
class Message {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->db = getDbConnection();
    }

    public function getConversationMessages($user1_id, $user2_id) {
        $stmt = $this->db->prepare("SELECT m.*, u.first_name, u.last_name, u.profile_picture FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.sent_at ASC");
        $stmt->execute([$user1_id, $user2_id, $user2_id, $user1_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendMessage($sender_id, $receiver_id, $content) {
        $stmt = $this->db->prepare("INSERT INTO messages (sender_id, receiver_id, content, sent_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$sender_id, $receiver_id, $content]);
    }

    public function getUserConversations($user_id) {
        $stmt = $this->db->prepare("SELECT CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as other_user_id, u.first_name, u.last_name, u.profile_picture as other_user_picture, CONCAT(u.first_name, ' ', u.last_name) as other_user_name, m.content as last_message FROM messages m JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END = u.id) WHERE m.sender_id = ? OR m.receiver_id = ? GROUP BY other_user_id ORDER BY m.sent_at DESC");
        $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
