<?php
// Include header
include '../layouts/header.php';
?>

<div class="search-results">
    <h2>Résultats de recherche</h2>
    <?php if (isset($searchResults) && is_array($searchResults) && !empty($searchResults)): ?>
        <ul class="search-results-list">
            <?php foreach ($searchResults as $user): ?>
                <li class="user-card">
                    <div class="user-image">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo $baseUrl; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil">
                        <?php else: ?>
                            <img src="<?php echo $baseUrl; ?>/assets/images/default-avatar.png" alt="Photo de profil par défaut">
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                        <?php if (isset($user['age'])): ?>
                            <p class="user-age"><?php echo htmlspecialchars($user['age']); ?> ans</p>
                        <?php endif; ?>
                        <?php if (isset($user['bio'])): ?>
                            <p class="user-bio"><?php echo htmlspecialchars($user['bio']); ?></p>
                        <?php endif; ?>
                        <div class="user-actions">
                            <a href="<?php echo $baseUrl; ?>/profile/view/<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-primary">Voir le profil</a>
                            <a href="<?php echo $baseUrl; ?>/message/new/<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-secondary">Envoyer un message</a>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="no-results">
            <p>Aucun résultat trouvé pour votre recherche.</p>
            <p>Essayez de modifier vos critères de recherche pour trouver plus de personnes.</p>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include '../layouts/footer.php';
?>