<?php
require_once __DIR__ . '/../config/database.php';

class Message {
    private $conn;
    private $messages_table = "messages";
    private $conversations_table = "conversations";

    public function __construct() {
        $this->conn = getDbConnection();
        $this->createTablesIfNotExists();
    }

    // Créer les tables si elles n'existent pas (version corrigée)
    private function createTablesIfNotExists() {
        try {
            // Vérifier si les tables existent
            $check = $this->conn->query("SHOW TABLES LIKE 'conversations'");
            if ($check && $check->rowCount() > 0) {
                return; // Tables déjà existantes
            }
            
            // Créer la table conversations
            $conversationsQuery = "CREATE TABLE IF NOT EXISTS conversations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user1_id INT NOT NULL,
                user2_id INT NOT NULL,
                last_message TEXT,
                last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user1 (user1_id),
                INDEX idx_user2 (user2_id)
            )";
            
            // Créer la table messages
            $messagesQuery = "CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                from_user_id INT NOT NULL,
                to_user_id INT NOT NULL,
                message_text TEXT NOT NULL,
                sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_read BOOLEAN DEFAULT FALSE,
                INDEX idx_conversation (conversation_id),
                INDEX idx_from_user (from_user_id),
                INDEX idx_to_user (to_user_id)
            )";
            
            $this->conn->exec($conversationsQuery);
            $this->conn->exec($messagesQuery);
            
        } catch (PDOException $e) {
            error_log("Erreur création tables messages: " . $e->getMessage());
        }
    }

    // Créer ou récupérer une conversation
    public function getOrCreateConversation($user1_id, $user2_id) {
        // Vérifier si une conversation existe déjà
        $query = "SELECT * FROM " . $this->conversations_table . " 
                  WHERE (user1_id = :user1_id AND user2_id = :user2_id) 
                     OR (user1_id = :user2_id_alt AND user2_id = :user1_id_alt)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id_alt', $user2_id, PDO::PARAM_INT);
        $stmt->bindParam(':user1_id_alt', $user1_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$conversation) {
            // Créer une nouvelle conversation
            $query = "INSERT INTO " . $this->conversations_table . " 
                      (user1_id, user2_id, created_at) 
                      VALUES (:user1_id, :user2_id, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
            $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $conversation_id = $this->conn->lastInsertId();
        } else {
            $conversation_id = $conversation['id'];
        }
        
        return $conversation_id;
    }

    // Envoyer un message
    public function sendMessage($from_user_id, $to_user_id, $message_text) {
        try {
            $conversation_id = $this->getOrCreateConversation($from_user_id, $to_user_id);
            
            $query = "INSERT INTO " . $this->messages_table . " 
                      (conversation_id, from_user_id, to_user_id, message_text, sent_at) 
                      VALUES (:conversation_id, :from_user_id, :to_user_id, :message_text, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
            $stmt->bindParam(':from_user_id', $from_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':to_user_id', $to_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':message_text', $message_text, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $message_id = $this->conn->lastInsertId();
                
                // Mettre à jour la conversation
                $this->updateConversationLastMessage($conversation_id);
                
                return $message_id;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Erreur sendMessage: " . $e->getMessage());
            return false;
        }
    }

    // Récupérer les messages d'une conversation
    public function getConversationMessages($user1_id, $user2_id, $limit = 50) {
        $query = "SELECT m.*, u.first_name, u.profile_picture
                  FROM " . $this->messages_table . " m
                  LEFT JOIN users u ON m.from_user_id = u.id
                  WHERE (m.from_user_id = :user1_id AND m.to_user_id = :user2_id)
                     OR (m.from_user_id = :user2_id_alt AND m.to_user_id = :user1_id_alt)
                  ORDER BY m.sent_at ASC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id_alt', $user2_id, PDO::PARAM_INT);
        $stmt->bindParam(':user1_id_alt', $user1_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer toutes les conversations d'un utilisateur
    public function getUserConversations($user_id) {
        $query = "SELECT c.*, 
                         CASE 
                            WHEN c.user1_id = :user_id1 THEN u2.first_name
                            ELSE u1.first_name
                         END as other_user_name,
                         CASE 
                            WHEN c.user1_id = :user_id2 THEN u2.profile_picture
                            ELSE u1.profile_picture
                         END as other_user_picture,
                         CASE 
                            WHEN c.user1_id = :user_id3 THEN u2.id
                            ELSE u1.id
                         END as other_user_id,
                         c.last_message,
                         c.last_message_at
                  FROM " . $this->conversations_table . " c
                  LEFT JOIN users u1 ON c.user1_id = u1.id
                  LEFT JOIN users u2 ON c.user2_id = u2.id
                  WHERE c.user1_id = :user_id4 OR c.user2_id = :user_id5
                  ORDER BY c.last_message_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id1', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id3', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id4', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id5', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Rechercher des utilisateurs
    public function searchUsers($search_term, $current_user_id) {
        $query = "SELECT id, first_name, last_name, profile_picture, location, is_premium
                  FROM users 
                  WHERE (first_name LIKE :search1 OR last_name LIKE :search2) 
                  AND id != :current_user_id 
                  AND is_active = 1
                  AND email NOT LIKE '%@example.com'
                  ORDER BY first_name ASC
                  LIMIT 20";
        
        $search_param = '%' . $search_term . '%';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':search1', $search_param, PDO::PARAM_STR);
        $stmt->bindParam(':search2', $search_param, PDO::PARAM_STR);
        $stmt->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Vérifier si deux utilisateurs ont matché
    public function hasMatched($user1_id, $user2_id) {
        $query = "SELECT COUNT(*) as count FROM matches 
                  WHERE (user1_id = :user1_id AND user2_id = :user2_id) 
                     OR (user1_id = :user2_id_alt AND user2_id = :user1_id_alt)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id_alt', $user2_id, PDO::PARAM_INT);
        $stmt->bindParam(':user1_id_alt', $user1_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Mettre à jour le dernier message de la conversation
    private function updateConversationLastMessage($conversation_id) {
        try {
            $query = "UPDATE " . $this->conversations_table . " 
                      SET last_message = (
                          SELECT message_text 
                          FROM " . $this->messages_table . " 
                          WHERE conversation_id = :conversation_id 
                          ORDER BY sent_at DESC 
                          LIMIT 1
                      ),
                      last_message_at = NOW()
                      WHERE id = :conversation_id_update";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
            $stmt->bindParam(':conversation_id_update', $conversation_id, PDO::PARAM_INT);
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Erreur updateConversationLastMessage: " . $e->getMessage());
        }
    }
}
?>
