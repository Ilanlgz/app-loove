<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Créer les tables de messages si elles n'existent pas
require_once 'config/database.php';
try {
    $conn = getDbConnection();
    
    // Table des conversations
    $conversationsQuery = "CREATE TABLE IF NOT EXISTS conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user1_id INT NOT NULL,
        user2_id INT NOT NULL,
        last_message TEXT,
        last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user1 (user1_id),
        INDEX idx_user2 (user2_id),
        UNIQUE KEY unique_conversation (user1_id, user2_id)
    )";
    
    // Table des messages
    $messagesQuery = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        message_text TEXT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE,
        INDEX idx_conversation (conversation_id),
        INDEX idx_from_user (from_user_id),
        INDEX idx_to_user (to_user_id)
    )";
    
    $conn->exec($conversationsQuery);
    $conn->exec($messagesQuery);
    
} catch (PDOException $e) {
    // Ignorer les erreurs de création de tables
}

require_once 'classes/Message.php';

$messageSystem = new Message();
$conversations = $messageSystem->getUserConversations($_SESSION["user_id"]);

// Si un chat spécifique est demandé
$active_chat = isset($_GET['chat']) ? (int)$_GET['chat'] : null;
$active_messages = [];
$active_user = null;

if ($active_chat) {
    $active_messages = $messageSystem->getConversationMessages($_SESSION["user_id"], $active_chat);
    
    // Récupérer les infos de l'utilisateur
    $conn = getDbConnection();
    $query = "SELECT first_name, last_name, profile_picture FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $active_chat, PDO::PARAM_INT);
    $stmt->execute();
    $active_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF4458;
            --secondary-color: #FD5068;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --background: #FAFAFA;
            --white: #FFFFFF;
            --success: #34C759;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
        }

        .header {
            background: var(--white);
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-link {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
            background: rgba(255, 68, 88, 0.1);
        }

        .btn-logout {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .messages-container {
            display: flex;
            height: calc(100vh - 100px);
            max-width: 1200px;
            margin: 0 auto;
        }

        .conversations-panel {
            width: 350px;
            background: var(--white);
            border-right: 1px solid #E8E8E8;
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            padding: 25px 20px;
            border-bottom: 1px solid #E8E8E8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .search-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }

        .conversation-item {
            padding: 20px;
            border-bottom: 1px solid #F0F0F0;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .conversation-item:hover, .conversation-item.active {
            background: rgba(255, 68, 88, 0.05);
        }

        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            font-size: 1.2rem;
        }

        .conversation-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .conversation-info {
            flex: 1;
        }

        .conversation-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .conversation-preview {
            color: var(--text-secondary);
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--white);
        }

        .chat-header {
            padding: 20px 25px;
            border-bottom: 1px solid #E8E8E8;
            display: flex;
            align-items: center;
            gap: 15px;
            background: var(--white);
        }

        .chat-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
        }

        .chat-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .chat-info h3 {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .chat-status {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .messages-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #F8F8F8;
        }

        .message {
            display: flex;
            margin-bottom: 20px;
            animation: fadeInUp 0.3s ease;
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 70%;
            padding: 15px 20px;
            border-radius: 20px;
            position: relative;
        }

        .message.received .message-content {
            background: var(--white);
            border-bottom-left-radius: 5px;
        }

        .message.sent .message-content {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border-bottom-right-radius: 5px;
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
        }

        .message-input-area {
            padding: 20px 25px;
            background: var(--white);
            border-top: 1px solid #E8E8E8;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .message-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #E8E8E8;
            border-radius: 25px;
            font-size: 1rem;
            font-family: inherit;
            outline: none;
            transition: all 0.3s ease;
        }

        .message-input:focus {
            border-color: var(--primary-color);
        }

        .send-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-btn:hover {
            transform: scale(1.1);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: var(--text-secondary);
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--primary-color);
            opacity: 0.5;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modal de recherche */
        .search-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .search-content {
            background: var(--white);
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
        }

        .search-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #E8E8E8;
            border-radius: 12px;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .search-results {
            max-height: 300px;
            overflow-y: auto;
        }

        .search-result-item {
            padding: 15px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .search-result-item:hover {
            background: rgba(255, 68, 88, 0.1);
        }

        @media (max-width: 768px) {
            .messages-container {
                flex-direction: column;
            }
            
            .conversations-panel {
                width: 100%;
                height: 40%;
            }
            
            .chat-panel {
                height: 60%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="main.php" class="logo">
                <i class="fas fa-heart"></i> Loove
            </a>
            <nav class="nav-menu">
                <a href="discover.php" class="nav-link">
                    <i class="fas fa-search"></i> Découvrir
                </a>
                <a href="matches.php" class="nav-link">
                    <i class="fas fa-heart"></i> Matches
                </a>
                <a href="messages.php" class="nav-link active">
                    <i class="fas fa-comments"></i> Messages
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </nav>
        </div>
    </header>

    <div class="messages-container">
        <!-- Panel des conversations -->
        <div class="conversations-panel">
            <div class="panel-header">
                <h2 class="panel-title">Messages</h2>
                <button class="search-btn" onclick="openSearchModal()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div class="conversations-list">
                <?php if (empty($conversations)): ?>
                    <div style="padding: 40px 20px; text-align: center; color: var(--text-secondary);">
                        <i class="fas fa-comment-dots" style="font-size: 2rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>Aucune conversation</p>
                        <p style="font-size: 0.9rem; margin-top: 5px;">Commencez à discuter avec vos matches !</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conversation): ?>
                        <div class="conversation-item <?php echo $active_chat == $conversation['other_user_id'] ? 'active' : ''; ?>" 
                             onclick="openChat(<?php echo $conversation['other_user_id']; ?>)">
                            <div class="conversation-avatar">
                                <?php if ($conversation['other_user_picture']): ?>
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($conversation['other_user_picture']); ?>" alt="">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($conversation['other_user_name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-name"><?php echo htmlspecialchars($conversation['other_user_name']); ?></div>
                                <div class="conversation-preview">
                                    <?php echo $conversation['last_message'] ? htmlspecialchars(substr($conversation['last_message'], 0, 40)) . '...' : 'Nouvelle conversation'; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Panel de chat -->
        <div class="chat-panel">
            <?php if ($active_chat && $active_user): ?>
                <div class="chat-header">
                    <div class="chat-avatar">
                        <?php if ($active_user['profile_picture']): ?>
                            <img src="uploads/profiles/<?php echo htmlspecialchars($active_user['profile_picture']); ?>" alt="">
                        <?php else: ?>
                            <?php echo strtoupper(substr($active_user['first_name'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="chat-info">
                        <h3><?php echo htmlspecialchars($active_user['first_name'] . ' ' . substr($active_user['last_name'], 0, 1) . '.'); ?></h3>
                        <div class="chat-status">En ligne</div>
                    </div>
                </div>

                <div class="messages-area" id="messagesArea">
                    <?php foreach ($active_messages as $message): ?>
                        <div class="message <?php echo $message['from_user_id'] == $_SESSION['user_id'] ? 'sent' : 'received'; ?>">
                            <div class="message-content">
                                <?php echo htmlspecialchars($message['message_text']); ?>
                                <div class="message-time">
                                    <?php echo date('H:i', strtotime($message['sent_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="message-input-area">
                    <input type="text" class="message-input" id="messageInput" placeholder="Tapez votre message..." onkeypress="handleKeyPress(event)">
                    <button class="send-btn" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>Sélectionnez une conversation</h3>
                    <p>Choisissez une conversation pour commencer à discuter</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de recherche -->
    <div class="search-modal" id="searchModal">
        <div class="search-content">
            <h3 style="margin-bottom: 20px;">Rechercher des utilisateurs</h3>
            <input type="text" class="search-input" id="searchInput" placeholder="Rechercher par nom..." oninput="searchUsers()">
            <div class="search-results" id="searchResults"></div>
            <button onclick="closeSearchModal()" style="margin-top: 20px; padding: 10px 20px; background: var(--text-secondary); color: white; border: none; border-radius: 8px; cursor: pointer;">
                Fermer
            </button>
        </div>
    </div>

    <script>
        function openChat(userId) {
            window.location.href = `messages.php?chat=${userId}`;
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) {
                alert('Veuillez saisir un message');
                return;
            }
            
            const chatUserId = <?php echo $active_chat ?: 'null'; ?>;
            if (!chatUserId) {
                alert('Aucune conversation sélectionnée');
                return;
            }
            
            // Désactiver le bouton d'envoi
            const sendBtn = document.querySelector('.send-btn');
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    to_user_id: chatUserId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse du serveur:', data); // Debug
                
                if (data.success) {
                    input.value = '';
                    addMessageToChat(message, true);
                    scrollToBottom();
                } else {
                    alert('Erreur: ' + (data.error || 'Impossible d\'envoyer le message'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de connexion. Veuillez réessayer.');
            })
            .finally(() => {
                // Réactiver le bouton
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            });
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }

        function addMessageToChat(message, isSent) {
            const messagesArea = document.getElementById('messagesArea');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${message.replace(/\n/g, '<br>')}
                    <div class="message-time">
                        ${new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}
                    </div>
                </div>
            `;
            messagesArea.appendChild(messageDiv);
        }

        function scrollToBottom() {
            const messagesArea = document.getElementById('messagesArea');
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function openSearchModal() {
            document.getElementById('searchModal').style.display = 'flex';
        }

        function closeSearchModal() {
            document.getElementById('searchModal').style.display = 'none';
        }

        function searchUsers() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            
            if (searchTerm.length < 2) {
                document.getElementById('searchResults').innerHTML = '';
                return;
            }
            
            fetch('search_users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    search: searchTerm
                })
            })
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data.users || []);
            })
            .catch(error => console.error('Erreur:', error));
        }

        function displaySearchResults(users) {
            const resultsDiv = document.getElementById('searchResults');
            
            if (users.length === 0) {
                resultsDiv.innerHTML = '<p style="text-align: center; color: var(--text-secondary); padding: 20px;">Aucun utilisateur trouvé</p>';
                return;
            }
            
            resultsDiv.innerHTML = users.map(user => `
                <div class="search-result-item" onclick="startConversation(${user.id})">
                    <div class="conversation-avatar">
                        ${user.profile_picture 
                            ? `<img src="uploads/profiles/${user.profile_picture}" alt="">`
                            : user.first_name.charAt(0).toUpperCase()
                        }
                    </div>
                    <div>
                        <div style="font-weight: 600;">${user.first_name} ${user.last_name}</div>
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">${user.location || ''}</div>
                    </div>
                    ${user.is_premium ? '<div style="margin-left: auto; color: gold;"><i class="fas fa-crown"></i></div>' : ''}
                </div>
            `).join('');
        }

        function startConversation(userId) {
            closeSearchModal();
            openChat(userId);
        }

        // Auto-scroll au chargement
        if (document.getElementById('messagesArea')) {
            scrollToBottom();
        }
    </script>
</body>
</html>
