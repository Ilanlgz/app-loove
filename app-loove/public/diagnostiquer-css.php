<?php
// Script de diagnostic pour les problèmes de CSS
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic CSS</title>
    
    <!-- Tester le chargement des CSS avec différentes méthodes -->
    <link rel="stylesheet" href="/loove/app-loove/public/css/style.css">
    <link rel="stylesheet" href="/loove/app-loove/public/css/auth.css">
    
    <style>
        /* Styles de secours pour le diagnostic */
        .diagnostic-box {
            border: 1px solid #333;
            padding: 15px;
            margin: 15px;
            background-color: #f5f5f5;
            font-family: monospace;
        }
        
        h1 {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnostic CSS - Loove App</h1>
        
        <div class="debug-css">
            <h2>Test de style.css</h2>
            <p>Si style.css est chargé correctement, cette boîte aura une bordure rose en pointillé.</p>
        </div>
        
        <div class="auth-container">
            <h2>Test de auth.css</h2>
            <p>Si auth.css est chargé correctement, cette boîte aura une bordure rose.</p>
            <button class="btn-primary">Bouton de test</button>
        </div>
        
        <div class="diagnostic-box">
            <h3>Informations système</h3>
            <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
            <p>Script Path: <?php echo __FILE__; ?></p>
            <p>Current URL: <?php echo $_SERVER['REQUEST_URI']; ?></p>
            
            <h3>Répertoires et fichiers</h3>
            <p>CSS Directory: <?php echo __DIR__ . '/css'; ?></p>
            <p>CSS Directory exists: <?php echo is_dir(__DIR__ . '/css') ? 'OUI' : 'NON'; ?></p>
            
            <?php if(is_dir(__DIR__ . '/css')): ?>
                <h4>Fichiers dans le répertoire CSS:</h4>
                <ul>
                    <?php foreach(scandir(__DIR__ . '/css') as $file): ?>
                        <li><?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <h3>État des fichiers</h3>
            <p>style.css exists: <?php echo file_exists(__DIR__ . '/css/style.css') ? 'OUI' : 'NON'; ?></p>
            <p>style.css readable: <?php echo is_readable(__DIR__ . '/css/style.css') ? 'OUI' : 'NON'; ?></p>
            <p>auth.css exists: <?php echo file_exists(__DIR__ . '/css/auth.css') ? 'OUI' : 'NON'; ?></p>
            <p>auth.css readable: <?php echo is_readable(__DIR__ . '/css/auth.css') ? 'OUI' : 'NON'; ?></p>
            
            <?php
                $styleFile = __DIR__ . '/css/style.css';
                if(file_exists($styleFile) && is_readable($styleFile)):
            ?>
                <h3>Contenu de style.css (début)</h3>
                <pre><?php echo htmlspecialchars(substr(file_get_contents($styleFile), 0, 200)); ?>...</pre>
            <?php endif; ?>
            
            <h3>Actions possibles</h3>
            <p><a href="#" onclick="window.location.reload(true);">Recharger la page (ignorer le cache)</a></p>
            
            <button onclick="createCSSFiles()">Créer/Réparer les fichiers CSS</button>
            
            <script>
                function createCSSFiles() {
                    fetch('create-css-files.php')
                        .then(response => response.text())
                        .then(data => {
                            alert(data);
                            window.location.reload(true);
                        })
                        .catch(error => {
                            alert('Erreur: ' + error);
                        });
                }
            </script>
        </div>
    </div>
</body>
</html>
