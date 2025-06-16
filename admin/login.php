<?php
session_start();

if (isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../config/database.php';
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        try {
            $conn = getDbConnection();
            
            // D'abord, vérifier si la colonne role existe
            try {
                $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user'");
            } catch (Exception $e) {
                // Ignorer si la colonne existe déjà
            }
            
            // Chercher d'abord par email seulement
            $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Vérifier si l'utilisateur existe mais n'a pas le role admin
                if (!isset($user['role']) || $user['role'] !== 'admin') {
                    // Mettre à jour le role pour cet email s'il s'agit de l'admin
                    if ($email === 'admin@loove.com') {
                        $update_role = "UPDATE users SET role = 'admin' WHERE email = :email";
                        $stmt_update = $conn->prepare($update_role);
                        $stmt_update->bindParam(':email', $email);
                        $stmt_update->execute();
                        
                        // Recharger l'utilisateur
                        $stmt->execute();
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        $error_message = "Accès administrateur non autorisé pour cet email.";
                    }
                }
                
                // Vérifier le mot de passe
                if (empty($error_message) && password_verify($password, $user['password'])) {
                    $_SESSION["admin_loggedin"] = true;
                    $_SESSION["admin_id"] = $user['id'];
                    $_SESSION["admin_name"] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION["admin_email"] = $user['email'];
                    
                    header("location: dashboard.php");
                    exit;
                } elseif (empty($error_message)) {
                    $error_message = "Mot de passe incorrect.";
                }
            } else {
                $error_message = "Aucun compte trouvé avec cet email.";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF4458;
            --secondary-color: #FD5068;
            --text-primary: #2c2c2c;
            --white: #FFFFFF;
            --error: #FF3B30;
            --admin-color: #1a1a2e;
            --admin-secondary: #16213e;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--admin-color), var(--admin-secondary));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .admin-login-container {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .admin-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-logo i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .admin-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 30px;
        }

        .alert-error {
            background: rgba(255, 59, 48, 0.1);
            color: #000000;
            border: 1px solid rgba(255, 59, 48, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #E8E8E8;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 68, 88, 0.1);
        }

        .btn-admin-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--admin-color), var(--admin-secondary));
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-admin-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(26, 26, 46, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-logo">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <h2 class="admin-title">Panel Administrateur</h2>
        
        <?php if($error_message): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" for="email">Email administrateur</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <button type="submit" class="btn-admin-login">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>

        <div class="back-link">
            <a href="../create_admin.php">
                <i class="fas fa-user-plus"></i> Créer le compte admin
            </a>
            |
            <a href="../main.php">
                <i class="fas fa-arrow-left"></i> Retour au site
            </a>
        </div>
    </div>
</body>
</html>
