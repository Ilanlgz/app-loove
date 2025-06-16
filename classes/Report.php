<?php
require_once __DIR__ . '/../config/database.php';

class Report {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
        $this->createTablesIfNotExists();
    }
    
    private function createTablesIfNotExists() {
        try {
            // Table des signalements
            $reportsQuery = "CREATE TABLE IF NOT EXISTS reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reporter_id INT NOT NULL,
                reported_id INT NOT NULL,
                reason VARCHAR(100) NOT NULL,
                description TEXT,
                status VARCHAR(20) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_reporter (reporter_id),
                INDEX idx_reported (reported_id),
                INDEX idx_status (status)
            )";
            
            // Table des blocages
            $blocksQuery = "CREATE TABLE IF NOT EXISTS blocks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                blocker_id INT NOT NULL,
                blocked_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_block (blocker_id, blocked_id),
                INDEX idx_blocker (blocker_id),
                INDEX idx_blocked (blocked_id)
            )";
            
            $this->conn->exec($reportsQuery);
            $this->conn->exec($blocksQuery);
            
        } catch (PDOException $e) {
            error_log("Erreur création tables report: " . $e->getMessage());
        }
    }
    
    public function reportUser($reporter_id, $reported_id, $reason, $description = '') {
        try {
            $query = "INSERT INTO reports (reporter_id, reported_id, reason, description) 
                      VALUES (:reporter_id, :reported_id, :reason, :description)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':reporter_id', $reporter_id, PDO::PARAM_INT);
            $stmt->bindParam(':reported_id', $reported_id, PDO::PARAM_INT);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':description', $description);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur signalement: " . $e->getMessage());
            return false;
        }
    }
    
    public function blockUser($blocker_id, $blocked_id) {
        try {
            $query = "INSERT IGNORE INTO blocks (blocker_id, blocked_id) 
                      VALUES (:blocker_id, :blocked_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':blocker_id', $blocker_id, PDO::PARAM_INT);
            $stmt->bindParam(':blocked_id', $blocked_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur blocage: " . $e->getMessage());
            return false;
        }
    }
    
    public function isBlocked($user1_id, $user2_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM blocks 
                      WHERE (blocker_id = :user1_id AND blocked_id = :user2_id) 
                         OR (blocker_id = :user2_id_alt AND blocked_id = :user1_id_alt)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
            $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
            $stmt->bindParam(':user2_id_alt', $user2_id, PDO::PARAM_INT);
            $stmt->bindParam(':user1_id_alt', $user1_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Erreur vérification blocage: " . $e->getMessage());
            return false;
        }
    }
}
?>
