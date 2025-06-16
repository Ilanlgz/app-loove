<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$message = '';

try {
    $conn = getDbConnection();
    
    // Créer la table messages
    $createMessages = "
    CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        content TEXT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read TINYINT(1) DEFAULT 0
    )";
    
    $conn->exec($createMessages);
    
    // Créer la table likes  
    $createLikes = "
    CREATE TABLE IF NOT EXISTS likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        liked_user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (user_id, liked_user_id)
    )";
    
    $conn->exec($createLikes);
    
    $message = "✅ Tables créées avec succès !";
    
} catch (Exception $e) {
    $message = "❌ Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Création des tables - Loove</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .message { padding: 20px; border-radius: 8px; text-align: center; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn { background: #FF4458; color: white; padding: 15px 30px; border: none; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>🛠️ Création des tables pour Loove</h1>
    
    <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
        <?= $message ?>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="main.php" class="btn">← Retour à l'accueil</a>
        <a href="send_message_with_notification.php" class="btn">💬 Tester les messages</a>
    </div>
</body>
</html>
