<?php
echo "<h1>Loove Debug Information</h1>";

// Check directories
$baseDir = __DIR__;
echo "<h2>Base Directory: $baseDir</h2>";

$checkDirs = [
    'config',
    'core', 
    'app',
    'app/controllers',
    'app/views',
    'app/models',
    'public'
];

echo "<h2>Directory Structure:</h2>";
foreach ($checkDirs as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    echo $dir . ": " . (is_dir($fullPath) ? "✅ EXISTS" : "❌ MISSING") . " ($fullPath)<br>";
}

// Check core files
echo "<h2>Core Files:</h2>";
$coreFiles = [
    'config/config.php',
    'core/Database.php',
    'core/Router.php', 
    'core/Controller.php',
    'core/ErrorHandler.php'
];

foreach ($coreFiles as $file) {
    $fullPath = $baseDir . '/' . $file;
    echo $file . ": " . (file_exists($fullPath) ? "✅ EXISTS" : "❌ MISSING") . " ($fullPath)<br>";
}

// Check controller files
echo "<h2>Controller Files:</h2>";
$controllerFiles = [
    'app/controllers/AuthController.php'
];

foreach ($controllerFiles as $file) {
    $fullPath = $baseDir . '/' . $file;
    echo $file . ": " . (file_exists($fullPath) ? "✅ EXISTS" : "❌ MISSING") . " ($fullPath)<br>";
    
    if (file_exists($fullPath)) {
        echo "  - File size: " . filesize($fullPath) . " bytes<br>";
        echo "  - Last modified: " . date('Y-m-d H:i:s', filemtime($fullPath)) . "<br>";
    }
}

// Test config loading
echo "<h2>Config Test:</h2>";
try {
    require_once 'config/config.php';
    echo "Config loaded: ✅<br>";
    echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "<br>";
    echo "BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : 'NOT DEFINED') . "<br>";
} catch (Exception $e) {
    echo "Config error: ❌ " . $e->getMessage() . "<br>";
}

echo "<h2>PHP Info:</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current working directory: " . getcwd() . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
?>
