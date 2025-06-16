<?php

class Match {
    private $userId1;
    private $userId2;
    private $matchDate;

    public function __construct($userId1, $userId2) {
        $this->userId1 = $userId1;
        $this->userId2 = $userId2;
        $this->matchDate = date('Y-m-d H:i:s');
    }

    public function getUserId1() {
        return $this->userId1;
    }

    public function getUserId2() {
        return $this->userId2;
    }

    public function getMatchDate() {
        return $this->matchDate;
    }

    public function saveMatch($dbConnection) {
        $stmt = $dbConnection->prepare("INSERT INTO matches (user_id_1, user_id_2, match_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $this->userId1, $this->userId2, $this->matchDate);
        return $stmt->execute();
    }

    public static function getMatchesByUserId($userId, $dbConnection) {
        $stmt = $dbConnection->prepare("SELECT user_id_2, match_date FROM matches WHERE user_id_1 = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}