<?php
// This check is to prevent direct access to this file.
if (strpos($_SERVER['REQUEST_URI'], '/app/views/errors/404.php') !== false) {
    header("Location: " . (defined('APP_URL') ? APP_URL : '/'));
    exit;
}

http_response_code(404);
$title = "404 - Page Not Found";

// If this is included by the router, $content might already be set.
// We want to ensure this page's content is shown.
ob_start();
?>
<style>
    .error-container {
        text-align: center;
        padding: 50px 20px;
        min-height: 60vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .error-container h1 {
        font-size: 5rem;
        color: #e74c3c;
        margin-bottom: 0;
    }
    .error-container h2 {
        font-size: 1.5rem;
        color: #555;
        margin-top: 0;
        margin-bottom: 20px;
    }
    .error-container p {
        color: #777;
        margin-bottom: 30px;
    }
    .error-container a.btn {
        padding: 10px 20px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    .error-container a.btn:hover {
        background-color: #2980b9;
    }
</style>
<div class="error-container">
    <h1>404</h1>
    <h2>Oops! Page Not Found.</h2>
    <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
    <a href="<?php echo defined('APP_URL') ? APP_URL : '/'; ?>" class="btn">Go to Homepage</a>
</div>

<?php
$content_for_layout = ob_get_clean();

// Determine if we should include the main layout or just output content
// This depends on how Router::serveNotFound() calls this file.
// If it's a direct require, we might need to include the layout.
// If it's part of the view rendering system, $content will be used by main.php
if (isset($GLOBALS['use_main_layout_for_404']) && $GLOBALS['use_main_layout_for_404'] === true) {
    $content = $content_for_layout; // Set $content for main.php
    if (file_exists(BASE_PATH . '/app/views/layouts/main.php')) {
        require_once BASE_PATH . '/app/views/layouts/main.php';
    } else {
        // Fallback if main layout is not found
        echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>$title</title></head><body>";
        echo $content_for_layout;
        echo "</body></html>";
    }
} else {
    // If called directly by router's die/require, just output the content.
    // This is the typical case for Router::serveNotFound()
    echo $content_for_layout;
}
?>
