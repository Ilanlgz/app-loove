<?php
/**
 * User Model - Handles all user-related database operations
 */
class UserModel extends Model {
    protected $table = 'users';
    
    // Register a new user
    public function register($data) {
        // Hash the password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default role and status
        $data['role'] = $data['role'] ?? 'user';
        $data['status'] = $data['status'] ?? 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    // Verify login credentials
    public function login($email, $password) {
        $user = $this->findByField('email', $email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login time
            $this->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);
            
            return $user;
        }
        
        return false;
    }
    
    // Get users by gender preference
    public function findMatchesByPreference($user_id, $limit = 10) {
        $user = $this->findById($user_id);
        
        if (!$user) {
            return [];
        }
        
        // Get preference
        $gender = $user['gender'];
        $preference = $user['preference'] ?? 'both';
        
        $sql = "SELECT * FROM {$this->table} WHERE id != :user_id";
        
        if ($preference === 'male') {
            $sql .= " AND gender = 'male'";
        } elseif ($preference === 'female') {
            $sql .= " AND gender = 'female'";
        }
        
        // Exclude already liked or disliked profiles
        $sql .= " AND id NOT IN (
            SELECT liked_user_id FROM likes WHERE user_id = :user_id
        )";
        
        $sql .= " LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Search users by criteria
    public function search($criteria, $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($criteria['gender'])) {
            $sql .= " AND gender = :gender";
            $params['gender'] = $criteria['gender'];
        }
        
        if (!empty($criteria['min_age']) && is_numeric($criteria['min_age'])) {
            $sql .= " AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= :min_age";
            $params['min_age'] = $criteria['min_age'];
        }
        
        if (!empty($criteria['max_age']) && is_numeric($criteria['max_age'])) {
            $sql .= " AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) <= :max_age";
            $params['max_age'] = $criteria['max_age'];
        }
        
        if (!empty($criteria['location'])) {
            $sql .= " AND location LIKE :location";
            $params['location'] = '%' . $criteria['location'] . '%';
        }
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            if (in_array($key, ['limit', 'offset', 'min_age', 'max_age'])) {
                $stmt->bindValue(":{$key}", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":{$key}", $value, PDO::PARAM_STR);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Update profile
    public function updateProfile($user_id, $data) {
        // If password is being updated, hash it
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Don't update password if empty
            unset($data['password']);
        }
        
        return $this->update($user_id, $data);
    }
    
    // Upload profile photo
    public function updateProfilePhoto($user_id, $photo_path) {
        return $this->update($user_id, ['profile_photo' => $photo_path]);
    }
}
