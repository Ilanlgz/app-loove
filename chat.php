<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$chat_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($chat_user_id === 0 || $chat_user_id === $user_id) {
    header("location: messages.php");
    exit;
}

$conn = getDbConnection();

// Vérifier d'abord si la table messages existe et ses colonnes
try {
    $stmt = $conn->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Si la table n'existe pas, la créer
    $conn->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            content TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id),
            FOREIGN KEY (receiver_id) REFERENCES users(id)
        )
    ");
    $columns = ['id', 'sender_id', 'receiver_id', 'content', 'is_read', 'sent_at'];
}

// Déterminer le nom de la colonne timestamp
$timestamp_column = in_array('created_at', $columns) ? 'created_at' : 'sent_at';

// Récupérer les informations de l'utilisateur avec qui on discute
$stmt = $conn->prepare("SELECT id, first_name, last_name, profile_picture, last_active FROM users WHERE id = ?");
$stmt->execute([$chat_user_id]);
$chat_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat_user) {
    header("location: messages.php");
    exit;
}

// Marquer les messages comme lus
$stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
$stmt->execute([$chat_user_id, $user_id]);

// Traitement de l'envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message_content = trim($_POST['message']);
    
    if (!empty($message_content)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, $timestamp_column) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $chat_user_id, $message_content]);
        
        // Redirection pour éviter la resoumission
        header("Location: chat.php?user_id=" . $chat_user_id);
        exit;
    }
}

