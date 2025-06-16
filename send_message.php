<?php
session_start();
require_once 'classes/Message.php';

header('Content-Type: application/json');

// Debug: Log des données reçues
error_log("POST data: " . file_get_contents('php://input'));

// Vérifier la connexion
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Not logged in', 'success' => false]);
    exit;
}

// Récupérer les données
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid JSON data', 'success' => false]);
    exit;
}

$to_user_id = isset($input['to_user_id']) ? (int)$input['to_user_id'] : 0;
$message_text = isset($input['message']) ? trim($input['message']) : '';
$from_user_id = $_SESSION["user_id"];

// Debug: Log des valeurs
error_log("From: $from_user_id, To: $to_user_id, Message: $message_text");

// Validation
if (empty($message_text)) {
    echo json_encode(['error' => 'Message vide', 'success' => false]);
    exit;
}

if ($to_user_id <= 0) {
    echo json_encode(['error' => 'Destinataire invalide', 'success' => false]);
    exit;
}

if ($from_user_id == $to_user_id) {
    echo json_encode(['error' => 'Impossible de s\'envoyer un message à soi-même', 'success' => false]);
    exit;
}

try {
    $messageSystem = new Message();
    $message_id = $messageSystem->sendMessage($from_user_id, $to_user_id, $message_text);

    if ($message_id) {
        echo json_encode([
            'success' => true, 
            'message_id' => $message_id,
            'message' => 'Message envoyé avec succès'
        ]);
    } else {
        echo json_encode([
            'error' => 'Erreur lors de l\'insertion en base de données',
            'success' => false
        ]);
    }
} catch (Exception $e) {
    error_log("Exception sendMessage: " . $e->getMessage());
    echo json_encode([
        'error' => 'Erreur serveur: ' . $e->getMessage(),
        'success' => false
    ]);
}
?>
