<?php
// Direct test file to verify CSS loading
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Test Page</title>
    
    <!-- Test CSS with absolute paths -->
    <link rel="stylesheet" href="/loove/app-loove/public/css/style.css">
    <link rel="stylesheet" href="/loove/app-loove/public/css/auth.css">
    
    <style>
        .test-container {
            border: 1px solid #333;
            padding: 20px;
            margin: 20px;
            font-family: Arial, sans-serif;
        }
        
        .path-info {
            background-color: #f5f5f5;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-family: monospace;
        }

        .debug-box {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>CSS Test Page</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="auth-container">
            <h2>Test Authentication Container</h2>
            <p>This container should be styled with auth.css.</p>
            
            <form class="auth-form">
                <div class="form-group">
                    <label for="test-email">Email Address</label>
                    <input type="email" id="test-email" class="form-control" value="test@example.com">
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn-primary">Test Button</button>
                </div>
            </form>
        </div>
        
        <div class="debug-box">
            <h3>CSS File Information</h3>
            <p>CSS directory: <?php echo __DIR__ . '/css'; ?></p>
            <p>CSS directory exists: <?php echo is_dir(__DIR__ . '/css') ? 'YES' : 'NO'; ?></p>
            
            <?php if(is_dir(__DIR__ . '/css')): ?>
                <h4>Files in CSS directory:</h4>
                <ul>
                    <?php foreach(scandir(__DIR__ . '/css') as $file): ?>
                        <li><?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <p>style.css exists: <?php echo file_exists(__DIR__ . '/css/style.css') ? 'YES' : 'NO'; ?></p>
            <p>auth.css exists: <?php echo file_exists(__DIR__ . '/css/auth.css') ? 'YES' : 'NO'; ?></p>
            
            <h3>Also check assets/css directory</h3>
            <p>Assets CSS dir: <?php echo __DIR__ . '/assets/css'; ?></p>
            <p>Assets CSS dir exists: <?php echo is_dir(__DIR__ . '/assets/css') ? 'YES' : 'NO'; ?></p>
            
            <?php if(is_dir(__DIR__ . '/assets/css')): ?>
                <h4>Files in assets/css directory:</h4>
                <ul>
                    <?php foreach(scandir(__DIR__ . '/assets/css') as $file): ?>
                        <li><?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <p>style.css in assets exists: <?php echo file_exists(__DIR__ . '/assets/css/style.css') ? 'YES' : 'NO'; ?></p>
        </div>
    </div>
</body>
</html>
