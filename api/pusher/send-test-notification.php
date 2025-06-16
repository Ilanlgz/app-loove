<?php
session_start();
require_once '../../vendor/autoload.php';

header('Content-Type: application/json');

// Vérifier l'authentification
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? null;
    $message = $input['message'] ?? 'Test notification';
    
    if (!$user_id || $user_id != $_SESSION['user_id']) {
        throw new Exception('User ID invalide');
    }
    
    // Configuration avec tes vraies clés
    $beamsClient = new \Pusher\PushNotifications\PushNotifications([
        'instanceId' => '4bbe0180-fd1d-4834-84c3-128c682c923d',
        'secretKey' => '07255B4D6A282E46CB5CE36FAB1F71B1CE604D2ABC9F597334F5298AF755126A',
    ]);
    
    // Envoyer la notification de test
    $response = $beamsClient->publishToInterests(
        ['user-' . $user_id],
        [
            'web' => [
                'notification' => [
                    'title' => '🧪 Test Loove - ' . $_SESSION['first_name'],
                    'body' => $message . ' 💕',
                    'icon' => 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png',
                    'badge' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
                    'tag' => 'loove-test',
                    'data' => [
                        'type' => 'test',
                        'user_id' => $user_id,
                        'timestamp' => time(),
                        'url' => '/main.php'
                    ]
                ]
            ]
        ]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification test envoyée!',
        'response' => $response
    ]);
    
} catch (Exception $e) {
    error_log('Erreur test notification: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
