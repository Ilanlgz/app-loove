<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// Vérifier que la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    $conn = getDbConnection();
    
    // Récupérer les données
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    
    if (!$receiver_id || !$content) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
        exit;
    }
    
    // Insérer le message en base
    $query = "INSERT INTO messages (sender_id, receiver_id, content, sent_at) 
              VALUES (:sender_id, :receiver_id, :content, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
    $stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    
    $result = $stmt->execute();
    
    if ($result) {
        // Récupérer les infos de l'expéditeur
        $senderQuery = "SELECT first_name, last_name, profile_picture FROM users WHERE id = :sender_id";
        $senderStmt = $conn->prepare($senderQuery);
        $senderStmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $senderStmt->execute();
        $senderInfo = $senderStmt->fetch(PDO::FETCH_ASSOC);
        
        // Envoyer la notification push automatiquement
        sendMessagePushNotification($receiver_id, $senderInfo, $content);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message envoyé avec succès!',
            'notification_sent' => true
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi']);
    }
    
} catch (Exception $e) {
    error_log('Erreur envoi message: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}

function sendMessagePushNotification($receiver_id, $senderInfo, $content) {
    try {
        $beamsClient = new \Pusher\PushNotifications\PushNotifications([
            'instanceId' => '4bbe0180-fd1d-4834-84c3-128c682c923d',
            'secretKey' => '07255B4D6A282E46CB5CE36FAB1F71B1CE604D2ABC9F597334F5298AF755126A',
        ]);

        $messagePreview = strlen($content) > 50 ? substr($content, 0, 50) . '...' : $content;
        
        $publishResponse = $beamsClient->publishToInterests(
            ['user-' . $receiver_id],
            [
                'web' => [
                    'notification' => [
                        'title' => '💬 Nouveau message de ' . $senderInfo['first_name'],
                        'body' => '"' . $messagePreview . '"',
                        'icon' => 'https://cdn-icons-png.flaticon.com/512/3193/3193015.png',
                        'badge' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
                        'tag' => 'loove-message',
                        'requireInteraction' => true,
                        'data' => [
                            'type' => 'message',
                            'sender_id' => $senderInfo['id'] ?? 0,
                            'sender_name' => $senderInfo['first_name'],
                            'url' => 'http://localhost/loove/messages.php'
                        ],
                        'actions' => [
                            [
                                'action' => 'view',
                                'title' => '👀 Voir la conversation',
                                'icon' => 'https://cdn-icons-png.flaticon.com/512/159/159604.png'
                            ],
                            [
                                'action' => 'dismiss',
                                'title' => '❌ Ignorer',
                                'icon' => 'https://cdn-icons-png.flaticon.com/512/458/458594.png'
                            ]
                        ]
                    ]
                ]
            ]
        );
        
        error_log('✅ Notification push message envoyée à user-' . $receiver_id);
        return true;
        
    } catch (Exception $e) {
        error_log('❌ Erreur notification push: ' . $e->getMessage());
        return false;
    }
}
?>
