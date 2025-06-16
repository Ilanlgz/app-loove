<?php
session_start();
require_once 'config/database.php';

$error_message = "";
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        try {
            $conn = getDbConnection();
            
            // Récupérer l'utilisateur par email
            $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Vérifier si c'est un admin qui essaie de se connecter comme utilisateur
                if (isset($user['role']) && $user['role'] === 'admin') {
                    $error_message = "Les comptes administrateurs doivent utiliser le panel admin.";
                } else {
                    // Connexion réussie pour utilisateur normal
                    $_SESSION["loggedin"] = true;
                    $_SESSION["user_id"] = $user['id'];
                    $_SESSION["first_name"] = $user['first_name'];
                    $_SESSION["last_name"] = $user['last_name'];
                    $_SESSION["email"] = $user['email'];
                    
                    // Mettre à jour la dernière activité
                    $updateQuery = "UPDATE users SET last_active = NOW() WHERE id = :user_id";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                    $updateStmt->execute();
                    
                    if ($is_ajax) {
                        echo json_encode([
                            'success' => true,
                            'message' => "Bon retour parmi nous, " . $user['first_name'] . " !",
                            'redirect' => 'main.php'
                        ]);
                        exit;
                    } else {
                        $_SESSION['success_message'] = "Bon retour parmi nous, " . $user['first_name'] . " !";
                        header("location: main.php");
                        exit;
                    }
                }
            } else {
                $error_message = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur de connexion à la base de données.";
        }
    }
    
    // Gestion des erreurs
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => $error_message
        ]);
        exit;
    } else {
        $_SESSION['login_error'] = $error_message;
        header("location: login.php");
        exit;
    }
} else {
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Méthode non autorisée'
        ]);
        exit;
    } else {
        header("location: login.php");
        exit;
    }
}
?>
