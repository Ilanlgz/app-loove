<div class="messages-dashboard">
    <div class="messages-sidebar">
        <div class="messages-header">
            <h2>Mes messages</h2>
            <button class="btn-new-message" title="Nouvelle conversation">
                <i class="fa fa-pencil-square-o"></i>
            </button>
        </div>
        
        <div class="search-messages">
            <div class="search-input-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input type="text" placeholder="Rechercher un message..." class="search-input">
            </div>
        </div>
        
        <div class="conversation-filters">
            <button class="filter-btn active">Tous</button>
            <button class="filter-btn">Non lus</button>
            <button class="filter-btn">Favoris</button>
        </div>
        
        <div class="conversations-list">
            <?php if (empty($conversations)): ?>
                <div class="no-conversations">
                    <div class="empty-state">
                        <img src="<?= $baseUrl ?>/assets/images/empty-messages.svg" alt="Pas de messages" class="empty-icon">
                        <h3>Aucun message</h3>
                        <p>Commencez à discuter avec d'autres membres pour voir vos conversations ici.</p>
                        <a href="<?= $baseUrl ?>/search" class="btn btn-primary mt-3">Trouver des profils</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $index => $conversation): ?>
                    <a href="<?= $baseUrl ?>/message/view/<?= $conversation['conversation_id'] ?>" class="conversation-item <?= $index === 0 ? 'active' : '' ?> <?= isset($conversation['unread']) && $conversation['unread'] > 0 ? 'unread' : '' ?>">
                        <div class="conversation-avatar">
                            <?php if (!empty($conversation['profile_picture'])): ?>
                                <img src="<?= $baseUrl ?>/uploads/profiles/<?= htmlspecialchars($conversation['profile_picture']) ?>" alt="Photo de profil">
                            <?php else: ?>
                                <img src="<?= $baseUrl ?>/assets/images/default-avatar.png" alt="Photo de profil par défaut">
                            <?php endif; ?>
                            <?php if ($index % 3 === 0): ?>
                                <span class="status-indicator online"></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="conversation-details">
                            <div class="conversation-top">
                                <h3 class="conversation-name"><?= htmlspecialchars($conversation['user_name']) ?></h3>
                                <span class="conversation-time"><?= date('H:i', strtotime($conversation['last_message_time'])) ?></span>
                            </div>
                            
                            <div class="conversation-bottom">
                                <p class="conversation-preview"><?= htmlspecialchars(substr($conversation['last_message'], 0, 40) . (strlen($conversation['last_message']) > 40 ? '...' : '')) ?></p>
                                
                                <?php if (isset($conversation['unread']) && $conversation['unread'] > 0): ?>
                                    <span class="unread-badge"><?= $conversation['unread'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="messages-welcome">
        <div class="welcome-content">
            <img src="<?= $baseUrl ?>/assets/images/messages-illustration.svg" alt="Messages" class="welcome-image">
            <h2>Bienvenue dans vos messages</h2>
            <p>Sélectionnez une conversation ou commencez une nouvelle discussion</p>
            <a href="<?= $baseUrl ?>/search" class="btn btn-primary">Trouver des profils</a>
        </div>
    </div>
</div>

<style>
    /* Styles pour la page des messages avec des couleurs améliorées */
    .messages-dashboard {
        display: flex;
        background-color: white;
        border-radius: 16px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin: 30px auto;
        max-width: 1200px;
        height: 85vh;
        position: relative;
    }
    
    /* Barre colorée en haut */
    .messages-dashboard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        z-index: 1;
    }
    
    .messages-sidebar {
        width: 350px;
        border-right: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        background-color: #f8f9ff;
        background-image: 
            radial-gradient(at 10% 10%, rgba(255, 75, 125, 0.02) 0px, transparent 50%),
            radial-gradient(at 90% 90%, rgba(106, 17, 203, 0.02) 0px, transparent 50%);
    }
    
    .messages-header {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: white;
    }
    
    .messages-header h2 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .btn-new-message {
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(255, 75, 125, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-new-message:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 15px rgba(255, 75, 125, 0.4);
    }
    
    .btn-new-message i {
        font-size: 1.2rem;
    }
    
    .search-messages {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: rgba(248, 249, 255, 0.8);
    }
    
    .search-input-wrapper {
        position: relative;
    }
    
    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
    }
    
    .search-input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background-color: white;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }
    
    .search-input:focus {
        outline: none;
        border-color: #ff4b7d;
        box-shadow: 0 0 0 3px rgba(255, 75, 125, 0.1);
    }
    
    .conversation-filters {
        display: flex;
        padding: 10px 20px;
        gap: 10px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: rgba(248, 249, 255, 0.6);
    }
    
    .filter-btn {
        background: none;
        border: none;
        padding: 8px 12px;
        border-radius: 15px;
        font-size: 0.9rem;
        color: #666;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .filter-btn:hover {
        background-color: rgba(255, 75, 125, 0.08);
        color: #ff4b7d;
    }
    
    .filter-btn.active {
        background: linear-gradient(135deg, rgba(255, 75, 125, 0.12) 0%, rgba(255, 146, 113, 0.12) 100%);
        color: #ff4b7d;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(255, 75, 125, 0.1);
    }
    
    .conversations-list {
        flex: 1;
        overflow-y: auto;
    }
    
    .conversation-item {
        display: flex;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        text-decoration: none;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .conversation-item:hover {
        background-color: rgba(255, 75, 125, 0.05);
    }
    
    .conversation-item.active {
        background-color: rgba(255, 75, 125, 0.08);
        border-left: 3px solid #ff4b7d;
    }
    
    .conversation-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background: linear-gradient(to bottom, #ff4b7d, #ff9271);
    }
    
    .conversation-item.unread {
        background-color: rgba(255, 75, 125, 0.08);
    }
    
    .conversation-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 15px;
        position: relative;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        border: 2px solid white;
    }
    
    .conversation-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .conversation-item:hover .conversation-avatar img {
        transform: scale(1.05);
    }
    
    .status-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    
    .status-indicator.online {
        background: linear-gradient(135deg, #38b2ac 0%, #4fd1c5 100%);
    }
    
    .conversation-details {
        flex: 1;
        min-width: 0;
    }
    
    .conversation-top {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    
    .conversation-name {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .conversation-time {
        font-size: 0.8rem;
        color: #999;
        white-space: nowrap;
    }
    
    .conversation-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .conversation-preview {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 70%;
    }
    
    .unread-badge {
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 20px;
        height: 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        box-shadow: 0 2px 5px rgba(255, 75, 125, 0.3);
    }
    
    .messages-welcome {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: white;
        background-image: 
            radial-gradient(at 10% 20%, rgba(255, 75, 125, 0.03) 0px, transparent 50%),
            radial-gradient(at 90% 80%, rgba(106, 17, 203, 0.03) 0px, transparent 50%);
    }
    
    .welcome-content {
        text-align: center;
        max-width: 500px;
        padding: 20px;
        animation: fadeIn 0.5s ease;
    }
    
    .welcome-image {
        width: 200px;
        margin-bottom: 30px;
        filter: drop-shadow(0 5px 15px rgba(255, 75, 125, 0.2));
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    
    .welcome-content h2 {
        font-size: 1.8rem;
        margin-bottom: 15px;
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .welcome-content p {
        color: #666;
        margin-bottom: 30px;
        font-size: 1.1rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        animation: fadeIn 0.5s ease;
    }
    
    .empty-icon {
        width: 120px;
        margin-bottom: 20px;
        opacity: 0.7;
        filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.1));
    }
    
    .empty-state h3 {
        font-size: 1.3rem;
        margin-bottom: 10px;
        color: #333;
    }
    
    .empty-state p {
        color: #666;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }
    
    .btn {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(255, 75, 125, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(255, 75, 125, 0.4);
    }
    
    .mt-3 {
        margin-top: 15px;
    }
    
    /* Scrollbar personnalisée */
    .conversations-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .conversations-list::-webkit-scrollbar-track {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .conversations-list::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, rgba(255, 75, 125, 0.5), rgba(255, 146, 113, 0.5));
        border-radius: 3px;
    }
    
    .conversations-list::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, rgba(255, 75, 125, 0.7), rgba(255, 146, 113, 0.7));
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .messages-dashboard {
            flex-direction: column;
            height: auto;
            margin: 0;
            border-radius: 0;
        }
        
        .messages-sidebar {
            width: 100%;
            height: 500px;
        }
        
        .messages-welcome {
            padding: 40px 20px;
        }
    }
    
    @media (max-width: 576px) {
        .conversation-item {
            padding: 10px 15px;
        }
        
        .conversation-avatar {
            width: 40px;
            height: 40px;
        }
        
        .conversation-filters {
            padding: 10px;
            gap: 5px;
        }
        
        .filter-btn {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activer le filtre sélectionné
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Animation d'apparition des conversations
    const conversationItems = document.querySelectorAll('.conversation-item');
    conversationItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(10px)';
        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        item.style.transitionDelay = (index * 0.05) + 's';
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 100);
    });
});
</script>
