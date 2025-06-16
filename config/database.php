<?php
/**
 * Database configuration file
 * Simple redirect to core Database class
 */

// Utiliser la classe Database existante du core
require_once __DIR__ . '/../core/Database.php';

// Fonction helper pour obtenir la connexion
function getDbConnection() {
    return Database::getInstance()->getConnection();
}
?>
