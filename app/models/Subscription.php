<?php
namespace App\Models;

use Model;
use PDO;

class Subscription extends Model {
    protected $table = 'subscriptions'; // e.g., user_subscriptions

    public function __construct() {
        parent::__construct();
    }

    // Example: Create or update a user's subscription
    public function setUserSubscription($user_id, $plan_id, $startDate, $endDate, $status = 'active', $payment_id = null) {
        // This could be an INSERT or UPDATE based on whether the user has an active sub
        $sql = "INSERT INTO {$this->table} (user_id, plan_id, start_date, end_date, status, payment_id, created_at) 
                VALUES (:user_id, :plan_id, :start_date, :end_date, :status, :payment_id, NOW())
                ON DUPLICATE KEY UPDATE 
                plan_id = VALUES(plan_id), start_date = VALUES(start_date), end_date = VALUES(end_date), status = VALUES(status), payment_id = VALUES(payment_id), updated_at = NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':plan_id', $plan_id, PDO::PARAM_INT); // Assuming you have a 'subscription_plans' table
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':payment_id', $payment_id);
        
        return $stmt->execute();
    }

    // Example: Get a user's active subscription
    public function getUserActiveSubscription($user_id) {
        $sql = "SELECT s.*, sp.name as plan_name, sp.features 
                FROM {$this->table} s
                JOIN subscription_plans sp ON s.plan_id = sp.id
                WHERE s.user_id = :user_id AND s.status = 'active' AND s.end_date >= CURDATE()
                ORDER BY s.end_date DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
