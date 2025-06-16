<?php
/**
 * Message Model - Handles messages between users
 */
class MessageModel extends Model {
    protected $table = 'messages';
    
    // Send a message
    public function sendMessage($sender_id, $receiver_id, $message) {
        return $this->create([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'is_read' => 0
        ]);
    }
    
    // Get conversation between two users
    public function getConversation($user1_id, $user2_id, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (sender_id = :user1_id AND receiver_id = :user2_id) 
                OR (sender_id = :user2_id AND receiver_id = :user1_id) 
                ORDER BY created_at ASC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Mark messages as read
    public function markAsRead($sender_id, $receiver_id) {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1 
                WHERE sender_id = :sender_id AND receiver_id = :receiver_id AND is_read = 0";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id
        ]);
    }
    
    // Get unread messages count
    public function getUnreadCount($user_id) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE receiver_id = :user_id AND is_read = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch();
        
        return $result['count'];
    }
    
    // Get all conversations for a user
    public function getConversations($user_id) {
        $sql = "SELECT 
                    u.id, u.name, u.profile_photo,
                    m.message, m.created_at, m.is_read,
                    (SELECT COUNT(*) FROM {$this->table} 
                     WHERE receiver_id = :user_id AND sender_id = u.id AND is_read = 0) as unread_count
                FROM users u
                JOIN (
                    SELECT 
                        CASE 
                            WHEN sender_id = :user_id THEN receiver_id
                            ELSE sender_id
                        END as other_user_id,
                        MAX(created_at) as max_date
                    FROM {$this->table}
                    WHERE sender_id = :user_id OR receiver_id = :user_id
                    GROUP BY other_user_id
                ) latest ON u.id = latest.other_user_id
                JOIN {$this->table} m ON (
                    (m.sender_id = :user_id AND m.receiver_id = u.id) OR
                    (m.sender_id = u.id AND m.receiver_id = :user_id)
                ) AND m.created_at = latest.max_date
                ORDER BY m.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
