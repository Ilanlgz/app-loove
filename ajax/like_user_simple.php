<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Match.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $_SESSION['error_message'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: ../login.php');
    exit;
}

// Vérifier si l'utilisateur à liker est spécifié
if (!isset($_GET['user_id'])) {
    $_SESSION['error_message'] = "Utilisateur non spécifié.";
    header('Location: ../discover.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];
$liked_user_id = (int)$_GET['user_id'];

try {
    $matchSystem = new MatchSystem();
    $result = $matchSystem->likeUser($current_user_id, $liked_user_id);
    
    if (isset($result['match']) && $result['match']) {
        $_SESSION['success_message'] = "C'est un match ! 💕";
    } else {
        $_SESSION['success_message'] = "Like envoyé ! 👍";
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
}

// Rediriger vers la page de découverte
header('Location: ../discover.php');
exit;
?>
            // C'est un match !
            $stmt = $conn->prepare("INSERT INTO matches (user1_id, user2_id) VALUES (?, ?)");
            $stmt->execute([$current_user_id, $liked_user_id]);
            
            $_SESSION['success_message'] = "C'est un match ! 💕";
        } else {
            $_SESSION['success_message'] = "Like envoyé ! 👍";
        }
    } else {
        $_SESSION['info_message'] = "Vous avez déjà liké cet utilisateur.";
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
}

// Rediriger vers la page de découverte
header('Location: ../discover.php');
exit;
