<?php
require_once 'config/database.php';

class MatchSystem {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
    }
    
    public function createMatch($user1_id, $user2_id) {
        try {
            // Vérifier si le match existe déjà
            $stmt = $this->conn->prepare("
                SELECT id FROM matches 
                WHERE (user1_id = ? AND user2_id = ?) 
                OR (user1_id = ? AND user2_id = ?)
            ");
            $stmt->execute([$user1_id, $user2_id, $user2_id, $user1_id]);
            
            if ($stmt->fetch()) {
                return false; // Match déjà existant
            }
            
            // Créer le nouveau match
            $stmt = $this->conn->prepare("
                INSERT INTO matches (user1_id, user2_id, matched_at) 
                VALUES (?, ?, NOW())
            ");
            return $stmt->execute([$user1_id, $user2_id]);
            
        } catch (PDOException $e) {
            error_log("Erreur createMatch: " . $e->getMessage());
            return false;
        }
    }
    
    public function checkMutualLike($user1_id, $user2_id) {
        try {
            // Vérifier si les deux utilisateurs se sont likés mutuellement
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as mutual_likes FROM likes 
                WHERE ((liker_id = ? AND liked_id = ?) OR (liker_id = ? AND liked_id = ?))
                AND is_like = 1
            ");
            $stmt->execute([$user1_id, $user2_id, $user2_id, $user1_id]);
            $result = $stmt->fetch();
            
            return $result['mutual_likes'] >= 2;
            
        } catch (PDOException $e) {
            error_log("Erreur checkMutualLike: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserMatches($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT m.*, 
                       u.first_name, u.last_name, u.profile_picture, u.bio,
                       CASE 
                           WHEN m.user1_id = ? THEN u2.first_name
                           ELSE u1.first_name
                       END as match_first_name,
                       CASE 
                           WHEN m.user1_id = ? THEN u2.last_name
                           ELSE u1.last_name
                       END as match_last_name,
                       CASE 
                           WHEN m.user1_id = ? THEN u2.profile_picture
                           ELSE u1.profile_picture
                       END as match_profile_picture,
                       CASE 
                           WHEN m.user1_id = ? THEN u2.bio
                           ELSE u1.bio
                       END as match_bio,
                       CASE 
                           WHEN m.user1_id = ? THEN m.user2_id
                           ELSE m.user1_id
                       END as match_user_id
                FROM matches m
                LEFT JOIN users u1 ON m.user1_id = u1.id
                LEFT JOIN users u2 ON m.user2_id = u2.id
                WHERE m.user1_id = ? OR m.user2_id = ?
                ORDER BY m.matched_at DESC
            ");
            $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getUserMatches: " . $e->getMessage());
            return [];
        }
    }
    
    public function getDiscoverUsers($user_id, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.*, 
                       TIMESTAMPDIFF(YEAR, u.birth_date, CURDATE()) as calculated_age
                FROM users u 
                WHERE u.id != ? 
                AND u.role != 'admin'
                AND u.id NOT IN (
                    SELECT liked_id FROM likes WHERE liker_id = ?
                )
                AND u.id NOT IN (
                    SELECT user1_id FROM matches WHERE user2_id = ?
                    UNION
                    SELECT user2_id FROM matches WHERE user1_id = ?
                )
                ORDER BY RAND()
                LIMIT ?
            ");
            $stmt->execute([$user_id, $user_id, $user_id, $user_id, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getDiscoverUsers: " . $e->getMessage());
            return [];
        }
    }
    
    public function createMatchConversation($user1_id, $user2_id) {
        try {
            // Message automatique de match
            $welcome_message = "🎉 Félicitations ! Vous avez un match ! Commencez votre conversation...";
            
            $stmt = $this->conn->prepare("
                INSERT INTO messages (sender_id, receiver_id, content, sent_at, is_match_starter) 
                VALUES (?, ?, ?, NOW(), 1)
            ");
            
            return $stmt->execute([$user1_id, $user2_id, $welcome_message]);
            
        } catch (PDOException $e) {
            error_log("Erreur createMatchConversation: " . $e->getMessage());
            return false;
        }
    }
}
?>
