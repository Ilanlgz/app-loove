<?php
require_once 'config/database.php';

try {
    $conn = getDbConnection();
    
    // Supprimer tous les utilisateurs générés (qui ont un email @example.com)
    $deleteGenerated = "DELETE FROM users WHERE email LIKE '%@example.com'";
    $stmt = $conn->prepare($deleteGenerated);
    $deletedUsers = $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    // Nettoyer les likes orphelins
    $cleanLikes = "DELETE FROM likes WHERE from_user_id NOT IN (SELECT id FROM users) OR to_user_id NOT IN (SELECT id FROM users)";
    $stmt = $conn->prepare($cleanLikes);
    $stmt->execute();
    $cleanedLikes = $stmt->rowCount();
    
    // Nettoyer les matches orphelins
    $cleanMatches = "DELETE FROM matches WHERE user1_id NOT IN (SELECT id FROM users) OR user2_id NOT IN (SELECT id FROM users)";
    $stmt = $conn->prepare($cleanMatches);
    $stmt->execute();
    $cleanedMatches = $stmt->rowCount();
    
    // Nettoyer les conversations orphelines
    $cleanConversations = "DELETE FROM conversations WHERE user1_id NOT IN (SELECT id FROM users) OR user2_id NOT IN (SELECT id FROM users)";
    $stmt = $conn->prepare($cleanConversations);
    $stmt->execute();
    $cleanedConversations = $stmt->rowCount();
    
    // Nettoyer les messages orphelins
    $cleanMessages = "DELETE FROM messages WHERE from_user_id NOT IN (SELECT id FROM users) OR to_user_id NOT IN (SELECT id FROM users)";
    $stmt = $conn->prepare($cleanMessages);
    $stmt->execute();
    $cleanedMessages = $stmt->rowCount();
    
    echo "✅ Nettoyage terminé !<br><br>";
    echo "📊 Résultats :<br>";
    echo "- <strong>$deletedCount</strong> profils générés supprimés<br>";
    echo "- <strong>$cleanedLikes</strong> likes orphelins nettoyés<br>";
    echo "- <strong>$cleanedMatches</strong> matches orphelins nettoyés<br>";
    echo "- <strong>$cleanedConversations</strong> conversations orphelines nettoyées<br>";
    echo "- <strong>$cleanedMessages</strong> messages orphelins nettoyés<br><br>";
    
    // Afficher les utilisateurs restants
    $remainingUsers = "SELECT id, first_name, last_name, email, created_at FROM users ORDER BY created_at DESC";
    $stmt = $conn->prepare($remainingUsers);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👥 Utilisateurs restants (" . count($users) . ") :<br>";
    if (empty($users)) {
        echo "<em>Aucun utilisateur réel trouvé. Vous pouvez maintenant créer de nouveaux comptes !</em><br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Nom</th><th style='padding: 8px;'>Email</th><th style='padding: 8px;'>Créé le</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $user['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='padding: 8px;'>" . date('d/m/Y H:i', strtotime($user['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><br>";
    echo "<a href='main.php' style='background: #FF4458; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;'>🏠 Retour à l'accueil</a> ";
    echo "<a href='register.php' style='background: #34C759; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; margin-left: 10px;'>➕ Créer un nouveau compte</a>";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors du nettoyage : " . $e->getMessage();
}
?>
