<?php
require_once __DIR__ . '/../config/database.php';

class SuperLike {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS super_likes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                from_user_id INT NOT NULL,
                to_user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_super_like (from_user_id, to_user_id),
                INDEX idx_from_user (from_user_id),
                INDEX idx_to_user (to_user_id)
            )";
            
            $this->conn->exec($query);
        } catch (PDOException $e) {
            error_log("Erreur création table super_likes: " . $e->getMessage());
        }
    }
    
    public function sendSuperLike($from_user_id, $to_user_id) {
        try {
            // Vérifier si l'utilisateur est premium
            if (!$this->isPremiumUser($from_user_id)) {
                return ['success' => false, 'message' => 'Fonctionnalité réservée aux membres Premium'];
            }
            
            // Vérifier les Super Likes disponibles aujourd'hui
            if (!$this->hasAvailableSuperLikes($from_user_id)) {
                return ['success' => false, 'message' => 'Vous avez utilisé tous vos Super Likes pour aujourd\'hui'];
            }
            
            $query = "INSERT IGNORE INTO super_likes (from_user_id, to_user_id) 
                      VALUES (:from_user_id, :to_user_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':from_user_id', $from_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':to_user_id', $to_user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Super Like envoyé !'];
            }
            
            return ['success' => false, 'message' => 'Erreur lors de l\'envoi du Super Like'];
        } catch (PDOException $e) {
            error_log("Erreur Super Like: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur système'];
        }
    }
    
    private function isPremiumUser($user_id) {
        try {
            $query = "SELECT is_premium, premium_expires_at FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !$user['is_premium']) {
                return false;
            }
            
            // Vérifier si l'abonnement n'a pas expiré
            if ($user['premium_expires_at'] && strtotime($user['premium_expires_at']) < time()) {
                return false;
            }
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function hasAvailableSuperLikes($user_id) {
        try {
            $today = date('Y-m-d');
            $query = "SELECT COUNT(*) as count FROM super_likes 
                      WHERE from_user_id = :user_id 
                      AND DATE(created_at) = :today";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':today', $today);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] < 5; // 5 Super Likes par jour
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getSuperLikesReceived($user_id) {
        try {
            $query = "SELECT sl.*, u.first_name, u.profile_picture, u.age, u.location 
                      FROM super_likes sl
                      JOIN users u ON sl.from_user_id = u.id
                      WHERE sl.to_user_id = :user_id
                      ORDER BY sl.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
