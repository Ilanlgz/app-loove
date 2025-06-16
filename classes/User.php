<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $date_of_birth;
    public $age;
    public $gender;
    public $phone;
    public $location;
    public $occupation;
    public $bio;
    public $interests;
    public $height;
    public $relationship_status;
    public $profile_picture;
    public $is_premium;
    public $is_active;
    public $is_verified;
    public $created_at;
    public $last_active;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Créer un nouvel utilisateur
    public function create($userData) {
        try {
            // Ajouter les colonnes manquantes si nécessaire
            $this->ensureColumnsExist();
            
            $query = "INSERT INTO users (
                first_name, last_name, email, password, date_of_birth, age, 
                gender, location, occupation, bio, interests, height, phone, 
                relationship_status, is_active, is_premium, created_at, last_active
            ) VALUES (
                :first_name, :last_name, :email, :password, :date_of_birth, :age,
                :gender, :location, :occupation, :bio, :interests, :height, :phone,
                :relationship_status, :is_active, :is_premium, :created_at, :last_active
            )";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($userData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            if ($stmt->execute()) {
                $userId = $this->conn->lastInsertId();
                error_log("Utilisateur créé avec succès, ID: " . $userId);
                return $userId;
            }
            
            error_log("Échec de l'exécution de la requête d'insertion");
            return false;
        } catch (PDOException $e) {
            error_log("Erreur création utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    private function ensureColumnsExist() {
        try {
            // Ajouter les colonnes premium si elles n'existent pas
            $this->conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_premium BOOLEAN DEFAULT FALSE");
            $this->conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS premium_expires_at TIMESTAMP NULL");
            $this->conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS photos TEXT");
        } catch (PDOException $e) {
            // Ignorer les erreurs si les colonnes existent déjà
        }
    }
    
    // Vérifier si l'email existe déjà
    public function emailExists() {
        $query = "SELECT id, email, password, first_name, last_name, age, gender, location, 
                         occupation, bio, interests, height, relationship_status, profile_picture, 
                         is_premium, is_active, is_verified, last_active 
                  FROM " . $this->table_name . " 
                  WHERE email = :email 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->age = $row['age'];
            $this->gender = $row['gender'];
            $this->location = $row['location'];
            $this->occupation = $row['occupation'];
            $this->bio = $row['bio'];
            $this->interests = $row['interests'];
            $this->height = $row['height'];
            $this->relationship_status = $row['relationship_status'];
            $this->profile_picture = $row['profile_picture'];
            $this->is_premium = $row['is_premium'];
            $this->is_active = $row['is_active'];
            $this->is_verified = $row['is_verified'];
            $this->last_active = $row['last_active'];
            
            return true;
        }

        return false;
    }

    // Mettre à jour la dernière activité
    public function updateLastActive($userId) {
        try {
            $query = "UPDATE users SET last_active = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur mise à jour activité: " . $e->getMessage());
            return false;
        }
    }

    // Obtenir tous les utilisateurs
    public function getAllUsers($limit = 20) {
        $query = "SELECT id, first_name, last_name, age, location, profile_picture, 
                         bio, interests, last_active 
                  FROM " . $this->table_name . " 
                  WHERE is_active = 1 
                  ORDER BY last_active DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Rechercher des utilisateurs
    public function searchUsers($keyword, $location = '', $age_min = 18, $age_max = 100) {
        $query = "SELECT id, first_name, last_name, age, location, profile_picture, 
                         bio, interests, last_active 
                  FROM " . $this->table_name . " 
                  WHERE is_active = 1 
                  AND (first_name LIKE :keyword OR last_name LIKE :keyword OR bio LIKE :keyword OR interests LIKE :keyword)
                  AND age BETWEEN :age_min AND :age_max";
        
        if (!empty($location)) {
            $query .= " AND location LIKE :location";
        }
        
        $query .= " ORDER BY last_active DESC LIMIT 50";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->bindParam(':age_min', $age_min, PDO::PARAM_INT);
        $stmt->bindParam(':age_max', $age_max, PDO::PARAM_INT);
        
        if (!empty($location)) {
            $location = "%{$location}%";
            $stmt->bindParam(':location', $location);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
