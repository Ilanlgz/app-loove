<?php
echo "<h1>Test Loove</h1>";
echo "<p>Si vous voyez ceci, le serveur fonctionne !</p>";

// Test des chemins
echo "<h2>Tests d'accès :</h2>";
echo "<ul>";
echo "<li><a href='/loove/public/'>Application principale</a></li>";
echo "<li><a href='/loove/public/index.php'>Index direct</a></li>";
echo "<li><a href='/loove/'>Racine avec redirection</a></li>";
echo "</ul>";

// Test des fichiers
echo "<h2>Vérification des fichiers :</h2>";
$files = [
    'config/config.php',
    'core/Database.php',
    'core/Router.php',
    'core/Controller.php',
    'core/ErrorHandler.php',
    'app/controllers/AuthController.php'
];

foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "• $file: " . ($exists ? "✅" : "❌") . "<br>";
}

echo "✅ Test réussi ! Les fichiers PHP fonctionnent directement.";
echo "<br><a href='login.php'>Aller à la connexion</a>";
echo "<br><a href='register.php'>Aller à l'inscription</a>";
?>
