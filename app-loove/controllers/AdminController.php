<?php

class AdminController {
    private $adminModel;

    public function __construct() {
        // Include the Admin model
        require_once '../models/Admin.php';
        $this->adminModel = new Admin();
    }

    public function viewUsers() {
        // Fetch all users from the model
        $users = $this->adminModel->getAllUsers();
        require '../views/admin/users.php';
    }

    public function viewReports() {
        // Fetch all reports from the model
        $reports = $this->adminModel->getAllReports();
        require '../views/admin/reports.php';
    }

    public function activateUser($userId) {
        // Activate a user account
        $this->adminModel->activateUser($userId);
        header('Location: /admin/users');
    }

    public function deactivateUser($userId) {
        // Deactivate a user account
        $this->adminModel->deactivateUser($userId);
        header('Location: /admin/users');
    }

    public function deleteUser($userId) {
        // Delete a user account
        $this->adminModel->deleteUser($userId);
        header('Location: /admin/users');
    }

    public function handleReport($reportId, $action) {
        // Handle user report (e.g., resolve or dismiss)
        if ($action === 'resolve') {
            $this->adminModel->resolveReport($reportId);
        } else {
            $this->adminModel->dismissReport($reportId);
        }
        header('Location: /admin/reports');
    }
}