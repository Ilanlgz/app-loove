<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Match.php';
require_once '../classes/User.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

// Vérifier si l'ID de l'utilisateur est fourni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
    $liked_user_id = (int)$_POST['user_id'];
    
    try {
        $conn = getDbConnection();
        $userSystem = new UserSystem($conn);
        
        // Vérifier si l'utilisateur a un abonnement premium
        if (!$userSystem->isPremiumUser($current_user_id)) {
            echo json_encode(['success' => false, 'message' => 'Fonctionnalité premium uniquement']);
            exit;
        }
        
        $matchSystem = new MatchSystem();
        $result = $matchSystem->superLikeUser($current_user_id, $liked_user_id);
        
        echo json_encode([
            'success' => true,
            'match' => isset($result['match']) && $result['match']
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}
?>
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}
?>
