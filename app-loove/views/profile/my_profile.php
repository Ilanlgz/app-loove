<div class="profile-container">
    <h1>Mon Profil</h1>
    
    <div class="profile-card">
        <div class="profile-header">
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="<?= $baseUrl ?>/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil">
            <?php else: ?>
                <img src="<?= $baseUrl ?>/assets/images/default-avatar.png" alt="Photo de profil par défaut">
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <h2><?= htmlspecialchars($user['name']) ?></h2>
            
            <?php if (!empty($user['birthdate'])): ?>
                <p class="profile-age">
                    <?= floor((time() - strtotime($user['birthdate'])) / 31536000) ?> ans
                </p>
            <?php endif; ?>
            
            <?php if (!empty($user['gender'])): ?>
                <p class="profile-gender">
                    <?= $user['gender'] === 'male' ? 'Homme' : ($user['gender'] === 'female' ? 'Femme' : 'Autre') ?>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($user['bio'])): ?>
                <div class="profile-bio">
                    <h3>À propos de moi</h3>
                    <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                </div>
            <?php endif; ?>
            
            <div class="profile-actions">
                <a href="<?= $baseUrl ?>/profile/edit" class="btn btn-primary">Modifier mon profil</a>
            </div>
        </div>
    </div>
</div>
