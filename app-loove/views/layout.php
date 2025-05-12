<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Loove - Dating App'; ?></title>
    <!-- Main CSS file -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Page specific CSS files -->
    <?php if(isset($page_css) && is_array($page_css)): ?>
        <?php foreach($page_css as $css_file): ?>
            <link rel="stylesheet" href="<?php echo $css_file; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="container">
            <h1>Loove</h1>
            <nav>
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/profile">Profile</a></li>
                    <li><a href="/search">Search</a></li>
                    <li><a href="/messages">Messages</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="/auth/logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/auth/login">Login</a></li>
                        <li><a href="/auth/register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php include($content); ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Loove Dating App. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
