<?php
session_start();
require_once 'classes/User.php';

// Variables pour les messages
$error_message = "";
$success_message = "";

// Traitement de la connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = new User();
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        $user->email = $email;
        
        if ($user->emailExists()) {
            if (!$user->is_active) {
                $error_message = "Votre compte a été désactivé.";
            } else if (password_verify($password, $user->password)) {
                // Connexion réussie
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $user->id;
                $_SESSION["email"] = $user->email;
                $_SESSION["first_name"] = $user->first_name;
                $_SESSION["last_name"] = $user->last_name;
                $_SESSION["is_premium"] = $user->is_premium;
                $_SESSION["profile_picture"] = $user->profile_picture;
                
                if ($remember) {
                    $cookie_name = "loove_remember";
                    $cookie_value = base64_encode($user->id . ":" . $user->email);
                    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
                }
                
                $user->updateLastActive();
                
                // Redirection immédiate avec JavaScript
                echo '<!DOCTYPE html>
                <html>
                <head>
                    <title>Connexion réussie</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                        .success { color: #28a745; font-size: 18px; margin-bottom: 20px; }
                        .loading { color: #6c757d; }
                    </style>
                </head>
                <body>
                    <div class="success">✅ Connexion réussie !</div>
                    <div class="loading">Redirection vers votre espace personnel...</div>
                    <script>
                        setTimeout(function() {
                            window.location.href = "main.php";
                        }, 1000);
                    </script>
                </body>
                </html>';
                exit();
            } else {
                $error_message = "Email ou mot de passe incorrect.";
            }
        } else {
            $error_message = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF4458;
            --error: #FF3B30;
            --white: #FFFFFF;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --border-color: #E8E8E8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FF4458 0%, #FD5068 50%, #FF6B7D 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(255, 68, 88, 0.3);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
        }

        .logo { font-size: 3rem; color: var(--primary-color); margin-bottom: 20px; }
        .title { font-size: 1.8rem; font-weight: 600; margin-bottom: 30px; color: var(--text-primary); }

        .error {
            background: rgba(255, 59, 48, 0.1);
            color: var(--error);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 59, 48, 0.2);
        }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-label { display: block; margin-bottom: 5px; font-weight: 500; color: var(--text-primary); }
        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-input:focus { outline: none; border-color: var(--primary-color); }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), #FD5068);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-login:hover { transform: translateY(-2px); }

        .register-link { color: var(--text-secondary); }
        .register-link a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo"><i class="fas fa-heart"></i></div>
        <h1 class="title">Connexion</h1>

        <?php if($error_message): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-input" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <div class="register-link">
            Pas de compte ? <a href="direct_register.php">S'inscrire</a>
        </div>
    </div>
</body>
</html>
