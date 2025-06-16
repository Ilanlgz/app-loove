<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
    <title>Inscription Loove</title>
</head>
<body>
    <main>
        <div class="auth-container">
            <h2>Créer un compte</h2>
            
            <?php if(isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if(isset($success) && $success): ?>
                <div class="alert alert-success">
                    Inscription valider avec succès </var> <a href="<?php echo $baseUrl; ?>/login">Connexion</a>.
                </div>
            <?php else: ?>
                <form method="post" action="<?php echo $baseUrl; ?>/register" class="auth-form">
                    <div class="form-group">
                        <label for="name">Nom complet</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Adresse mail</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">S'inscrire</button>
                    </div>
                    
                    <div class="auth-links">
                        Vous avez déjà un compte ? <a href="<?php echo $baseUrl; ?>/login">Se connecter</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
   
</body>
</html>