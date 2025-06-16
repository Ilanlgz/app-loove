<?php
/**
 * Loove Dating App - Main Entry Point
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session BEFORE loading config to avoid conflicts
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Définir BASE_PATH seulement si pas déjà défini
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Inclure la configuration de base de données
require_once BASE_PATH . '/config/database.php';

// Inclure le router
require_once BASE_PATH . '/app/core/Router.php';

// Démarrer l'application
$router = new Router();
$router->handleRequest();
?>
