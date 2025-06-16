<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/style.css">
    <?php if (isset($cssFiles) && is_array($cssFiles)): ?>
        <?php foreach ($cssFiles as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="auth-container">
        <h2>Se connecter à votre compte</h2>
        
        <?php if(isset($registered) && $registered): ?>
            <div class="alert alert-success">
            Inscription réussie ! Vous pouvez maintenant vous connecter avec vos identifiants.
            </div>
        <?php endif; ?>
        
        <?php if(isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo $baseUrl; ?>/login" class="auth-form">
            <div class="form-group">
                <label for="email">Adresse mail</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </div>
            
            <div class="auth-links">
               Vous n'avez pas de compte <a href="<?php echo $baseUrl; ?>/register">S'inscrire</a>
            </div>
        </form>
    </div>
</body>
</html>