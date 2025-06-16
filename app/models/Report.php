<?php
namespace App\Models;

use Model;
use PDO;

class Report extends Model {
    protected $table = 'reports';

    public function __construct() {
        parent::__construct();
    }

    // Example: Create a report
    public function createReport($reporter_id, $reported_user_id, $reason, $details = null) {
        $sql = "INSERT INTO {$this->table} (reporter_user_id, reported_user_id, reason, details, status, reported_at) 
                VALUES (:reporter_id, :reported_user_id, :reason, :details, 'pending', NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':reporter_id', $reporter_id, PDO::PARAM_INT);
        $stmt->bindParam(':reported_user_id', $reported_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
        $stmt->bindParam(':details', $details, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Example: Get all reports (for admin)
    public function getAllReports($status = null) {
        $sql = "SELECT r.*, 
                       reporter.email as reporter_email, 
                       reported.email as reported_user_email 
                FROM {$this->table} r
                JOIN users reporter ON r.reporter_user_id = reporter.id
                JOIN users reported ON r.reported_user_id = reported.id";
        if ($status) {
            $sql .= " WHERE r.status = :status";
        }
        $sql .= " ORDER BY r.reported_at DESC";
        
        $stmt = $this->db->prepare($sql);
        if ($status) {
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countPending() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = 'pending'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
