<?php
session_start();
require_once '../../vendor/autoload.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

try {
    // Récupérer les données de la requête
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? null;
    
    if (!$user_id) {
        throw new Exception('User ID manquant');
    }
    
    // Vérifier que l'utilisateur correspond à la session
    $expected_user_id = 'user-' . $_SESSION['user_id'];
    if ($user_id !== $expected_user_id) {
        throw new Exception('User ID invalide');
    }
    
    // Configuration Pusher Beams
    $beamsClient = new \Pusher\PushNotifications\PushNotifications([
        'instanceId' => 'a4e6f07d-67c8-4327-9be8-example', // Remplace par ton instance ID
        'secretKey' => 'C8A1234567890ABCDEF1234567890ABCDEF1234567890', // Remplace par ta clé secrète
    ]);
    
    // Générer le token d'authentification
    $token = $beamsClient->generateToken($user_id);
    
    echo json_encode($token);
    
} catch (Exception $e) {
    error_log('Erreur Beams Auth: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}
?>
