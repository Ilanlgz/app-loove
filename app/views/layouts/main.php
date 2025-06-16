<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <!-- Add any other global CSS or JS links here -->
</head>
<body>
    <?php require_once 'header.php'; ?>

    <main class="container">
        <?php 
        // Display success/error messages
        if (isset($_SESSION['success_message'])) {
            echo '<div class="message success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <?php echo $content; // This is where the view content will be injected ?>
    </main>

    <?php require_once 'footer.php'; ?>

    <script src="<?php echo APP_URL; ?>/js/main.js"></script>
    <!-- Add other global JS or page-specific JS here -->
</body>
</html>
