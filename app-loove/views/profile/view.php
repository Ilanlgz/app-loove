<?php
// view.php - Display a user's profile

// Include necessary files
require_once '../../config/database.php';
require_once '../../models/Profile.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Create a Profile object
$profile = new Profile($db);

// Get the user ID from the URL or session
$userId = isset($_GET['id']) ? $_GET['id'] : die('ERROR: User ID not found.');

// Fetch the profile data
$profileData = $profile->getProfileById($userId);

if ($profileData) {
    // Display the profile information
    echo "<h1>{$profileData['name']}'s Profile</h1>";
    echo "<img src='uploads/{$profileData['photo']}' alt='Profile Picture' />";
    echo "<p><strong>Email:</strong> {$profileData['email']}</p>";
    echo "<p><strong>Bio:</strong> {$profileData['bio']}</p>";
    echo "<p><strong>Interests:</strong> {$profileData['interests']}</p>";
    echo "<p><strong>Looking for:</strong> {$profileData['relationship_type']}</p>";
} else {
    echo "<p>Profile not found.</p>";
}
?>