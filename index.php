<?php
session_start();

// Si l'utilisateur est connecté, aller à main.php
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: main.php");
    exit;
} else {
    // Sinon, aller à la page de connexion
    header("location: login.php");
    exit;
}
?>
