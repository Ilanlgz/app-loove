<?php
session_start();

// Si l'utilisateur est déjà connecté en tant qu'admin, le rediriger vers le dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
    header("location: dashboard.php");
    exit;
}

require_once '../config/database.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                
                if (password_verify($password, $user["password"])) {
                    // Vérifier que c'est un admin
                    if ($user["role"] === "admin") {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $user["id"];
                        $_SESSION["first_name"] = $user["first_name"];
                        $_SESSION["last_name"] = $user["last_name"];
                        $_SESSION["email"] = $user["email"];
                        $_SESSION["role"] = $user["role"];
                        
                        header("location: dashboard.php");
                        exit;
                    } else {
                        $error_message = "Accès refusé. Vous n'êtes pas administrateur.";
                    }
                } else {
                    $error_message = "Mot de passe incorrect.";
                }
            } else {
                $error_message = "Aucun compte trouvé avec cet email.";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur de connexion à la base de données.";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .admin-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 32px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #666;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            border-color: #FF4458;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 68, 88, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
        }

        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #FF4458;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="admin-logo">
                <i class="fas fa-shield-halved"></i>
            </div>
            <h1 class="login-title">Admin Loove</h1>
            <p class="login-subtitle">Panneau d'administration</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label class="form-label" for="email">
                    <i class="fas fa-envelope"></i> Email administrateur
                </label>
                <input type="email" id="email" name="email" class="form-input" 
                       placeholder="admin@loove.com" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">
                    <i class="fas fa-lock"></i> Mot de passe
                </label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter en tant qu'Admin
            </button>
        </form>

        <div class="back-link">
            <a href="../login.php">
                <i class="fas fa-arrow-left"></i>
                Retour à la connexion utilisateur
            </a>
        </div>
    </div>
</body>
</html>
