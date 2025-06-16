<?php
// Script simple pour servir directement les fichiers CSS
$fichier = isset($_GET['fichier']) ? $_GET['fichier'] : 'style.css';

// Nettoyage du nom de fichier pour des raisons de sécurité
$fichier = basename($fichier);

// Chemin complet du fichier
$chemin_fichier = __DIR__ . '/css/' . $fichier;

// Vérifier si le fichier existe et est lisible
if (file_exists($chemin_fichier) && is_readable($chemin_fichier)) {
    // Définir le type de contenu
    $extension = pathinfo($chemin_fichier, PATHINFO_EXTENSION);
    if ($extension == 'css') {
        header('Content-Type: text/css');
    } else {
        header('Content-Type: text/plain');
    }
    
    // Désactiver la mise en cache pour le développement
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Afficher le contenu du fichier
    readfile($chemin_fichier);
} else {
    // Message d'erreur si le fichier n'est pas trouvé
    header('Content-Type: text/plain');
    echo "Erreur: Impossible de lire le fichier '{$fichier}'\n";
    echo "Chemin complet: {$chemin_fichier}\n";
    echo "Le fichier existe: " . (file_exists($chemin_fichier) ? 'OUI' : 'NON') . "\n";
    echo "Le fichier est lisible: " . (is_readable($chemin_fichier) ? 'OUI' : 'NON') . "\n";
    
    // Afficher le contenu du répertoire CSS
    $css_dir = __DIR__ . '/css';
    if (is_dir($css_dir)) {
        echo "\nFichiers dans le répertoire CSS:\n";
        foreach (scandir($css_dir) as $f) {
            echo "- {$f}\n";
        }
    } else {
        echo "\nLe répertoire CSS n'existe pas.\n";
    }
}
?>
