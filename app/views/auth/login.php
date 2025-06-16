<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<style>
    body {
        background: linear-gradient(135deg, #FF4458 0%, #FD5068 50%, #FF6B7D 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
    }
    
    .login-container {
        background: white;
        border-radius: 24px;
        box-shadow: 0 32px 64px rgba(255, 68, 88, 0.2);
        max-width: 400px;
        width: 100%;
        padding: 40px;
        margin: 20px;
    }
    
    .form-title {
        color: #2c2c2c;
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
        color: #34C759;
        border: 1px solid rgba(52, 199, 89, 0.2);
    }
    
    .form-group {
        margin-bottom: 24px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #2c2c2c;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .form-input {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #E8E8E8;
        border-radius: 12px;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s ease;
        background: #FFFFFF;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #FF4458;
        box-shadow: 0 0 0 4px rgba(255, 68, 88, 0.1);
    }
    
    .btn-primary {
        width: 100%;
        padding: 18px 24px;
        background: linear-gradient(135deg, #FF4458, #FD5068);
        color: #FFFFFF;
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
    
    .register-link {
        text-align: center;
        color: #8E8E93;
    }
    
    .register-link a {
        color: #FF4458;
        text-decoration: none;
        font-weight: 600;
    }
</style>

<div class="login-container">
    <h2 class="form-title">Connexion à Loove</h2>
    
    <?php if(isset($error) && $error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if(isset($success) && $success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="/loove/login.php">
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-input" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Mot de passe</label>
            <input type="password" id="password" name="password" class="form-input" required>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Se connecter
        </button>
    </form>

    <div class="register-link">
        Pas encore de compte ? <a href="/loove/register.php">Créer un compte</a>
    </div>
</div>

</body>
</html>