// Récupérer tous les messages de la conversation
$stmt = $conn->prepare("
    SELECT m.*, u.first_name, u.profile_picture
    FROM messages m
    LEFT JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.$timestamp_column ASC
");
$stmt->execute([$user_id, $chat_user_id, $chat_user_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur est en ligne (actif dans les 5 dernières minutes)
$is_online = $chat_user['last_active'] && (time() - strtotime($chat_user['last_active']) < 300);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat avec <?php echo htmlspecialchars($chat_user['first_name']); ?> - Loove</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/hearts-background.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        /* Navbar identique */
        .header {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            padding: 12px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        .logo {
            font-size: 22px;
            font-weight: 700;
            color: white;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 6px;
            font-size: 20px;
        }
        
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 20px;
            padding-left: 20px;
            border-left: 1px solid rgba(255,255,255,0.3);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: white;
            color: #FF4458;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .btn-logout {
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            background: rgba(255,255,255,0.1);
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 500;
        }

        /* Container du chat */
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            height: calc(100vh - 140px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* En-tête du chat */
        .chat-header {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-radius: 20px 20px 0 0;
        }

        .back-btn {
            color: white;
            font-size: 20px;
            text-decoration: none;
            transition: opacity 0.3s ease;
            padding: 8px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .chat-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .chat-user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            position: relative;
        }

        .chat-user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 12px;
            object-fit: cover;
        }

        .online-dot {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 14px;
            height: 14px;
            background: #22c55e;
            border: 2px solid white;
            border-radius: 50%;
        }

        .chat-user-details h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .user-status {
            font-size: 12px;
            opacity: 0.8;
            margin: 2px 0 0 0;
        }

        /* Zone des messages */
        .messages-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            max-width: 80%;
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.received {
            align-self: flex-start;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
            flex-shrink: 0;
        }

        .message-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 8px;
            object-fit: cover;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 16px;
            position: relative;
            word-wrap: break-word;
            max-width: 100%;
        }

        .message.sent .message-bubble {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.received .message-bubble {
            background: white;
            color: #333;
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 4px;
        }

        .message-content {
            margin: 0;
            line-height: 1.4;
        }

        .message-time {
            font-size: 10px;
            opacity: 0.7;
            margin-top: 4px;
            text-align: right;
        }

        .message.received .message-time {
            text-align: left;
        }

        /* Zone de saisie */
        .message-input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 20px 20px;
        }

        .input-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .message-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 20px;
            font-size: 14px;
            resize: none;
            outline: none;
            font-family: inherit;
            transition: all 0.3s ease;
            min-height: 20px;
            max-height: 100px;
        }

        .message-input:focus {
            border-color: #FF4458;
            box-shadow: 0 0 0 3px rgba(255, 68, 88, 0.1);
        }

        .send-btn {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 68, 88, 0.3);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Indicateur de frappe */
        .typing-indicator {
            display: none;
            padding: 12px 16px;
            background: white;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            margin-bottom: 8px;
            align-self: flex-start;
            max-width: 80px;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            animation: typing 1.4s ease-in-out infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }

        /* État vide */
        .empty-messages {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-messages i {
            font-size: 48px;
            color: #FF4458;
            margin-bottom: 16px;
        }

        .empty-messages h3 {
            margin: 0 0 8px 0;
            color: #333;
        }

        .empty-messages p {
            margin: 0;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .chat-container {
                margin: 10px;
                height: calc(100vh - 100px);
            }
            
            .messages-area {
                padding: 16px;
            }
            
            .message-input-area {
                padding: 16px;
            }
            
            .nav-container {
                padding: 0 16px;
            }
            
            .user-info span {
                display: none;
            }
        }

        /* Scrollbar personnalisée */
        .messages-area::-webkit-scrollbar {
            width: 6px;
        }

        .messages-area::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }

        .messages-area::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Container des coeurs flottants -->
    <div class="hearts-container">
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
    </div>

    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-heart"></i> Loove
            </div>
            <nav class="nav-menu">
                <a href="discover.php" class="nav-link">
                    <i class="fas fa-search"></i> Découvrir
                </a>
                <a href="matches.php" class="nav-link">
                    <i class="fas fa-heart"></i> Matches
                </a>
                <a href="messages.php" class="nav-link active">
                    <i class="fas fa-comment-dots"></i> Messages
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user-circle"></i> Profil
                </a>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION["first_name"], 0, 1)); ?>
                    </div>
                    <span>Bonjour <?php echo htmlspecialchars($_SESSION["first_name"]); ?> !</span>
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <div class="chat-container">
        <!-- En-tête du chat -->
        <div class="chat-header">
            <a href="messages.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="chat-user-info">
                <div class="chat-user-avatar">
                    <?php if (!empty($chat_user['profile_picture'])): ?>
                        <img src="uploads/profiles/<?php echo htmlspecialchars($chat_user['profile_picture']); ?>" alt="Avatar">
                    <?php else: ?>
                        <?php echo strtoupper(substr($chat_user['first_name'], 0, 1)); ?>
                    <?php endif; ?>
                    
                    <?php if ($is_online): ?>
                        <div class="online-dot"></div>
                    <?php endif; ?>
                </div>
                <div class="chat-user-details">
                    <h3><?php echo htmlspecialchars($chat_user['first_name'] . ' ' . $chat_user['last_name']); ?></h3>
                    <p class="user-status">
                        <?php if ($is_online): ?>
                            En ligne
                        <?php else: ?>
                            Dernière connexion <?php echo $chat_user['last_active'] ? date('d/m à H:i', strtotime($chat_user['last_active'])) : 'Inconnue'; ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Zone des messages -->
        <div class="messages-area" id="messagesArea">
            <?php if (empty($messages)): ?>
                <div class="empty-messages">
                    <i class="fas fa-comment-dots"></i>
                    <h3>Nouvelle conversation</h3>
                    <p>Envoyez votre premier message à <?php echo htmlspecialchars($chat_user['first_name']); ?> !</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                        <div class="message-avatar">
                            <?php if ($message['sender_id'] == $user_id): ?>
                                <?php echo strtoupper(substr($_SESSION["first_name"], 0, 1)); ?>
                            <?php else: ?>
                                <?php if (!empty($message['profile_picture'])): ?>
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($message['profile_picture']); ?>" alt="Avatar">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($message['first_name'], 0, 1)); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="message-bubble">
                            <p class="message-content"><?php echo htmlspecialchars($message['content']); ?></p>
                            <div class="message-time">
                                <?php echo date('H:i', strtotime($message[$timestamp_column])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Indicateur de frappe -->
            <div class="typing-indicator" id="typingIndicator">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>

        <!-- Zone de saisie -->
        <div class="message-input-area">
            <form class="input-form" method="POST" id="messageForm">
                <textarea 
                    name="message" 
                    class="message-input" 
                    placeholder="Tapez votre message..." 
                    id="messageInput"
                    rows="1"
                    required
                ></textarea>
                <button type="submit" class="send-btn" id="sendBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        const messagesArea = document.getElementById('messagesArea');
        const messageInput = document.getElementById('messageInput');
        const messageForm = document.getElementById('messageForm');
        const sendBtn = document.getElementById('sendBtn');

        // Faire défiler vers le bas
        function scrollToBottom() {
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        // Auto-resize du textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            
            // Activer/désactiver le bouton d'envoi
            sendBtn.disabled = this.value.trim() === '';
        });

        // Envoi avec Entrée
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim() !== '') {
                    messageForm.submit();
                }
            }
        });

        // Faire défiler vers le bas au chargement
        window.addEventListener('load', function() {
            scrollToBottom();
        });

        // Actualisation automatique des messages (optionnel)
        function refreshMessages() {
            // Ici vous pourriez ajouter une fonction AJAX pour récupérer de nouveaux messages
            // sans recharger la page
        }

        // Vérifier les nouveaux messages toutes les 3 secondes (optionnel)
        // setInterval(refreshMessages, 3000);

        // Animation de l'indicateur de frappe (simulé)
        let typingTimeout;
        messageInput.addEventListener('input', function() {
            // Vous pourriez envoyer une notification en AJAX que l'utilisateur tape
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(function() {
                // Arrêter l'indicateur de frappe
            }, 1000);
        });
    </script>
</body>
</html>
