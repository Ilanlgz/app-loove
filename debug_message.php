<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    die("Non connecté");
}

try {
    $conn = getDbConnection();
    
    echo "<h3>Debug - Tables de messages</h3>";
    
    // Vérifier les tables
    $tables = ['conversations', 'messages'];
    foreach ($tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() > 0) {
            echo "✅ Table '$table' existe<br>";
            
            // Compter les enregistrements
            $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            echo "&nbsp;&nbsp;→ $count enregistrement(s)<br>";
            
            // Afficher les derniers enregistrements
            if ($count > 0) {
                $last = $conn->query("SELECT * FROM $table ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
                echo "&nbsp;&nbsp;→ Derniers enregistrements:<br>";
                foreach ($last as $record) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;" . json_encode($record) . "<br>";
                }
            }
        } else {
            echo "❌ Table '$table' manquante<br>";
        }
        echo "<br>";
    }
    
    // Test d'insertion
    echo "<h3>Test d'insertion</h3>";
    
    $user_id = $_SESSION["user_id"];
    echo "User ID actuel: $user_id<br>";
    
    // Créer une conversation test
    $testQuery = "INSERT INTO conversations (user1_id, user2_id, created_at) VALUES (:user1, :user2, NOW())";
    $stmt = $conn->prepare($testQuery);
    $stmt->bindParam(':user1', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user2', $user_id, PDO::PARAM_INT); // Test avec soi-même
    
    if ($stmt->execute()) {
        $conv_id = $conn->lastInsertId();
        echo "✅ Conversation test créée (ID: $conv_id)<br>";
        
        // Insérer un message test
        $msgQuery = "INSERT INTO messages (conversation_id, from_user_id, to_user_id, message_text, sent_at) VALUES (:conv, :from, :to, :msg, NOW())";
        $stmt = $conn->prepare($msgQuery);
        $stmt->bindParam(':conv', $conv_id, PDO::PARAM_INT);
        $stmt->bindParam(':from', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':to', $user_id, PDO::PARAM_INT);
        $test_message = "Message de test " . date('Y-m-d H:i:s');
        $stmt->bindParam(':msg', $test_message, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $msg_id = $conn->lastInsertId();
            echo "✅ Message test inséré (ID: $msg_id)<br>";
        } else {
            echo "❌ Erreur insertion message<br>";
        }
    } else {
        echo "❌ Erreur création conversation<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage();
}

echo "<br><a href='messages.php'>🔙 Retour aux messages</a>";
?>
