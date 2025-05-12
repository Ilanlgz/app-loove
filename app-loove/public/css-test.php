<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Test Page</title>
    
    <!-- Try different ways to load CSS -->
    <link rel="stylesheet" href="/loove/app-loove/public/css/style.css">
    <link rel="stylesheet" href="/loove/app-loove/public/css/auth.css">
    
    <!-- Inline styles for visibility check -->
    <style>
        .test-container {
            border: 1px solid #333;
            padding: 20px;
            margin: 20px;
        }
        
        .path-info {
            background-color: #eee;
            padding: 15px;
            margin: 15px 0;
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
        <div class="debug-css">
            <h2>CSS Loading Test</h2>
            <p>If the styling is working correctly:</p>
            <ul>
                <li>This box should have a pink dashed border</li>
                <li>The page background should be light blue</li>
                <li>Headers should be pink</li>
            </ul>
        </div>
        
        <div class="auth-container">
            <h2>Auth CSS Test</h2>
            <p>This container should have a pink border if auth.css is working.</p>
            <button class="btn-primary">Test Button</button>
        </div>
        
        <div class="path-info">
            <h3>Path Information:</h3>
            <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
            <p>Current Script: <?php echo __FILE__; ?></p>
            <p>Request URI: <?php echo $_SERVER['REQUEST_URI']; ?></p>
            
            <h3>CSS Files Status:</h3>
            <p>CSS dir exists: <?php echo is_dir(__DIR__ . '/css') ? 'YES' : 'NO'; ?></p>
            <?php if (is_dir(__DIR__ . '/css')): ?>
                <h4>Files in CSS directory:</h4>
                <ul>
                    <?php foreach (scandir(__DIR__ . '/css') as $file): ?>
                        <li><?php echo $file; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <p>style.css exists: <?php echo file_exists(__DIR__ . '/css/style.css') ? 'YES' : 'NO'; ?></p>
            <p>style.css readable: <?php echo is_readable(__DIR__ . '/css/style.css') ? 'YES' : 'NO'; ?></p>
            <p>style.css content:</p>
            <pre><?php echo htmlspecialchars(file_get_contents(__DIR__ . '/css/style.css')); ?></pre>
        </div>
    </div>
</body>
</html>
