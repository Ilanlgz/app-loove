<?php
echo "<h1>Test Simple AuthController</h1>";

// Step 1: Load config
echo "1. Loading config...<br>";
require_once 'config/config.php';
echo "   - BASE_PATH: " . BASE_PATH . "<br>";

// Step 2: Load Controller base class
echo "2. Loading Controller...<br>";
if (file_exists(BASE_PATH . '/core/Controller.php')) {
    require_once BASE_PATH . '/core/Controller.php';
    echo "   - Controller loaded ✅<br>";
} else {
    echo "   - Controller NOT FOUND ❌<br>";
}

// Step 3: Load AuthController
echo "3. Loading AuthController...<br>";
$authPath = BASE_PATH . '/app/controllers/AuthController.php';
echo "   - Path: $authPath<br>";
if (file_exists($authPath)) {
    require_once $authPath;
    echo "   - File loaded ✅<br>";
    
    if (class_exists('AuthController')) {
        echo "   - Class exists ✅<br>";
        
        try {
            $auth = new AuthController();
            echo "   - Instance created ✅<br>";
            
            if (method_exists($auth, 'landing')) {
                echo "   - landing() method exists ✅<br>";
                echo "<p><strong>TOUT FONCTIONNE ! Le problème est ailleurs.</strong></p>";
            } else {
                echo "   - landing() method missing ❌<br>";
            }
        } catch (Exception $e) {
            echo "   - Instance creation failed: " . $e->getMessage() . " ❌<br>";
        }
    } else {
        echo "   - Class NOT EXISTS ❌<br>";
    }
} else {
    echo "   - File NOT FOUND ❌<br>";
}

echo "<hr>";
echo "<h2>Contents of AuthController.php:</h2>";
echo "<pre>" . htmlspecialchars(file_get_contents($authPath)) . "</pre>";
?>
