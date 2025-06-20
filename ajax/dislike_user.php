<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

// Vérifier si l'ID de l'utilisateur est fourni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
    $disliked_user_id = (int)$_POST['user_id'];
    
    try {
        $conn = getDbConnection();
        
        // Vérifier si la table dislikes existe
        $stmt = $conn->query("SHOW TABLES LIKE 'dislikes'");
        if ($stmt->rowCount() === 0) {
            // Créer la table si elle n'existe pas
            $conn->exec("CREATE TABLE dislikes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                disliked_user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_dislike (user_id, disliked_user_id)
            )");
        }
        
        // Insérer le dislike
        $stmt = $conn->prepare("INSERT INTO dislikes (user_id, disliked_user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
        $stmt->execute([$current_user_id, $disliked_user_id]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}
?>
