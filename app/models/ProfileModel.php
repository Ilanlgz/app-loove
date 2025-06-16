<?php
/**
 * Profile Model - Handles user profile data
 */
class ProfileModel extends Model {
    protected $table = 'profiles';
    
    // Create or update a profile
    public function saveProfile($user_id, $data) {
        // Check if profile exists
        $profile = $this->findByField('user_id', $user_id);
        
        if ($profile) {
            // Update existing profile
            return $this->update($profile['id'], $data);
        } else {
            // Create new profile
            $data['user_id'] = $user_id;
            return $this->create($data);
        }
    }
    
    // Get a user's profile
    public function getProfile($user_id) {
        return $this->findByField('user_id', $user_id);
    }
    
    // Get user profile with user data
    public function getCompleteProfile($user_id) {
        $sql = "SELECT u.*, p.* 
                FROM users u 
                LEFT JOIN {$this->table} p ON u.id = p.user_id 
                WHERE u.id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetch();
    }
    
    // Add profile photo
    public function addPhoto($user_id, $photo_path) {
        $profile = $this->findByField('user_id', $user_id);
        
        if ($profile) {
            // Get existing photos
            $photos = json_decode($profile['photos'] ?? '[]', true);
            $photos[] = $photo_path;
            
            // Update photos
            return $this->update($profile['id'], [
                'photos' => json_encode($photos)
            ]);
        }
        
        return false;
    }
    
    // Remove profile photo
    public function removePhoto($user_id, $photo_index) {
        $profile = $this->findByField('user_id', $user_id);
        
        if ($profile) {
            // Get existing photos
            $photos = json_decode($profile['photos'] ?? '[]', true);
            
            // Check if index exists
            if (isset($photos[$photo_index])) {
                // Remove the photo
                unset($photos[$photo_index]);
                $photos = array_values($photos); // Reindex array
                
                // Update photos
                return $this->update($profile['id'], [
                    'photos' => json_encode($photos)
                ]);
            }
        }
        
        return false;
    }
}
