<?php
require_once 'config/database.php';

try {
    $conn = getDbConnection();
    
    // Ajouter la colonne role si elle n'existe pas
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user'");
    
    // Créer le compte admin par défaut
    $admin_email = 'admin@loove.com';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Vérifier si l'admin existe déjà
    $check = $conn->prepare("SELECT id, role FROM users WHERE email = :email");
    $check->bindParam(':email', $admin_email);
    $check->execute();
    $existing_admin = $check->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_admin) {
        // Créer le compte admin
        $query = "INSERT INTO users (
            first_name, last_name, email, password, date_of_birth, age, 
            gender, location, occupation, bio, interests, height, phone, 
            relationship_status, is_active, is_premium, role, created_at, last_active
        ) VALUES (
            'Admin', 'Loove', :email, :password, '1990-01-01', 34,
            'other', 'Paris', 'Administrateur', 'Compte administrateur principal', 'Administration', 175, '',
            'single', 1, 1, 'admin', NOW(), NOW()
        )";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $admin_email);
        $stmt->bindParam(':password', $admin_password);
        
        if ($stmt->execute()) {
            echo "✅ Compte admin créé avec succès !<br>";
            echo "📧 Email: admin@loove.com<br>";
            echo "🔐 Mot de passe: admin123<br><br>";
        } else {
            echo "❌ Erreur lors de la création du compte admin<br>";
        }
    } else {
        // Mettre à jour le role si nécessaire
        if ($existing_admin['role'] !== 'admin') {
            $update_role = "UPDATE users SET role = 'admin' WHERE email = :email";
            $stmt_update = $conn->prepare($update_role);
            $stmt_update->bindParam(':email', $admin_email);
            $stmt_update->execute();
            echo "✅ Role admin mis à jour !<br>";
        } else {
            echo "ℹ️ Le compte admin existe déjà<br>";
        }
        echo "📧 Email: admin@loove.com<br>";
        echo "🔐 Mot de passe: admin123<br><br>";
    }
    
    echo "<a href='admin/login.php' style='background: #FF4458; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;'>🔐 Connexion Admin</a>";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
