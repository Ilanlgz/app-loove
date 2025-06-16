<?php
session_start();

// Détruire toutes les variables de session admin
unset($_SESSION["admin_loggedin"]);
unset($_SESSION["admin_id"]);
unset($_SESSION["admin_name"]);
unset($_SESSION["admin_email"]);

// Rediriger vers la page de connexion admin
header("location: login.php");
exit;
?>
