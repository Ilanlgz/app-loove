<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config/database.php';

// Vérifier d'abord si user_id existe dans la session
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Vérifier la structure de la table messages
$conn = getDbConnection();
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

// Récupérer les conversations de l'utilisateur
$stmt = $conn->prepare("
    SELECT DISTINCT
        u.id as user_id,
        u.first_name,
        u.last_name,
        u.profile_picture,
        COALESCE(u.last_active, u.created_at) as last_active,
        (SELECT content FROM messages 
         WHERE (sender_id = ? AND receiver_id = u.id) 
            OR (sender_id = u.id AND receiver_id = ?)
         ORDER BY $timestamp_column DESC LIMIT 1) as last_message,
        (SELECT $timestamp_column FROM messages 
         WHERE (sender_id = ? AND receiver_id = u.id) 
            OR (sender_id = u.id AND receiver_id = ?)
         ORDER BY $timestamp_column DESC LIMIT 1) as last_message_time,
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
    FROM users u
    WHERE u.id IN (
        SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END 
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ?
    )
    ORDER BY last_message_time DESC
");

$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer des utilisateurs suggérés pour commencer une conversation
$stmt = $conn->prepare("
    SELECT id, first_name, last_name, profile_picture 
    FROM users 
    WHERE id != ? 
    AND id NOT IN (
        SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END 
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ?
    )
    LIMIT 5
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$suggested_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Messages - Loove</title>
    <link rel="stylesheet" href="assets/css/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/hearts-background.css">
    <style>        /* Modification du background body pour s'intégrer avec les coeurs */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #FF4458 0%, #FF6B81 25%, #FD5068 50%, #FF8A95 75%, #FFB3C1 100%);
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            z-index: -1;
            animation: floatingColors 20s ease-in-out infinite;
        }

        @keyframes floatingColors {
            0%, 100% { 
                background: 
                    radial-gradient(circle at 20% 80%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 40% 40%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            }
            50% { 
                background: 
                    radial-gradient(circle at 70% 30%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 30% 70%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 60% 60%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            }
        }        /* Header unifié avec taille cohérente */
        .header {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: white;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .logo:hover {
            opacity: 0.8;
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
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            font-size: 14px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        }

        .nav-link i {
            font-size: 16px;
            width: 16px;
            text-align: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 20px;
            padding-left: 20px;
            border-left: 1px solid rgba(255, 255, 255, 0.3);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FF4458;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info span {
            font-weight: 500;
            color: white;
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 13px;
        }

        .btn-logout:hover {
            background: white;
            color: #FF4458;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
        }

        /* Container principal */
        .messages-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
        }

        /* Section conversations */
        .conversations-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f3f4;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .new-message-btn {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .new-message-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 68, 88, 0.3);
        }

        /* Barre de recherche */
        .search-container {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(255, 68, 88, 0.2);
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .search-input:focus {
            border-color: #FF4458;
            box-shadow: 0 0 0 3px rgba(255, 68, 88, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        /* Liste des conversations */
        .conversations-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 500px;
            overflow-y: auto;
        }

        .conversation-item {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .conversation-item:hover {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(255, 68, 88, 0.3);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 68, 88, 0.1);
        }

        .conversation-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            position: relative;
            flex-shrink: 0;
        }

        .conversation-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 12px;
            object-fit: cover;
        }

        .online-indicator {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background: #22c55e;
            border: 2px solid white;
            border-radius: 50%;
        }

        .conversation-content {
            flex: 1;
            min-width: 0;
        }

        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
        }

        .conversation-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .conversation-time {
            font-size: 11px;
            color: #999;
        }

        .conversation-preview {
            color: #666;
            font-size: 12px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread-badge {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            border-radius: 8px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
            min-width: 16px;
            text-align: center;
            margin-left: 8px;
        }

        /* Section suggestions */
        .suggestions-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .suggestions-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .suggestion-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .suggestion-item:hover {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(255, 68, 88, 0.3);
            transform: translateY(-1px);
        }

        .suggestion-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }

        .suggestion-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            object-fit: cover;
        }

        .suggestion-info {
            flex: 1;
        }

        .suggestion-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin: 0 0 2px 0;
        }

        .suggestion-status {
            font-size: 12px;
            color: #666;
            margin: 0;
        }

        .start-chat-btn {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .start-chat-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(255, 68, 88, 0.3);
        }

        /* État vide */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 48px;
            color: #FF4458;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 18px;
            margin: 0 0 8px 0;
            color: #333;
        }

        .empty-state p {
            margin: 0 0 20px 0;
            font-size: 14px;
        }

        .discover-btn {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .discover-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 68, 88, 0.3);
        }

        /* Modal nouvelle conversation */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 20px;
            padding: 24px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .messages-container {
                grid-template-columns: 1fr;
                margin: 20px 16px;
                padding: 16px;
                gap: 16px;
            }
            
            .nav-container {
                padding: 0 16px;
            }
            
            .user-info span {
                display: none;
            }
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

       <?php include 'includes/navbar.php'; ?>

    <div class="messages-container">
        <!-- Section des conversations -->
        <div class="conversations-section">
            <div class="section-header">
                <h2 class="section-title">Messages</h2>
                <button class="new-message-btn" onclick="openNewMessageModal()">
                    <i class="fas fa-plus"></i>
                    Nouveau
                </button>
            </div>

            <div class="search-container">
                <input type="text" class="search-input" placeholder="Rechercher une conversation..." id="searchInput">
                <i class="fas fa-search search-icon"></i>
            </div>

            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>Aucune conversation</h3>
                    <p>Commencez à découvrir de nouveaux profils pour débuter des conversations !</p>
                    <a href="discover.php" class="discover-btn">
                        <i class="fas fa-heart"></i>
                        Découvrir des profils
                    </a>
                </div>
            <?php else: ?>
                <div class="conversations-list">
                    <?php foreach ($conversations as $conversation): ?>
                        <a href="chat.php?user_id=<?php echo $conversation['user_id']; ?>" class="conversation-item">
                            <div class="conversation-avatar">
                                <?php if (!empty($conversation['profile_picture'])): ?>
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($conversation['profile_picture']); ?>" alt="Avatar">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($conversation['first_name'], 0, 1)); ?>
                                <?php endif; ?>
                                
                                <?php 
                                $is_online = isset($conversation['last_active']) && 
                                           (time() - strtotime($conversation['last_active']) < 300);
                                if ($is_online): 
                                ?>
                                    <div class="online-indicator"></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="conversation-content">
                                <div class="conversation-header">
                                    <h4 class="conversation-name">
                                        <?php echo htmlspecialchars($conversation['first_name'] . ' ' . $conversation['last_name']); ?>
                                    </h4>
                                    <span class="conversation-time">
                                        <?php 
                                        if ($conversation['last_message_time']) {
                                            echo date('H:i', strtotime($conversation['last_message_time']));
                                        }
                                        ?>
                                    </span>
                                </div>
                                <p class="conversation-preview">
                                    <?php echo htmlspecialchars($conversation['last_message'] ?? 'Démarrer une conversation...'); ?>
                                </p>
                            </div>
                            
                            <?php if ($conversation['unread_count'] > 0): ?>
                                <span class="unread-badge"><?php echo $conversation['unread_count']; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section suggestions -->
        <div class="suggestions-section">
            <div class="section-header">
                <h3 class="section-title">Suggestions</h3>
            </div>

            <?php if (empty($suggested_users)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-plus"></i>
                    <h4>Aucune suggestion</h4>
                    <p>Découvrez de nouveaux profils pour voir des suggestions ici.</p>
                </div>
            <?php else: ?>
                <div class="suggestions-list">
                    <?php foreach ($suggested_users as $user): ?>
                        <div class="suggestion-item">
                            <div class="suggestion-avatar">
                                <?php if (!empty($user['profile_picture'])): ?>
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Avatar">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="suggestion-info">
                                <h5 class="suggestion-name">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </h5>
                                <p class="suggestion-status">Nouveau match potentiel</p>
                            </div>
                            
                            <button class="start-chat-btn" onclick="startChat(<?php echo $user['id']; ?>)">
                                <i class="fas fa-comment"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal nouvelle conversation -->
    <div class="modal" id="newMessageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Nouvelle conversation</h3>
                <button class="close-btn" onclick="closeNewMessageModal()">×</button>
            </div>
            
            <div class="suggestions-list">
                <?php foreach ($suggested_users as $user): ?>
                    <div class="suggestion-item" onclick="startChat(<?php echo $user['id']; ?>)">
                        <div class="suggestion-avatar">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Avatar">
                            <?php else: ?>
                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="suggestion-info">
                            <h5 class="suggestion-name">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </h5>
                            <p class="suggestion-status">Cliquez pour démarrer une conversation</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Recherche en temps réel
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const conversations = document.querySelectorAll('.conversation-item');
            
            conversations.forEach(conv => {
                const name = conv.querySelector('.conversation-name').textContent.toLowerCase();
                const preview = conv.querySelector('.conversation-preview').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || preview.includes(searchTerm)) {
                    conv.style.display = 'flex';
                } else {
                    conv.style.display = 'none';
                }
            });
        });

        // Modal nouvelle conversation
        function openNewMessageModal() {
            document.getElementById('newMessageModal').style.display = 'block';
        }

        function closeNewMessageModal() {
            document.getElementById('newMessageModal').style.display = 'none';
        }

        // Démarrer une conversation
        function startChat(userId) {
            window.location.href = `chat.php?user_id=${userId}`;
        }

        // Fermer le modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('newMessageModal');
            if (event.target === modal) {
                closeNewMessageModal();
            }
        }    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
