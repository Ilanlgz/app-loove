<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de chargement CSS</title>
    
    <!-- Essayons différentes façons de charger les CSS -->
    <link rel="stylesheet" href="/loove/app-loove/public/css/style.css">
    <link rel="stylesheet" href="/loove/app-loove/public/css/auth.css">
    
    <style>
        /* Styles de secours (fallback) */
        .boite-test {
            border: 1px solid #333;
            padding: 20px;
            margin: 20px;
            background-color: #f8f8f8;
        }
        
        .info-fichier {
            font-family: monospace;
            background-color: #eee;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Page de test CSS</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="debug-css">
            <h2>Test de chargement CSS</h2>
            <p>Si les CSS sont correctement chargés :</p>
            <ul>
                <li>Le fond de la page doit être bleu clair</li>
                <li>L'en-tête (ci-dessus) doit avoir un fond rose</li>
                <li>Cette boîte doit avoir une bordure rose en pointillé</li>
                <li>Tous les titres doivent être roses</li>
            </ul>
        </div>
        
        <div class="auth-container">
            <h2>Test des styles d'authentification</h2>
            <p>Cette boîte doit avoir une bordure rose pleine si auth.css est correctement chargé.</p>
            <button class="btn-primary">Bouton de test</button>
        </div>
        
        <div class="info-fichier">
            <h3>Informations sur les fichiers CSS :</h3>
            <p>Répertoire CSS : <?php echo __DIR__ . '/css'; ?></p>
            <p>Le répertoire CSS existe : <?php echo is_dir(__DIR__ . '/css') ? 'OUI' : 'NON'; ?></p>
            
            <?php if (is_dir(__DIR__ . '/css')): ?>
                <h4>Fichiers dans le répertoire CSS :</h4>
                <ul>
                    <?php foreach (scandir(__DIR__ . '/css') as $file): ?>
                        <li><?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <h4>État des fichiers :</h4>
            <p>style.css existe : <?php echo file_exists(__DIR__ . '/css/style.css') ? 'OUI' : 'NON'; ?></p>
            <p>style.css est lisible : <?php echo is_readable(__DIR__ . '/css/style.css') ? 'OUI' : 'NON'; ?></p>
            <p>auth.css existe : <?php echo file_exists(__DIR__ . '/css/auth.css') ? 'OUI' : 'NON'; ?></p>
            <p>auth.css est lisible : <?php echo is_readable(__DIR__ . '/css/auth.css') ? 'OUI' : 'NON'; ?></p>
            
            <h4>Aperçu du contenu CSS :</h4>
            <pre>
<?php
    $style_file = __DIR__ . '/css/style.css';
    if (file_exists($style_file) && is_readable($style_file)) {
        $content = file_get_contents($style_file);
        echo htmlspecialchars(substr($content, 0, 200)) . "...";
    } else {
        echo "Impossible de lire style.css";
    }
?>
            </pre>
        </div>
        
        <div class="boite-test">
            <h3>Liens directs vers les fichiers CSS :</h3>
            <p>Cliquez sur ces liens pour accéder directement aux fichiers CSS :</p>
            <ul>
                <li><a href="/loove/app-loove/public/css/style.css" target="_blank">style.css</a></li>
                <li><a href="/loove/app-loove/public/css/auth.css" target="_blank">auth.css</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
