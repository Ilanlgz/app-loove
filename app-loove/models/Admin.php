<?php

class Admin {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function createUser($data) {
        // Code to create a new user in the database
    }

    public function deleteUser($userId) {
        // Code to delete a user from the database
    }

    public function activateUser($userId) {
        // Code to activate a user account
    }

    public function deactivateUser($userId) {
        // Code to deactivate a user account
    }

    public function reportUser($userId, $reason) {
        // Code to report a user for inappropriate behavior
    }

    public function getUserReports() {
        // Code to retrieve user reports
    }

    public function manageSubscriptions($userId, $action) {
        // Code to manage user subscriptions
    }

    public function getAllUsers() {
        // Code to retrieve all users from the database
    }
}