<?php
require_once 'config/database.php';

try {
    $conn = getDbConnection();
    
    // Créer la table likes
    $likesQuery = "CREATE TABLE IF NOT EXISTS likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        action ENUM('like', 'pass') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_from_user (from_user_id),
        INDEX idx_to_user (to_user_id),
        UNIQUE KEY unique_like (from_user_id, to_user_id)
    )";
    
    // Créer la table matches
    $matchesQuery = "CREATE TABLE IF NOT EXISTS matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user1_id INT NOT NULL,
        user2_id INT NOT NULL,
        matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user1 (user1_id),
        INDEX idx_user2 (user2_id),
        UNIQUE KEY unique_match (user1_id, user2_id)
    )";
    
    $conn->exec($likesQuery);
    $conn->exec($matchesQuery);
    
    echo "✅ Tables créées avec succès !<br><br>";
    echo "📊 Tables créées :<br>";
    echo "- <strong>likes</strong> (pour les likes/passes)<br>";
    echo "- <strong>matches</strong> (pour les matches mutuels)<br><br>";
    echo "<a href='discover.php' style='background: #FF4458; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;'>🚀 Tester la page Découvrir</a>";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la création des tables : " . $e->getMessage();
}
?>
