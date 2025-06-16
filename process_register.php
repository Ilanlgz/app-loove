<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';

// Debug: vérifier que le formulaire est bien soumis
error_log("POST data reçu: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $location = trim($_POST['location']);
    $occupation = trim($_POST['occupation'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    $height = !empty($_POST['height']) ? (int)$_POST['height'] : null;
    $phone = trim($_POST['phone'] ?? '');
    $relationship_status = $_POST['relationship_status'] ?? 'single';
    
    $errors = [];
    
    // Validation
    if (empty($first_name)) {
        $errors[] = "Le prénom est requis.";
    }
    
    if (empty($last_name)) {
        $errors[] = "Le nom est requis.";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($date_of_birth)) {
        $errors[] = "La date de naissance est requise.";
    } else {
        $birthDate = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        if ($age < 18) {
            $errors[] = "Vous devez avoir au moins 18 ans pour vous inscrire.";
        }
    }
    
    if (empty($gender)) {
        $errors[] = "Le genre est requis.";
    }
    
    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        try {
            $conn = getDbConnection();
            $checkEmail = "SELECT id FROM users WHERE email = :email";
            $stmt = $conn->prepare($checkEmail);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Cet email est déjà utilisé.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de connexion à la base de données.";
        }
    }
    
    // Si pas d'erreurs, créer l'utilisateur
    if (empty($errors)) {
        try {
            $user = new User();
            $userId = $user->create([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'date_of_birth' => $date_of_birth,
                'age' => $age,
                'gender' => $gender,
                'location' => $location,
                'occupation' => $occupation,
                'bio' => $bio,
                'interests' => $interests,
                'height' => $height,
                'phone' => $phone,
                'relationship_status' => $relationship_status,
                'is_active' => 1,
                'is_premium' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'last_active' => date('Y-m-d H:i:s')
            ]);
            
            if ($userId) {
                // Debug: confirmer la création
                error_log("Utilisateur créé avec ID: " . $userId);
                
                // Connecter automatiquement l'utilisateur
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $userId;
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"] = $last_name;
                $_SESSION["email"] = $email;
                
                // Message de bienvenue
                $_SESSION['success_message'] = "Bienvenue sur Loove, " . $first_name . " ! Votre compte a été créé avec succès.";
                
                // Debug: confirmer avant redirection
                error_log("Redirection vers main.php pour l'utilisateur: " . $first_name);
                
                // Rediriger vers la page d'accueil
                header("location: main.php");
                exit;
            } else {
                error_log("Erreur: userId est false");
                $errors[] = "Erreur lors de la création du compte.";
            }
        } catch (Exception $e) {
            error_log("Exception lors de l'inscription: " . $e->getMessage());
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
    
    // S'il y a des erreurs, les stocker en session et rediriger
    if (!empty($errors)) {
        error_log("Erreurs d'inscription: " . print_r($errors, true));
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_data'] = $_POST;
        header("location: register.php");
        exit;
    }
} else {
    error_log("Redirection car pas de POST");
    header("location: register.php");
    exit;
}
?>
