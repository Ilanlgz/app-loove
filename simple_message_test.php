<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'] ?? '';
    $test_message = $_POST['test_message'] ?? 'Test de notification push!';
    
    if ($receiver_id) {
        try {
            // Envoyer directement la notification push avec cURL
            $data = [
                'interests' => ['user-' . $receiver_id],
                'web' => [
                    'notification' => [
                        'title' => '💬 Message de ' . $_SESSION['first_name'],
                        'body' => $test_message,
                        'icon' => 'https://cdn-icons-png.flaticon.com/512/3193/3193015.png',
                        'badge' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
                        'tag' => 'loove-message-' . time(),
                        'requireInteraction' => true,
                        'data' => [
                            'type' => 'message',
                            'sender_id' => $_SESSION['user_id'],
                            'sender_name' => $_SESSION['first_name'],
                            'url' => 'http://localhost/loove/messages.php'
                        ]
                    ]
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://4bbe0180-fd1d-4834-84c3-128c682c923d.pushnotifications.pusher.com/publish_api/v1/instances/4bbe0180-fd1d-4834-84c3-128c682c923d/publishes');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer 07255B4D6A282E46CB5CE36FAB1F71B1CE604D2ABC9F597334F5298AF755126A'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $message = '✅ Notification push envoyée avec succès à l\'utilisateur ' . $receiver_id . '!';
                $success = true;
            } else {
                $message = '❌ Erreur HTTP: ' . $httpCode . ' - ' . $response;
            }
            
        } catch (Exception $e) {
            $message = '❌ Erreur: ' . $e->getMessage();
        }
    } else {
        $message = '❌ Veuillez saisir un ID d\'utilisateur';
    }
}

// Récupérer la liste des utilisateurs
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
    <title>Test Notifications Push Simple - Loove</title>
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
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #FF4458;
            text-decoration: none;
        }
        
        .instructions {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .instructions h3 {
            color: #0066cc;
            margin-bottom: 10px;
        }
        
        .instructions ol {
            margin-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📲 Test Notifications Push Direct</h1>
        
        <div class="user-info">
            <strong>Connecté en tant que:</strong> <?= htmlspecialchars($_SESSION['first_name']) ?> (ID: <?= $_SESSION['user_id'] ?>)
        </div>
        
        <div class="instructions">
            <h3>📝 Instructions :</h3>
            <ol>
                <li>Ouvre ton autre compte dans un autre onglet</li>
                <li>Va sur la page principale pour activer les notifications</li>
                <li>Accepte les notifications quand le navigateur demande</li>
                <li>Reviens ici et envoie un message à ton autre compte</li>
                <li>Tu devrais voir la notification apparaître ! 🎉</li>
            </ol>
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
                <label for="test_message">💭 Message:</label>
                <textarea id="test_message" name="test_message" rows="3" 
                          placeholder="Coucou ! Test de notification 🎉" required><?= htmlspecialchars($_POST['test_message'] ?? 'Coucou ! Test de notification 🎉') ?></textarea>
            </div>
            
            <button type="submit">📤 Envoyer Notification Push</button>
        </form>
        
        <?php if ($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <a href="main.php" class="back-link">← Retour à l'accueil</a>
    </div>

    <!-- Auto-activation des notifications -->
    <script src="https://js.pusher.com/beams/2.1.0/push-notifications-cdn.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                console.log('🚀 Initialisation notifications...');
                
                const beamsClient = new PusherPushNotifications.Client({
                    instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
                });
                
                await beamsClient.start();
                await beamsClient.addDeviceInterest('user-<?= $_SESSION["user_id"] ?>');
                
                console.log('✅ Prêt à recevoir des notifications pour user-<?= $_SESSION["user_id"] ?>');
                
                // Demander permission
                if (Notification.permission === 'default') {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        console.log('✅ Permission accordée');
                    }
                }
            } catch (error) {
                console.error('❌ Erreur:', error);
            }
        });
    </script>
</body>
</html>
