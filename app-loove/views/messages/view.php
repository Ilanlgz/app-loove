<div class="messages-container">
    <div class="messages-header">
        <a href="<?= $baseUrl ?>/message" class="btn btn-back"><i class="fa fa-arrow-left"></i> Retour</a>
        <h2>Conversation avec <?= htmlspecialchars($other_user['name']) ?></h2>
    </div>

    <div class="message-list">
        <?php if (empty($messages)): ?>
            <p class="no-messages">Aucun message pour le moment. Commencez à discuter !</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message <?= $message['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received' ?>">
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($message['content'])) ?>
                    </div>
                    <div class="message-time">
                        <?= date('d M H:i', strtotime($message['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form method="post" action="<?= $baseUrl ?>/message/view/<?= $conversation_id ?>" class="message-form">
        <textarea name="content" placeholder="Tapez votre message ici..." required></textarea>
        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
</div>

<script>
    // Scroll to bottom of messages when page loads
    window.onload = function() {
        const messageList = document.querySelector('.message-list');
        messageList.scrollTop = messageList.scrollHeight;
    };
</script>
