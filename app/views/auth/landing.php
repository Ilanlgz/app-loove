<?php 
// This view will not use the main layout's header/footer in the traditional sense.
// It's designed to be a full-page experience.
// We can prevent main layout inclusion in the controller or by not calling parent layout in this view.
// For now, let's assume the CSS will handle making it full-page.
// The $title variable is passed from AuthController.

// Retrieve and clear session data for login form
$error_message_login = $_SESSION['error_message_login'] ?? null;
$form_data_login = $_SESSION['form_data_login'] ?? [];
unset($_SESSION['error_message_login'], $_SESSION['form_data_login']);

// Retrieve and clear session data for registration form
$error_message_register = $_SESSION['error_message_register'] ?? null;
$form_data_register = $_SESSION['form_data_register'] ?? []; // This will contain 'errors_reg' and input values
$errors_reg = $form_data_register['errors_reg'] ?? [];
unset($_SESSION['error_message_register'], $_SESSION['form_data_register']);

// Retrieve and clear general success/error messages
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Loove - Trouvez l\'amour' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #2d3748;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .landing-header {
            padding: 2rem 0;
            text-align: center;
            color: white;
        }
        .logo {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .tagline {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .auth-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin: 2rem auto;
            max-width: 800px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .auth-tabs {
            display: flex;
            margin-bottom: 2rem;
            background: #f8fafc;
            border-radius: 10px;
            padding: 0.5rem;
        }
        .tab-btn {
            flex: 1;
            background: none;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        .tab-btn.active {
            background: #e94057;
            color: white;
        }
        .auth-form {
            display: none;
        }
        .auth-form.active {
            display: block;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-input:focus {
            outline: none;
            border-color: #e94057;
        }
        .btn-primary {
            width: 100%;
            background: #e94057;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #d63447;
            transform: translateY(-2px);
        }
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        .feature {
            text-align: center;
            padding: 2rem;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            color: white;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .feature h3 {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="landing-header">
        <div class="container">
            <div class="logo">♥ Loove</div>
            <div class="tagline">Trouvez l'amour authentique</div>
        </div>
    </div>

    <div class="container">
        <div class="auth-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">✗ <?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <div class="auth-tabs">
                <button class="tab-btn active" onclick="switchTab('login')">Connexion</button>
                <button class="tab-btn" onclick="switchTab('register')">Inscription</button>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="auth-form active" method="POST" action="/loove/public/index.php?url=processLogin">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label class="form-label">Adresse email</label>
                    <input type="email" name="email" class="form-input" placeholder="votre@email.com" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-primary">Se connecter</button>
                
                <p style="text-align: center; margin-top: 1rem; color: #64748b;">
                    Compte de test : test@loove.com / password
                </p>
            </form>

            <!-- Register Form -->
            <form id="registerForm" class="auth-form" method="POST" action="/loove/public/index.php?url=processRegister">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="first_name" class="form-input" placeholder="Votre prénom" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Adresse email</label>
                    <input type="email" name="email" class="form-input" placeholder="votre@email.com" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-primary">Créer mon compte</button>
            </form>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">♥</div>
                <h3>Rencontres authentiques</h3>
                <p>Connectez-vous avec des personnes qui partagent vos valeurs</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🛡</div>
                <h3>Sécurité & confidentialité</h3>
                <p>Vos données sont protégées et sécurisées</p>
            </div>
            <div class="feature">
                <div class="feature-icon">★</div>
                <h3>Algorithme intelligent</h3>
                <p>Notre IA vous propose les meilleurs matchs</p>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Remove active classes
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
            
            // Add active classes
            event.target.classList.add('active');
            document.getElementById(tab + 'Form').classList.add('active');
        }
    </script>
</body>
</html>
