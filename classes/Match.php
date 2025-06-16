<?php
require_once __DIR__ . '/../config/database.php';

class MatchSystem {
    private $conn;
    private $likes_table = "likes";
    private $matches_table = "matches";

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Liker un utilisateur
    public function likeUser($from_user_id, $to_user_id) {
        // Vérifier si le like existe déjà
        if ($this->hasLiked($from_user_id, $to_user_id)) {
            return false;
        }

        // Ajouter le like
        $query = "INSERT INTO " . $this->likes_table . " 
                  SET from_user_id = :from_user_id, to_user_id = :to_user_id, 
                      action = 'like', created_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':from_user_id', $from_user_id);
        $stmt->bindParam(':to_user_id', $to_user_id);
        
        if ($stmt->execute()) {
            // Vérifier si c'est un match mutuel
            if ($this->hasLiked($to_user_id, $from_user_id)) {
                $this->createMatch($from_user_id, $to_user_id);
                return 'match';
            }
            return 'like';
        }
        return false;
    }

    // Passer un utilisateur
    public function passUser($from_user_id, $to_user_id) {
        if ($this->hasLiked($from_user_id, $to_user_id)) {
            return false;
        }

        $query = "INSERT INTO " . $this->likes_table . " 
                  SET from_user_id = :from_user_id, to_user_id = :to_user_id, 
                      action = 'pass', created_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':from_user_id', $from_user_id);
        $stmt->bindParam(':to_user_id', $to_user_id);
        
        return $stmt->execute();
    }

    // Vérifier si un utilisateur a déjà liké/passé
    public function hasLiked($from_user_id, $to_user_id) {
        $query = "SELECT id FROM " . $this->likes_table . " 
                  WHERE from_user_id = :from_user_id AND to_user_id = :to_user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':from_user_id', $from_user_id);
        $stmt->bindParam(':to_user_id', $to_user_id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Créer un match
    private function createMatch($user1_id, $user2_id) {
        $query = "INSERT INTO " . $this->matches_table . " 
                  SET user1_id = :user1_id, user2_id = :user2_id, matched_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user1_id', $user1_id);
        $stmt->bindParam(':user2_id', $user2_id);
        
        return $stmt->execute();
    }

    // Obtenir les matches d'un utilisateur
    public function getUserMatches($user_id) {
        $query = "SELECT m.*, 
                         CASE 
                            WHEN m.user1_id = :user_id1 THEN u2.first_name
                            ELSE u1.first_name
                         END as match_name,
                         CASE 
                            WHEN m.user1_id = :user_id2 THEN u2.profile_picture
                            ELSE u1.profile_picture
                         END as match_picture,
                         CASE 
                            WHEN m.user1_id = :user_id3 THEN u2.id
                            ELSE u1.id
                         END as match_id
                  FROM " . $this->matches_table . " m
                  LEFT JOIN users u1 ON m.user1_id = u1.id
                  LEFT JOIN users u2 ON m.user2_id = u2.id
                  WHERE m.user1_id = :user_id4 OR m.user2_id = :user_id5
                  ORDER BY m.matched_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id1', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id3', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id4', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id5', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtenir les utilisateurs pour découvrir (qui n'ont pas été likés/passés)
    public function getDiscoverUsers($user_id, $limit = 10, $offset = 0) {
        // Récupérer le genre de l'utilisateur actuel
        $current_user_query = "SELECT gender FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($current_user_query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Déterminer le genre à afficher selon les préférences
        $target_gender = '';
        if ($current_user && $current_user['gender'] === 'male') {
            $target_gender = 'female'; // Les hommes voient les femmes
        } else if ($current_user && $current_user['gender'] === 'female') {
            $target_gender = 'male'; // Les femmes voient les hommes
        }
        
        // Construire la requête avec filtre de genre
        $gender_condition = $target_gender ? "AND u.gender = :target_gender" : "";
        
        $query = "SELECT u.id, u.first_name, u.last_name, u.age, u.location, 
                         u.profile_picture, u.bio, u.interests, u.occupation,
                         u.gender, u.height, u.relationship_status, u.phone,
                         u.photos,
                         TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as calculated_age,
                         u.last_active, u.is_premium, u.created_at
                  FROM users u
                  WHERE u.id != :user_id 
                  AND u.is_active = 1
                  AND u.email NOT LIKE '%@example.com'
                  $gender_condition
                  AND u.id NOT IN (
                      SELECT l.to_user_id 
                      FROM " . $this->likes_table . " l 
                      WHERE l.from_user_id = :user_id2
                  )
                  ORDER BY u.last_active DESC, RAND()
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
        if ($target_gender) {
            $stmt->bindParam(':target_gender', $target_gender, PDO::PARAM_STR);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ne plus générer de profils automatiquement
        // Les utilisateurs verront seulement les vrais profils
        
        return $users;
    }
}
?>
