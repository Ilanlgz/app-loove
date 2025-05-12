<div class="messages-container animate-slide-up">
    <div class="messages-header">
        <a href="<?= $baseUrl ?>/profile/view/<?= $recipient['id'] ?>" class="btn-back">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
        <h2>Nouveau message à <?= htmlspecialchars($recipient['name']) ?></h2>
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
        <!-- Espace pour l'historique des messages (vide pour un nouveau message) -->
        <div class="new-conversation-hint">
            <div class="message received">
                <div class="message-content">
                    Commencez une nouvelle conversation avec <?= htmlspecialchars($recipient['name']) ?>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="<?= $baseUrl ?>/message/new/<?= $recipient['id'] ?>" class="message-form">
        <textarea name="content" placeholder="Écrivez votre message ici..." required></textarea>
        <button type="submit" class="btn btn-primary">
            Envoyer
        </button>
    </form>
</div>

<script>
    // Ajout d'un effet de focus automatique sur le textarea
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('textarea[name="content"]').focus();
    });
</script>
