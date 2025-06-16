<?php

class Report {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function createReport($userId, $reportedUserId, $reason) {
        $stmt = $this->db->prepare("INSERT INTO reports (user_id, reported_user_id, reason, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $userId, $reportedUserId, $reason);
        return $stmt->execute();
    }

    public function getReportsByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllReports() {
        $result = $this->db->query("SELECT * FROM reports");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteReport($reportId) {
        $stmt = $this->db->prepare("DELETE FROM reports WHERE id = ?");
        $stmt->bind_param("i", $reportId);
        return $stmt->execute();
    }
}