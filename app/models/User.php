<?php

class User {
    private $conn;
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $date_of_birth;
    public $age;
    public $gender;
    public $location;
    public $occupation;
    public $bio;
    public $interests;
    public $height;
    public $phone;
    public $relationship_status;
    public $is_active;
    public $is_premium;
    public $role;
    
    public function __construct() {
        $this->conn = getDbConnection();
        $this->createTable();
    }
    
    private function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            date_of_birth DATE,
            age INT,
            gender ENUM('male', 'female', 'other'),
            location VARCHAR(255),
            occupation VARCHAR(255),
            bio TEXT,
            interests TEXT,
            height INT,
            phone VARCHAR(20),
            relationship_status VARCHAR(50) DEFAULT 'single',
            profile_picture VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            is_premium BOOLEAN DEFAULT FALSE,
            premium_expires_at TIMESTAMP NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->conn->exec($query);
    }
    
    public function login($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->updateLastActive($user['id']);
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function register($data) {
        try {
            // Validation
            $errors = $this->validateRegistration($data);
            if (!empty($errors)) {
                return false;
            }
            
            // Calculer l'âge
            $birthDate = new DateTime($data['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            
            $query = "INSERT INTO users (
                first_name, last_name, email, password, date_of_birth, age, 
                gender, location, occupation, bio, interests, height, 
                relationship_status, created_at, last_active
            ) VALUES (
                :first_name, :last_name, :email, :password, :date_of_birth, :age,
                :gender, :location, :occupation, :bio, :interests, :height,
                :relationship_status, NOW(), NOW()
            )";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
            $stmt->bindParam(':age', $age, PDO::PARAM_INT);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':location', $data['location']);
            $stmt->bindParam(':occupation', $data['occupation']);
            $stmt->bindParam(':bio', $data['bio']);
            $stmt->bindParam(':interests', $data['interests']);
            $stmt->bindParam(':height', $data['height'], PDO::PARAM_INT);
            $stmt->bindParam(':relationship_status', 'single');
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function findById($id) {
        try {
            $query = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateProfile($userId, $data) {
        try {
            $query = "UPDATE users SET 
                first_name = :first_name, last_name = :last_name, email = :email, 
                date_of_birth = :date_of_birth, age = :age, gender = :gender, 
                location = :location, occupation = :occupation, bio = :bio, 
                interests = :interests, height = :height, phone = :phone, 
                relationship_status = :relationship_status, updated_at = NOW()
            WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
            $stmt->bindParam(':age', $data['age'], PDO::PARAM_INT);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':location', $data['location']);
            $stmt->bindParam(':occupation', $data['occupation']);
            $stmt->bindParam(':bio', $data['bio']);
            $stmt->bindParam(':interests', $data['interests']);
            $stmt->bindParam(':height', $data['height'], PDO::PARAM_INT);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':relationship_status', $data['relationship_status']);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function adminLogin($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email AND role = 'admin' AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                $this->updateLastActive($admin['id']);
                return $admin;
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAllUsers() {
        try {
            $query = "SELECT * FROM users";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAdminStats() {
        try {
            $query = "SELECT 
                COUNT(*) as total_users, 
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN is_premium = 1 THEN 1 ELSE 0 END) as premium_users
            FROM users";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function activatePremium($userId, $planType) {
        try {
            $expiresAt = null;
            
            // Déterminer la date d'expiration en fonction du type de plan
            if ($planType == 'monthly') {
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 month'));
            } elseif ($planType == 'yearly') {
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));
            }
            
            $query = "UPDATE users SET 
                is_premium = 1, 
                premium_expires_at = :expires_at
            WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function validateRegistration($data) {
        $errors = [];
        
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || 
            empty($data['password']) || empty($data['date_of_birth']) || empty($data['gender']) || 
            empty($data['location'])) {
            $errors[] = "Tous les champs marqués d'un * sont obligatoires.";
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format d'email invalide.";
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        
        if (strlen($data['password']) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        }
        
        // Vérifier l'âge
        if (!empty($data['date_of_birth'])) {
            $birthDate = new DateTime($data['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            
            if ($age < 18) {
                $errors[] = "Vous devez avoir au moins 18 ans pour vous inscrire.";
            }
        }
        
        // Vérifier si l'email existe déjà
        $checkEmail = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($checkEmail);
        $stmt->bindParam(':email', $data['email']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Cet email est déjà utilisé.";
        }
        
        return $errors;
    }
    
    private function updateLastActive($userId) {
        $query = "UPDATE users SET last_active = NOW() WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
?>
