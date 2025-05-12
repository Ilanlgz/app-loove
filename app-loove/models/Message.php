<?php

class Message {
    private $id;
    private $senderId;
    private $receiverId;
    private $content;
    private $timestamp;

    public function __construct($senderId, $receiverId, $content) {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->content = $content;
        $this->timestamp = date("Y-m-d H:i:s");
    }

    public function getId() {
        return $this->id;
    }

    public function getSenderId() {
        return $this->senderId;
    }

    public function getReceiverId() {
        return $this->receiverId;
    }

    public function getContent() {
        return $this->content;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }

    public function save() {
        // Code to save the message to the database
    }

    public static function getMessagesBetween($userId1, $userId2) {
        // Code to retrieve messages between two users from the database
    }
}