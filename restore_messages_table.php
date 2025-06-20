<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDbConnection();
        
        // Supprimer l'ancienne table
        $conn->exec("DROP TABLE IF EXISTS messages");
        
        // Recréer la table avec les ANCIENS noms de colonnes qui marchaient
        $createMessages = "
        CREATE TABLE messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            from_user_id INT NOT NULL,
            to_user_id INT NOT NULL,
            message_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) DEFAULT 0
        )";
        
        $conn->exec($createMessages);
        
        $message = "✅ Table messages restaurée avec l'ancienne structure !";
        
    } catch (Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Restaurer Messages - Loove</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .container { background: #f8f9fa; padding: 30px; border-radius: 10px; }
        button { background: #FF4458; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; width: 100%; }
        .message { padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; font-weight: bold; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Restaurer Table Messages</h1>
        <p>Cette action va recréer la table messages avec l'ancienne structure qui marchait.</p>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <button type="submit">🔄 Restaurer l'ancienne structure</button>
        </form>
        
        <p style="margin-top: 20px; text-align: center;">
            <a href="main.php" style="color: #FF4458;">← Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>
