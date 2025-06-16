<?php
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: accueil.php");
    exit;
}

$error_message = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : "";
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "";
if($error_message) unset($_SESSION['login_error']);
if($success_message) unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF4458;
            --primary-dark: #E73C4E;
            --secondary-color: #FD5068;
            --accent-color: #FF6B7D;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --background: #FAFAFA;
            --white: #FFFFFF;
            --border-color: #E8E8E8;
            --error: #FF3B30;
            --success: #34C759;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FF4458 0%, #FD5068 50%, #FF6B7D 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: 0 32px 64px rgba(255, 68, 88, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            padding: 50px 40px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 40px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 16px 32px rgba(255, 68, 88, 0.3);
        }

        .logo i {
            font-size: 2rem;
            color: var(--white);
        }

        .login-title {
            color: var(--text-primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 40px;
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
            color: var(--error);
            border: 1px solid rgba(255, 59, 48, 0.2);
        }

        .alert-success {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
            border: 1px solid rgba(52, 199, 89, 0.2);
        }

        .form-group {
            margin-bottom: 24px;
            text-align: left;
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
            padding: 18px 24px;
            border: 2px solid var(--border-color);
            border-radius: 16px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(255, 68, 88, 0.1);
            transform: translateY(-2px);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .btn-primary {
            width: 100%;
            padding: 18px 24px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 32px;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(255, 68, 88, 0.4);
        }

        .divider {
            position: relative;
            text-align: center;
            margin: 32px 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border-color);
        }

        .divider span {
            background: var(--white);
            padding: 0 20px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .register-link {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-heart"></i>
            </div>
            <h1 class="login-title">Connexion</h1>
            <p class="login-subtitle">Connectez-vous pour retrouver votre communauté</p>
        </div>

        <?php if($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
            </div>
        <?php endif; ?>

        <form action="process_login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <input type="email" id="email" name="email" class="form-input" required placeholder="votre@email.com">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="••••••••">
            </div>

            <div class="form-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>
                <a href="#" class="forgot-password">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>

        <div class="divider">
            <span>Nouveau sur Loove ?</span>
        </div>

        <div class="register-link">
            <a href="register.php">Créer un compte gratuitement</a>
        </div>
    </div>
</body>
</html>
