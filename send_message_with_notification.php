<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'] ?? '';
    $content = trim($_POST['content'] ?? '');
    $sender_id = $_SESSION['user_id'];
    
    if ($receiver_id && $content) {
        try {
            $conn = getDbConnection();
            
            // 1. Sauvegarder le message en base de données
            $query = "INSERT INTO messages (sender_id, receiver_id, content, sent_at) 
                      VALUES (:sender_id, :receiver_id, :content, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
            $stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                // 2. Envoyer la notification push automatiquement
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
                                'title' => '💬 Message de ' . $_SESSION['first_name'],
                                'body' => '"' . $messagePreview . '"',
                                'icon' => 'https://cdn-icons-png.flaticon.com/512/3193/3193015.png',
                                'badge' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
                                'tag' => 'loove-message-' . time(),
                                'requireInteraction' => true,
                                'data' => [
                                    'type' => 'message',
                                    'sender_id' => $sender_id,
                                    'sender_name' => $_SESSION['first_name'],
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
                                        'title' => '❌ Plus tard',
                                        'icon' => 'https://cdn-icons-png.flaticon.com/512/458/458594.png'
                                    ]
                                ]
                            ]
                        ]
                    ]
                );
                
                $message = '✅ Message envoyé ET notification push automatique envoyée !';
                $success = true;
            }
            
        } catch (Exception $e) {
            $message = '❌ Erreur: ' . $e->getMessage();
        }
    } else {
        $message = '❌ Veuillez remplir tous les champs';
    }
}

// Récupérer la liste des utilisateurs pour le sélecteur
$conn = getDbConnection();
$usersQuery = "SELECT id, first_name, last_name FROM users WHERE id != :current_user ORDER BY first_name";
$usersStmt = $conn->prepare($usersQuery);
$usersStmt->bindParam(':current_user', $_SESSION['user_id'], PDO::PARAM_INT);
$usersStmt->execute();
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un Message - Loove</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #FF4458, #FD5068);
            min-height: 100vh;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #FF4458;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        select:focus, textarea:focus {
            outline: none;
            border-color: #FF4458;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        button {
            background: linear-gradient(135deg, #FF4458, #FD5068);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.3s ease;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
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
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #FF4458;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💬 Envoyer un Message</h1>
        
        <div class="user-info">
            <strong>Connecté en tant que:</strong> <?= htmlspecialchars($_SESSION['first_name']) ?> (ID: <?= $_SESSION['user_id'] ?>)
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="receiver_id">👤 Destinataire:</label>
                <select id="receiver_id" name="receiver_id" required>
                    <option value="">Choisir un destinataire...</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= ($_POST['receiver_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> (ID: <?= $user['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="content">💭 Votre message:</label>
                <textarea id="content" name="content" 
                          placeholder="Tapez votre message ici..." required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
            </div>
            
            <button type="submit">📤 Envoyer + Notification Push</button>
        </form>
        
        <?php if ($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <a href="main.php" class="back-link">← Retour à l'accueil</a>
    </div>

    <!-- Notifications push pour recevoir -->
    <script src="https://js.pusher.com/beams/2.1.0/push-notifications-cdn.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const beamsClient = new PusherPushNotifications.Client({
                    instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
                });
                
                await beamsClient.start();
                await beamsClient.addDeviceInterest('user-<?= $_SESSION["user_id"] ?>');
                
                console.log('✅ Prêt à recevoir des notifications');
                
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
