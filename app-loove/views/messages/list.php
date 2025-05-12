<h1>Messages</h1>

<div class="conversations-list">
    <?php if (empty($conversations)): ?>
        <div class="no-conversations">
            <p>Vous n'avez pas encore de messages.</p>
            <p>Commencez à discuter avec d'autres utilisateurs pour les voir ici.</p>
        </div>
    <?php else: ?>
        <?php foreach ($conversations as $conversation): ?>
            <a href="<?= $baseUrl ?>/message/view/<?= $conversation['conversation_id'] ?>" class="conversation-item">
                <div class="conversation-avatar">
                    <?php if ($conversation['profile_picture']): ?>
                        <img src="<?= $baseUrl ?>/uploads/profiles/<?= $conversation['profile_picture'] ?>" alt="<?= htmlspecialchars($conversation['user_name']) ?>">
                    <?php else: ?>
                        <img src="<?= $baseUrl ?>/assets/images/default-avatar.png" alt="Default Avatar">
                    <?php endif; ?>
                </div>
                <div class="conversation-info">
                    <div class="conversation-name"><?= htmlspecialchars($conversation['user_name']) ?></div>
                    <div class="conversation-preview"><?= htmlspecialchars($conversation['last_message']) ?></div>
                </div>
                <div class="conversation-time">
                    <?= date('d M', strtotime($conversation['last_message_time'])) ?>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
