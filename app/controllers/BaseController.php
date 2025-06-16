<?php

class BaseController {
    
    protected function getCommonStyles() {
        return '
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f8fafc;
            color: #2d3748;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        ';
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
    }
    
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->setFlash('error', 'Vous devez être connecté.');
            $this->redirect('/loove/login.php');
        }
    }
    
    protected function generateCSRF() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    protected function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    protected function setFlash($key, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$key] = $message;
    }
    
    protected function getFlash($key) {
        $message = $_SESSION['flash'][$key] ?? null;
        if (isset($_SESSION['flash'][$key])) {
            unset($_SESSION['flash'][$key]);
        }
        return $message;
    }
    
    protected function redirect($url) {
        // Corriger toutes les redirections obsolètes
        if ($url === '/loove/accueil.php' || $url === 'accueil.php' || 
            strpos($url, 'direct_') !== false) {
            $url = '/loove/login.php';
        }
        
        header('Location: ' . $url);
        exit;
    }
    
    protected function view($view, $data = []) {
        extract($data);
        include BASE_PATH . '/app/views/' . $view . '.php';
    }
}
?>
