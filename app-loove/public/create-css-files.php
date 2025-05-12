<?php
// Script pour créer/réparer les fichiers CSS
header('Content-Type: text/plain; charset=utf-8');

// Vérifier si le répertoire CSS existe, sinon le créer
$css_dir = __DIR__ . '/css';
if (!is_dir($css_dir)) {
    if (mkdir($css_dir, 0777, true)) {
        echo "Répertoire CSS créé avec succès.\n";
    } else {
        echo "Erreur: Impossible de créer le répertoire CSS.\n";
        exit;
    }
}

// Contenu pour style.css
$style_css = "/* Style principal pour l'application Loove */
body {
    background-color: #f0f2f5 !important;
    font-family: Arial, sans-serif !important;
    margin: 0 !important;
    padding: 0 !important;
    color: #333 !important;
}

h1, h2, h3 {
    color: #ff6b81 !important;
    margin-bottom: 15px !important;
}

header {
    background-color: #ff6b81 !important;
    color: white !important;
    padding: 15px 0 !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
}

.container {
    width: 90% !important;
    max-width: 1200px !important;
    margin: 0 auto !important;
    padding: 15px !important;
}

.debug-css {
    border: 3px dashed #ff6b81 !important;
    background-color: #ffe6e6 !important;
    padding: 15px !important;
    margin: 15px 0 !important;
    border-radius: 5px !important;
}";

// Contenu pour auth.css
$auth_css = "/* Styles pour les pages d'authentification */
.auth-container {
    background-color: white !important;
    border: 3px solid #ff6b81 !important;
    border-radius: 8px !important;
    padding: 30px !important;
    max-width: 500px !important;
    margin: 30px auto !important;
    box-shadow: 0 0 20px rgba(0,0,0,0.1) !important;
}

.auth-container h2 {
    color: #ff6b81 !important;
    text-align: center !important;
    margin-bottom: 25px !important;
    font-size: 24px !important;
}

.form-group {
    margin-bottom: 20px !important;
}

.btn-primary {
    background-color: #ff6b81 !important;
    color: white !important;
    border: none !important;
    padding: 12px 20px !important;
    width: 100% !important;
    cursor: pointer !important;
    border-radius: 4px !important;
}";

// Écrire style.css
$style_file = $css_dir . '/style.css';
if (file_put_contents($style_file, $style_css)) {
    echo "Fichier style.css créé avec succès.\n";
    chmod($style_file, 0666);
} else {
    echo "Erreur: Impossible de créer style.css.\n";
}

// Écrire auth.css
$auth_file = $css_dir . '/auth.css';
if (file_put_contents($auth_file, $auth_css)) {
    echo "Fichier auth.css créé avec succès.\n";
    chmod($auth_file, 0666);
} else {
    echo "Erreur: Impossible de créer auth.css.\n";
}

echo "\nOpération terminée. Vous pouvez maintenant retourner à la page de diagnostic.";
?>
