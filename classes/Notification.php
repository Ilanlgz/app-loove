<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                data JSON,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_read (is_read),
                INDEX idx_type (type)
            )";
            
            $this->conn->exec($query);
        } catch (PDOException $e) {
            error_log("Erreur création table notifications: " . $e->getMessage());
        }
    }
    
    public function create($user_id, $type, $title, $message, $data = null) {
        try {
            $query = "INSERT INTO notifications (user_id, type, title, message, data) 
                      VALUES (:user_id, :type, :title, :message, :data)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':data', json_encode($data));
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur création notification: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUnreadCount($user_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM notifications 
                      WHERE user_id = :user_id AND is_read = FALSE";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getNotifications($user_id, $limit = 20) {
        try {
            $query = "SELECT * FROM notifications 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function markAsRead($notification_id, $user_id) {
        try {
            $query = "UPDATE notifications SET is_read = TRUE 
                      WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $notification_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Méthodes utilitaires pour créer des notifications spécifiques
    public function newMatch($user_id, $match_user_name) {
        return $this->create(
            $user_id,
            'match',
            'Nouveau match !',
            "Vous avez un nouveau match avec {$match_user_name} !",
            ['match_user_name' => $match_user_name]
        );
    }
    
    public function newMessage($user_id, $sender_name) {
        return $this->create(
            $user_id,
            'message',
            'Nouveau message',
            "{$sender_name} vous a envoyé un message",
            ['sender_name' => $sender_name]
        );
    }
    
    public function newSuperLike($user_id, $sender_name) {
        return $this->create(
            $user_id,
            'super_like',
            'Super Like reçu !',
            "{$sender_name} vous a envoyé un Super Like !",
            ['sender_name' => $sender_name]
        );
    }
}
?>
