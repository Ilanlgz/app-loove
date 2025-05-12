<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Loove Dating App' ?></title>
    
    <!-- Include CSS files -->
    <?php if(isset($css) && is_array($css)): ?>
        <?php foreach($css as $cssFile): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($cssFile) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>Loove</h1>
            <nav class="nav">
                <ul>
                    <li><a href="/">Home</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="/profile">Profile</a></li>
                        <li><a href="/search">Search</a></li>
                        <li><a href="/message">Messages</a></li>
                        <li><a href="/logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/login">Login</a></li>
                        <li><a href="/register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <main class="container">
