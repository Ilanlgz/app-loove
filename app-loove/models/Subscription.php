<?php

class Subscription {
    private $db;
    private $table = 'subscriptions';

    public function __construct($database) {
        $this->db = $database;
    }

    public function createSubscription($userId, $planId, $startDate, $endDate) {
        $query = "INSERT INTO " . $this->table . " (user_id, plan_id, start_date, end_date) VALUES (:user_id, :plan_id, :start_date, :end_date)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':plan_id', $planId);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        return $stmt->execute();
    }

    public function getSubscription($userId) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSubscription($userId, $planId, $endDate) {
        $query = "UPDATE " . $this->table . " SET plan_id = :plan_id, end_date = :end_date WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':plan_id', $planId);
        $stmt->bindParam(':end_date', $endDate);
        return $stmt->execute();
    }

    public function deleteSubscription($userId) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }
}