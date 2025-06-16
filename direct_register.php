<?php
session_start();

$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config/database.php';
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $height = !empty($_POST['height']) ? (int)$_POST['height'] : null;
    $phone = '';
    $relationship_status = 'single';
    
    $errors = [];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($date_of_birth) || empty($gender) || empty($location)) {
        $errors[] = "Tous les champs marqués d'un * sont obligatoires.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    
    // Vérifier l'âge
    if (!empty($date_of_birth)) {
        $birth_date = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($birth_date)->y;
        
        if ($age < 18) {
            $errors[] = "Vous devez avoir au moins 18 ans pour vous inscrire.";
        }
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
            // S'assurer que les colonnes existent
            $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_premium BOOLEAN DEFAULT FALSE");
            $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS premium_expires_at TIMESTAMP NULL");
            $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS photos TEXT");
            
            $query = "INSERT INTO users (
                first_name, last_name, email, password, date_of_birth, age, 
                gender, location, occupation, bio, interests, height, phone, 
                relationship_status, is_active, is_premium, created_at, last_active
            ) VALUES (
                :first_name, :last_name, :email, :password, :date_of_birth, :age,
                :gender, :location, :occupation, :bio, :interests, :height, :phone,
                :relationship_status, 1, 0, NOW(), NOW()
            )";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT));
            $stmt->bindParam(':date_of_birth', $date_of_birth);
            $stmt->bindParam(':age', $age, PDO::PARAM_INT);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':occupation', $occupation);
            $stmt->bindParam(':bio', $bio);
            $stmt->bindParam(':interests', $interests);
            $stmt->bindParam(':height', $height, PDO::PARAM_INT);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':relationship_status', $relationship_status);
            
            if ($stmt->execute()) {
                $userId = $conn->lastInsertId();
                
                // Connecter automatiquement l'utilisateur
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $userId;
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"] = $last_name;
                $_SESSION["email"] = $email;
                
                // Message de bienvenue
                $_SESSION['success_message'] = "Bienvenue sur Loove, " . $first_name . " ! Votre compte a été créé avec succès.";
                
                // Redirection immédiate
                header("Location: main.php");
                exit();
            } else {
                $errors[] = "Erreur lors de la création du compte.";
            }
        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
    
    // S'il y a des erreurs, les afficher
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Même style que register.php mais adapté */
        :root {
            --primary-color: #FF4458;
            --primary-dark: #E73C4E;
            --secondary-color: #FD5068;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --white: #FFFFFF;
            --border-color: #E8E8E8;
            --error: #FF3B30;
            --success: #34C759;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FF4458 0%, #FD5068 50%, #FF6B7D 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: 0 32px 64px rgba(255, 68, 88, 0.2);
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
        }

        .form-title {
            color: var(--text-primary);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-error {
            background: rgba(255, 59, 48, 0.1);
            color: #000000;
            border: 1px solid rgba(255, 59, 48, 0.2);
            font-weight: 600;
        }

        .alert-success {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
            border: 1px solid rgba(52, 199, 89, 0.2);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(255, 68, 88, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 18px 24px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 24px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .login-link {
            text-align: center;
            color: var(--text-secondary);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="form-title">Créer un compte</h2>
        
        <?php if($is_logged_in): ?>
            <div class="alert" style="background: rgba(255, 193, 7, 0.1); color: #856404; border: 1px solid rgba(255, 193, 7, 0.2);">
                <i class="fas fa-info-circle"></i>
                <span>Vous êtes déjà connecté. Créer un nouveau compte vous déconnectera du compte actuel.</span>
            </div>
        <?php endif; ?>

        <?php if($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="first_name">Prénom *</label>
                    <input type="text" id="first_name" name="first_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name">Nom *</label>
                    <input type="text" id="last_name" name="last_name" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe *</label>
                    <input type="password" id="password" name="password" class="form-input" required minlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirmer *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="date_of_birth">Date de naissance *</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="gender">Genre *</label>
                    <select id="gender" name="gender" class="form-input" required>
                        <option value="">Sélectionner...</option>
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="location">Ville *</label>
                <input type="text" id="location" name="location" class="form-input" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="occupation">Profession</label>
                    <input type="text" id="occupation" name="occupation" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label" for="height">Taille (cm)</label>
                    <input type="number" id="height" name="height" class="form-input" min="140" max="220">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="interests">Centres d'intérêt</label>
                <input type="text" id="interests" name="interests" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label" for="bio">Présentation</label>
                <textarea id="bio" name="bio" class="form-input" style="min-height: 100px;"></textarea>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-heart"></i>
                Créer mon compte
            </button>
        </form>

        <div class="login-link">
            Déjà membre ? <a href="login.php">Se connecter</a>
        </div>
    </div>

    <!-- JavaScript minimal et sécurisé -->
    <script>
        // Simple validation côté client
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Les mots de passe ne correspondent pas');
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>
