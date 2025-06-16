<div class="messages-container">
    <div class="messages-header">
        <a href="<?= $baseUrl ?>/profile/view/<?= $recipient['id'] ?>" class="btn-back">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
        <div class="recipient-info">
            <?php if (!empty($recipient['profile_picture'])): ?>
                <div class="recipient-avatar">
                    <img src="<?= $baseUrl ?>/uploads/profiles/<?= htmlspecialchars($recipient['profile_picture']) ?>" alt="Photo de profil">
                </div>
            <?php else: ?>
                <div class="recipient-avatar">
                    <img src="<?= $baseUrl ?>/assets/images/default-avatar.png" alt="Photo de profil par défaut">
                </div>
            <?php endif; ?>
            <div class="recipient-details">
                <h2><?= htmlspecialchars($recipient['name']) ?></h2>
                <span class="recipient-status online">En ligne</span>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($existing_conversation_id): ?>
        <div class="alert alert-info">
            Vous avez déjà une conversation avec cet utilisateur. 
            <a href="<?= $baseUrl ?>/message/view/<?= $existing_conversation_id ?>">Voir la conversation existante</a>.
        </div>
    <?php endif; ?>

    <div class="message-list">
        <!-- Espace pour l'historique des messages -->
        <div class="message-date-separator">
            <span>Aujourd'hui</span>
        </div>
        
        <div class="new-conversation-hint">
            <div class="message received">
                <div class="message-content">
                    <div class="message-bubble">
                        Commencez une nouvelle conversation avec <?= htmlspecialchars($recipient['name']) ?>
                    </div>
                    <div class="message-time">12:00</div>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="<?= $baseUrl ?>/message/new/<?= $recipient['id'] ?>" class="message-form">
        <div class="message-input-wrapper">
            <textarea name="content" placeholder="Écrivez votre message ici..." required></textarea>
            <div class="message-actions">
                <button type="button" class="btn-emoji"><i class="fa fa-smile-o"></i></button>
                <button type="button" class="btn-attach"><i class="fa fa-paperclip"></i></button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-send">
            <i class="fa fa-paper-plane"></i> Envoyer
        </button>
    </form>
</div>

<style>
    /* Styles spécifiques pour améliorer la page de messages */
    .messages-container {
        background-color: white;
        border-radius: 16px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin: 30px auto;
        max-width: 900px;
        display: flex;
        flex-direction: column;
        height: 80vh;
    }
    
    .messages-header {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        background-color: rgba(255, 75, 125, 0.03);
    }
    
    .btn-back {
        margin-right: 20px;
        color: #666;
        font-size: 1.2rem;
        transition: all 0.2s ease;
    }
    
    .btn-back:hover {
        color: #ff4b7d;
        transform: translateX(-3px);
    }
    
    .recipient-info {
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    .recipient-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 15px;
        border: 2px solid #ff4b7d;
        box-shadow: 0 2px 10px rgba(255, 75, 125, 0.2);
    }
    
    .recipient-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .recipient-details h2 {
        margin: 0 0 5px;
        font-size: 1.2rem;
        color: #333;
    }
    
    .recipient-status {
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        color: #666;
    }
    
    .recipient-status.online::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #38b2ac;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .message-list {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: #f8f9ff;
        background-image: 
            radial-gradient(at 10% 20%, rgba(255, 75, 125, 0.03) 0px, transparent 50%),
            radial-gradient(at 90% 80%, rgba(106, 17, 203, 0.03) 0px, transparent 50%);
    }
    
    .message-date-separator {
        text-align: center;
        margin: 15px 0;
        position: relative;
    }
    
    .message-date-separator::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 100%;
        height: 1px;
        background-color: rgba(0, 0, 0, 0.1);
    }
    
    .message-date-separator span {
        position: relative;
        background-color: #f8f9ff;
        padding: 0 15px;
        font-size: 0.8rem;
        color: #888;
    }
    
    .message {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }
    
    .message.received .message-content {
        align-items: flex-start;
    }
    
    .message.sent .message-content {
        align-items: flex-end;
    }
    
    .message-content {
        display: flex;
        flex-direction: column;
    }
    
    .message-bubble {
        padding: 12px 16px;
        border-radius: 18px;
        max-width: 70%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        font-size: 0.95rem;
        word-wrap: break-word;
    }
    
    .message.received .message-bubble {
        background-color: white;
        color: #333;
        border-bottom-left-radius: 4px;
    }
    
    .message.sent .message-bubble {
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }
    
    .message-time {
        font-size: 0.75rem;
        color: #999;
        margin-top: 5px;
        margin-left: 10px;
    }
    
    .message-form {
        padding: 15px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        background-color: white;
        display: flex;
        align-items: flex-end;
        gap: 15px;
    }
    
    .message-input-wrapper {
        flex: 1;
        position: relative;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        background-color: #f8f9ff;
        transition: all 0.3s ease;
    }
    
    .message-input-wrapper:focus-within {
        border-color: #ff4b7d;
        box-shadow: 0 0 0 3px rgba(255, 75, 125, 0.1);
    }
    
    .message-form textarea {
        width: 100%;
        border: none;
        padding: 12px 50px 12px 20px;
        resize: none;
        max-height: 100px;
        background-color: transparent;
        border-radius: 24px;
        font-family: inherit;
        font-size: 0.95rem;
    }
    
    .message-form textarea:focus {
        outline: none;
    }
    
    .message-actions {
        position: absolute;
        right: 10px;
        bottom: 7px;
        display: flex;
        gap: 10px;
    }
    
    .btn-emoji, .btn-attach {
        background: none;
        border: none;
        color: #aaa;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    
    .btn-emoji:hover, .btn-attach:hover {
        color: #ff4b7d;
        background-color: rgba(255, 75, 125, 0.1);
    }
    
    .btn-send {
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        box-shadow: 0 4px 10px rgba(255, 75, 125, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(255, 75, 125, 0.4);
    }
    
    .btn-send i {
        font-size: 1.2rem;
    }
    
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin: 15px 20px 0;
        font-size: 0.95rem;
    }
    
    .alert-danger {
        background-color: #fff5f5;
        color: #e53e3e;
        border: 1px solid #fed7d7;
    }
    
    .alert-info {
        background-color: #ebf8ff;
        color: #3182ce;
        border: 1px solid #bee3f8;
    }
    
    .alert a {
        font-weight: 600;
        color: inherit;
        text-decoration: underline;
    }
    
    .new-conversation-hint {
        text-align: center;
        opacity: 0.7;
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 0.7; transform: translateY(0); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .messages-container {
            margin: 0;
            border-radius: 0;
            height: 100vh;
        }
        
        .message-bubble {
            max-width: 85%;
        }
    }
</style>

<script>
    // Ajout d'un effet de focus automatique sur le textarea
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.querySelector('textarea[name="content"]');
        textarea.focus();
        
        // Ajuster automatiquement la hauteur du textarea
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
</script>
