<?php
session_start();

// Si l'utilisateur est connecté, aller au dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
} else {
    // Sinon, aller à la page de connexion
    header("location: login.php");
    exit;
}
?>
