<?php
/**
 * Script utilitaire pour télécharger des images de démo depuis Unsplash
 * Exécutez ce script une fois pour configurer votre environnement de démo
 */

// Définir les chemins
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('ASSETS_PATH', ROOT_PATH . '/public/assets/images');
define('DEMO_PATH', ASSETS_PATH . '/demo');

// Créer des dossiers s'ils n'existent pas
if (!file_exists(ASSETS_PATH)) {
    mkdir(ASSETS_PATH, 0777, true);
}

if (!file_exists(DEMO_PATH)) {
    mkdir(DEMO_PATH, 0777, true);
}

// Fonction pour télécharger une image
function downloadImage($url, $path) {
    if (file_exists($path)) {
        echo "L'image existe déjà: " . basename($path) . "\n";
        return;
    }
    
    $content = file_get_contents($url);
    if ($content === false) {
        echo "Erreur lors du téléchargement de: " . $url . "\n";
        return;
    }
    
    file_put_contents($path, $content);
    echo "Image téléchargée: " . basename($path) . "\n";
}

// URLs d'images d'avatars d'Unsplash (portraits)
$avatarUrls = [
    'https://images.unsplash.com/photo-1544005313-94ddf0286df2', // Femme 1
    'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d', // Homme 1
    'https://images.unsplash.com/photo-1494790108377-be9c29b29330', // Femme 2
    'https://images.unsplash.com/photo-1500648767791-00dcc994a43e', // Homme 2
    'https://images.unsplash.com/photo-1534528741775-53994a69daeb', // Femme 3
    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d', // Homme 3
    'https://images.unsplash.com/photo-1531123897727-8f129e1688ce', // Femme 4
    'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61', // Homme 4
    'https://images.unsplash.com/photo-1499952127939-9bbf5af6c51c', // Femme 5
    'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6'  // Homme 5
];

// URLs d'images pour les stories
$storyUrls = [
    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f', // Groupe d'amis
    'https://images.unsplash.com/photo-1516298773066-c48f8e9bd92b', // Café
    'https://images.unsplash.com/photo-1414609245224-afa02bfb3fda', // Paysage
    'https://images.unsplash.com/photo-1583244532610-2a825ade4441', // Concert
    'https://images.unsplash.com/photo-1496412705862-e0088f16f791'  // Plage
];

// URLs d'images pour les posts
$postUrls = [
    'https://images.unsplash.com/photo-1488161628813-04466f872be2', // Mode
    'https://images.unsplash.com/photo-1533107862482-0e6974b06ec4', // Voyage
    'https://images.unsplash.com/photo-1551632811-561732d1e306', // Cuisine
    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce', // Selfie
    'https://images.unsplash.com/photo-1541697418880-c73508473887', // Technologie
    'https://images.unsplash.com/photo-1530062329328-9e18fb5d0d1e', // Nature
    'https://images.unsplash.com/photo-1518895949257-7621c3c786d7', // Lecture
    'https://images.unsplash.com/photo-1498579485796-98be3abc076e'  // Sport
];

// Télécharger les avatars
foreach ($avatarUrls as $index => $url) {
    $filename = 'demo-user-' . ($index + 1) . '.jpg';
    downloadImage($url . '?auto=format&fit=crop&w=300&h=300', DEMO_PATH . '/' . $filename);
}

// Télécharger les images de stories
foreach ($storyUrls as $index => $url) {
    $filename = 'story-' . ($index + 1) . '.jpg';
    downloadImage($url . '?auto=format&fit=crop&w=500&h=800', DEMO_PATH . '/' . $filename);
}

// Télécharger les images de posts
foreach ($postUrls as $index => $url) {
    $filename = 'post-' . ($index + 1) . '.jpg';
    downloadImage($url . '?auto=format&fit=crop&w=800&h=800', DEMO_PATH . '/' . $filename);
}

// Télécharger une image de couple pour l'illustration d'accueil
$coupleUrl = 'https://images.unsplash.com/photo-1517335868810-975284638fd2';
downloadImage($coupleUrl . '?auto=format&fit=crop&w=800&h=600', DEMO_PATH . '/couple-illustration.jpg');

// Télécharger une image par défaut pour les couvertures
$coverUrl = 'https://images.unsplash.com/photo-1557683316-973673baf926';
downloadImage($coverUrl . '?auto=format&fit=crop&w=800&h=300', ASSETS_PATH . '/default-cover.jpg');

// Télécharger une image par défaut pour les avatars
$defaultAvatarUrl = 'https://images.unsplash.com/photo-1513721032312-6a18a42c8763';
downloadImage($defaultAvatarUrl . '?auto=format&fit=crop&w=300&h=300', ASSETS_PATH . '/default-avatar.png');

echo "\nToutes les images ont été téléchargées avec succès dans: " . DEMO_PATH . "\n";
echo "Vous pouvez maintenant utiliser ces images dans votre application de démo.\n";
