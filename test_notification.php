<?php
session_start();
require_once 'vendor/autoload.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'] ?? '';
    $test_message = $_POST['test_message'] ?? 'Test de notification push!';
    
    if ($receiver_id) {
        try {
            $beamsClient = new \Pusher\PushNotifications\PushNotifications([
                'instanceId' => '4bbe0180-fd1d-4834-84c3-128c682c923d',
                'secretKey' => '07255B4D6A282E46CB5CE36FAB1F71B1CE604D2ABC9F597334F5298AF755126A',
            ]);

            $publishResponse = $beamsClient->publishToInterests(
                ['user-' . $receiver_id],
                [
                    'web' => [
                        'notification' => [
                            'title' => '💬 Message de ' . $_SESSION['first_name'],
                            'body' => $test_message,
                            'icon' => 'https://cdn-icons-png.flaticon.com/512/3193/3193015.png',
                            'badge' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
                            'tag' => 'loove-test',
                            'requireInteraction' => true,
                            'data' => [
                                'type' => 'message',
                                'url' => 'http://localhost/loove/messages.php'
                            ],
                            'actions' => [
                                [
                                    'action' => 'view',
                                    'title' => '👀 Voir',
                                    'icon' => 'https://cdn-icons-png.flaticon.com/512/159/159604.png'
                                ]
                            ]
                        ]
                    ]
                ]
            );
            
            $message = '✅ Notification envoyée à l\'utilisateur ' . $receiver_id;
            
        } catch (Exception $e) {
            $message = '❌ Erreur: ' . $e->getMessage();
        }
    } else {
        $message = '❌ Veuillez saisir un ID d\'utilisateur';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Notifications Push - Loove</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #FF4458;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        button {
            background: #FF4458;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        
        button:hover {
            background: #E73C4E;
        }
        
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Notifications Push</h1>
        
        <div class="info">
            <strong>Connecté en tant que:</strong> <?= htmlspecialchars($_SESSION['first_name']) ?> (ID: <?= $_SESSION['user_id'] ?>)
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="receiver_id">ID de l'utilisateur destinataire:</label>
                <input type="number" id="receiver_id" name="receiver_id" 
                       placeholder="Exemple: 14" required>
                <small>Saisir l'ID de ton autre compte pour tester</small>
            </div>
            
            <div class="form-group">
                <label for="test_message">Message de test:</label>
                <textarea id="test_message" name="test_message" rows="3" 
                          placeholder="Coucou ! Test de notification push 🎉"><?= $_POST['test_message'] ?? 'Coucou ! Test de notification push 🎉' ?></textarea>
            </div>
            
            <button type="submit">📤 Envoyer la notification push</button>
        </form>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="main.php" style="color: #FF4458;">← Retour à l'accueil</a>
        </div>
    </div>

    <!-- Inclure les notifications push -->
    <script src="https://js.pusher.com/beams/2.1.0/push-notifications-cdn.js"></script>
    <script>
        // Auto-initialisation des notifications push
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const beamsClient = new PusherPushNotifications.Client({
                    instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
                });
                
                await beamsClient.start();
                await beamsClient.addDeviceInterest('user-<?= $_SESSION["user_id"] ?>');
                
                console.log('✅ Prêt à recevoir des notifications');
                
                // Demander permission
                if (Notification.permission === 'default') {
                    await Notification.requestPermission();
                }
            } catch (error) {
                console.error('Erreur notifications:', error);
            }
        });
    </script>
</body>
</html>
