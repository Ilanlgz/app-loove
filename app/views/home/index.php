<!-- Section Stories -->
<div class="stories-section">
    <h2 class="section-title">Stories</h2>
    <div class="stories-container">
        <?php foreach ($stories as $story): ?>
            <div class="story" data-story-id="<?= $story['id'] ?>">
                <div class="story-avatar">
                    <?php if (!empty($story['profile_picture'])): ?>
                        <img src="<?= $baseUrl ?>/assets/images/demo/<?= htmlspecialchars($story['profile_picture']) ?>" alt="Photo de profil">
                    <?php else: ?>
                        <img src="<?= $baseUrl ?>/assets/images/default-avatar.png" alt="Photo de profil par défaut">
                    <?php endif; ?>
                </div>
                <span class="story-name"><?= htmlspecialchars($story['name']) ?></span>
            </div>
        <?php endforeach; ?>
        
        <?php if ($isLoggedIn): ?>
        <div class="story add-story">
            <div class="story-avatar add-icon">
                <i class="fa fa-plus"></i>
            </div>
            <span class="story-name">Ajouter</span>
        </div>
        <?php endif; ?>
    </div>
</div>