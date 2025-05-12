<?php
/**
 * Like Model - Handles likes between users
 */
class LikeModel extends Model {
    protected $table = 'likes';
    
    // Like a user
    public function likeUser($user_id, $liked_user_id) {
        return $this->create([
            'user_id' => $user_id,
            'liked_user_id' => $liked_user_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Unlike a user
    public function unlikeUser($user_id, $liked_user_id) {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id AND liked_user_id = :liked_user_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $user_id,
            'liked_user_id' => $liked_user_id
        ]);
    }
    
    // Check if user likes another user
    public function checkLike($user_id, $liked_user_id) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND liked_user_id = :liked_user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'liked_user_id' => $liked_user_id
        ]);
        
        return $stmt->fetch() ? true : false;
    }
    
    // Get users who like a specific user
    public function getUserLikes($user_id) {
        $sql = "SELECT u.* 
                FROM users u 
                JOIN {$this->table} l ON u.id = l.user_id 
                WHERE l.liked_user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    }
    
    // Get users liked by a specific user
    public function getLikedUsers($user_id) {
        $sql = "SELECT u.* 
                FROM users u 
                JOIN {$this->table} l ON u.id = l.liked_user_id 
                WHERE l.user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    }
    
    // Get mutual likes (matches)
    public function getMatches($user_id) {
        $sql = "SELECT u.* 
                FROM users u 
                JOIN {$this->table} l1 ON u.id = l1.liked_user_id 
                JOIN {$this->table} l2 ON u.id = l2.user_id 
                WHERE l1.user_id = :user_id 
                AND l2.liked_user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    }
}
