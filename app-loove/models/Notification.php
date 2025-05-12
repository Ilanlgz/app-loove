<?php

class Notification {
    private $id;
    private $userId;
    private $message;
    private $createdAt;
    private $isRead;

    public function __construct($userId, $message) {
        $this->userId = $userId;
        $this->message = $message;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->isRead = false;
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function isRead() {
        return $this->isRead;
    }

    public function markAsRead() {
        $this->isRead = true;
    }

    public function save() {
        // Logic to save notification to the database
    }

    public static function getUserNotifications($userId) {
        // Logic to retrieve notifications for a specific user from the database
    }

    public static function deleteNotification($id) {
        // Logic to delete a notification from the database
    }
}