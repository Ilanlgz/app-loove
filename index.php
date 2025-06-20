<?php
session_start();

// Si l'utilisateur est déjà connecté, le rediriger
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
        header("location: admin/dashboard.php");
    } else {
        header("location: main.php");
    }
    exit;
}

// Sinon rediriger vers la page de connexion
header("location: login.php");
exit;
?>
