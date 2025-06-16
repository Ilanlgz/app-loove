<?php
session_start();

// Détruire toutes les données de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Supprimer le cookie de "se souvenir de moi" si il existe
if (isset($_COOKIE['loove_remember'])) {
    setcookie('loove_remember', '', time() - 3600, '/');
}

// Rediriger vers la page de connexion
header("location: login.php");
exit();
?>
