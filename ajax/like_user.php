<?php
session_start();
require_once '../classes/Match.php';
require_once '../classes/User.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Not logged in', 'success' => false]);
    exit;
}

// Vérifier si la méthode de requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed', 'success' => false]);
    exit;
}

// Récupérer l'ID de l'utilisateur à liker
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$current_user_id = $_SESSION["user_id"];

// Vérifier si l'ID de l'utilisateur est valide
if ($user_id <= 0) {
    echo json_encode(['error' => 'Invalid user ID', 'success' => false]);
    exit;
}

// Vérifier si l'utilisateur essaie de se liker lui-même
if ($user_id == $current_user_id) {
    echo json_encode(['error' => 'Cannot like yourself', 'success' => false]);
    exit;
}

try {
    $conn = getDbConnection();
    $matchSystem = new MatchSystem();
    
    // Vérifier si l'utilisateur a déjà liké cette personne
    $stmt = $conn->prepare("SELECT id FROM likes WHERE liker_id = ? AND liked_id = ?");
    $stmt->execute([$current_user_id, $user_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Already liked', 'success' => false]);
        exit;
    }
    
    // Ajouter le like
    $stmt = $conn->prepare("INSERT INTO likes (liker_id, liked_id, is_like, created_at) VALUES (?, ?, 1, NOW())");
    $like_success = $stmt->execute([$current_user_id, $user_id]);
    
    if (!$like_success) {
        echo json_encode(['error' => 'Failed to save like', 'success' => false]);
        exit;
    }
    
    // Vérifier si c'est un match mutuel
    $is_mutual = $matchSystem->checkMutualLike($current_user_id, $user_id);
    
    if ($is_mutual) {
        // Créer le match
        $match_created = $matchSystem->createMatch($current_user_id, $user_id);
        
        if ($match_created) {
            // Créer la conversation de match
            $matchSystem->createMatchConversation($current_user_id, $user_id);
            
            // Récupérer les infos de l'utilisateur matché
            $stmt = $conn->prepare("SELECT first_name, last_name, profile_picture FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $matched_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'match' => true,
                'message' => 'C\'est un match ! 🎉',
                'matched_user' => $matched_user,
                'redirect_url' => "messages.php?user_id=" . $user_id
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'match' => false,
                'message' => 'Like envoyé !'
            ]);
        }
    } else {
        echo json_encode([
            'success' => true,
            'match' => false,
            'message' => 'Like envoyé !'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erreur like_user: " . $e->getMessage());
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage(),
        'success' => false
    ]);
}
?>
