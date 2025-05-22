<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Loove Dating App'); ?></title>
    
    <!-- Include CSS files -->
    <?php if(isset($cssFiles) && is_array($cssFiles)): ?>
        <?php foreach($cssFiles as $cssFile): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($cssFile); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/style.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/messages.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/utilities.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Loove Dating App</h1>
            <nav>
                <ul>
                    <li><a href="<?php echo $baseUrl ?? '/loove/app-loove/public'; ?>/">Home</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo $baseUrl ?? '/loove/app-loove/public'; ?>/profile">Profile</a></li>
                        <li><a href="<?php echo $baseUrl ?? '/loove/app-loove/public'; ?>/search">Search</a></li>
                        <li><a href="<?php echo $baseUrl ?? '/loove/app-loove/public'; ?>/message">Messages</a></li>
                        <li><a href="<?php echo $baseUrl ?? '/loove/app-loove/public'; ?>/logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $baseUrl ?? '/loove/app-loove/public'; ?>/login">Login</a></li>
                        <li><a href="<?php echo $baseUrl ?? '/loove/app-loove/public'; ?>/register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php echo $content; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Loove Dating App. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <?php if(isset($jsFiles) && is_array($jsFiles)): ?>
        <?php foreach($jsFiles as $jsFile): ?>
            <script src="<?php echo htmlspecialchars($jsFile); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if(isset($currentPage) && $currentPage === 'home'): ?>
        <script src="<?php echo $baseUrl; ?>/js/home.js"></script>
    <?php endif; ?>
</body>
</html>
