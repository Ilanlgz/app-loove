<?php
require_once 'config/database.php';

try {
    $conn = getDbConnection();
    
    // Table des conversations
    $conversationsQuery = "CREATE TABLE IF NOT EXISTS conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user1_id INT NOT NULL,
        user2_id INT NOT NULL,
        last_message TEXT,
        last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user1 (user1_id),
        INDEX idx_user2 (user2_id),
        UNIQUE KEY unique_conversation (user1_id, user2_id)
    )";
    
    // Table des messages
    $messagesQuery = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        message_text TEXT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE,
        INDEX idx_conversation (conversation_id),
        INDEX idx_from_user (from_user_id),
        INDEX idx_to_user (to_user_id)
    )";
    
    $conn->exec($conversationsQuery);
    $conn->exec($messagesQuery);
    
    echo "✅ Tables de messages créées avec succès !<br><br>";
    echo "📊 Tables créées :<br>";
    echo "- <strong>conversations</strong> (gestion des conversations)<br>";
    echo "- <strong>messages</strong> (stockage des messages)<br><br>";
    echo "<a href='messages.php' style='background: #FF4458; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;'>💬 Tester les messages</a>";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la création des tables : " . $e->getMessage();
}
?>
