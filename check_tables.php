<?php
require_once 'config/database.php';

try {
    $conn = getDbConnection();
    
    // Vérifier les tables
    $tables = ['conversations', 'messages'];
    foreach ($tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() > 0) {
            echo "✅ Table '$table' existe<br>";
            
            // Afficher la structure
            $structure = $conn->query("DESCRIBE $table");
            while ($column = $structure->fetch(PDO::FETCH_ASSOC)) {
                echo "&nbsp;&nbsp;- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
            }
        } else {
            echo "❌ Table '$table' manquante<br>";
        }
        echo "<br>";
    }
    
    echo "<a href='messages.php'>🔙 Retour aux messages</a>";
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
