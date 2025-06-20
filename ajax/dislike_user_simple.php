<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $_SESSION['error_message'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: ../login.php');
    exit;
}

// Vérifier si l'utilisateur à disliker est spécifié
if (!isset($_GET['user_id'])) {
    $_SESSION['error_message'] = "Utilisateur non spécifié.";
    header('Location: ../discover.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];
$disliked_user_id = (int)$_GET['user_id'];

try {
    $conn = getDbConnection();
    
    // Ajouter une entrée dans la table dislikes si elle existe
    try {
        $stmt = $conn->prepare("INSERT INTO dislikes (user_id, disliked_user_id) VALUES (?, ?)");
        $stmt->execute([$current_user_id, $disliked_user_id]);
        $_SESSION['info_message'] = "Utilisateur ignoré.";
    } catch (Exception $e) {
        // La table dislikes n'existe probablement pas, on l'ignore
        $_SESSION['info_message'] = "Utilisateur ignoré.";
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
}

// Rediriger vers la page de découverte
header('Location: ../discover.php');
exit;
?>
