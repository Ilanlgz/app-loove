<?php
// edit.php - View for editing user profiles

// Include necessary files
require_once '../../config/config.php';
require_once '../../models/Profile.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /app-loove/public/auth/login.php');
    exit();
}

// Initialize profile model
$profileModel = new Profile();

// Fetch user profile data
$userId = $_SESSION['user_id'];
$profileData = $profileModel->getProfileByUserId($userId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedData = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'gender' => $_POST['gender'],
        'orientation' => $_POST['orientation'],
        'birthdate' => $_POST['birthdate'],
        'bio' => $_POST['bio'],
        'interests' => $_POST['interests'],
        'profile_picture' => $_FILES['profile_picture']['name'] ?? $profileData['profile_picture']
    ];

    // Update profile
    if ($profileModel->updateProfile($userId, $updatedData)) {
        // Handle file upload if a new picture is uploaded
        if (!empty($_FILES['profile_picture']['name'])) {
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], '../../public/uploads/' . $updatedData['profile_picture']);
        }
        header('Location: /app-loove/public/profile/view.php');
        exit();
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/app-loove/public/assets/css/main.css">
    <title>Modifier le Profil</title>
</head>
<body>
    <header>
        <h1>Modifier votre Profil</h1>
    </header>
    <main>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="first_name">Prénom:</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($profileData['first_name']) ?>" required>

            <label for="last_name">Nom:</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($profileData['last_name']) ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($profileData['email']) ?>" required>

            <label for="gender">Genre:</label>
            <select id="gender" name="gender" required>
                <option value="male" <?= $profileData['gender'] === 'male' ? 'selected' : '' ?>>Homme</option>
                <option value="female" <?= $profileData['gender'] === 'female' ? 'selected' : '' ?>>Femme</option>
                <option value="other" <?= $profileData['gender'] === 'other' ? 'selected' : '' ?>>Autre</option>
            </select>

            <label for="orientation">Orientation:</label>
            <input type="text" id="orientation" name="orientation" value="<?= htmlspecialchars($profileData['orientation']) ?>" required>

            <label for="birthdate">Date de Naissance:</label>
            <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($profileData['birthdate']) ?>" required>

            <label for="bio">Bio:</label>
            <textarea id="bio" name="bio" required><?= htmlspecialchars($profileData['bio']) ?></textarea>

            <label for="interests">Intérêts:</label>
            <input type="text" id="interests" name="interests" value="<?= htmlspecialchars($profileData['interests']) ?>" required>

            <label for="profile_picture">Photo de Profil:</label>
            <input type="file" id="profile_picture" name="profile_picture">

            <button type="submit">Mettre à jour le Profil</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 App Loove</p>
    </footer>
</body>
</html>