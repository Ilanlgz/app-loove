<?php
// This file directly serves the style.css file for testing
$cssFile = __DIR__ . '/css/style.css';

if (file_exists($cssFile) && is_readable($cssFile)) {
    header('Content-Type: text/css');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    readfile($cssFile);
    exit;
} else {
    header('Content-Type: text/plain');
    echo "Error: CSS file not found or not readable.\n";
    echo "Looking for: " . $cssFile . "\n";
    echo "File exists: " . (file_exists($cssFile) ? 'YES' : 'NO') . "\n";
    echo "File readable: " . (is_readable($cssFile) ? 'YES' : 'NO') . "\n";
    
    if (is_dir(__DIR__ . '/css')) {
        echo "\nFiles in CSS directory:\n";
        foreach (scandir(__DIR__ . '/css') as $file) {
            echo "- " . $file . "\n";
        }
    } else {
        echo "\nCSS directory does not exist.\n";
    }
}
?>
