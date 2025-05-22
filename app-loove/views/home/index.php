<div class="home-container">
    <?php if (!$isLoggedIn): ?>
    <div class="welcome-banner">
        <div class="welcome-content">
            <h1>Bienvenue sur <span class="brand-highlight">Loove</span></h1>
            <p class="tagline">Faites de belles rencontres et créez des connexions authentiques</p>
            <div class="banner-buttons">
                <a href="<?= $baseUrl ?>/login" class="btn btn-primary">Connexion</a>
                <a href="<?= $baseUrl ?>/register" class="btn btn-outline">Inscription</a>
            </div>
        </div>
        <div class="welcome-image">
            <img src="<?= $baseUrl ?>/assets/images/couple-illustration.svg" alt="Rencontres" class="couple-img">
        </div>
    </div>
    <?php endif; ?>
    
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
    
    <!-- Section des publications récentes -->
    <div class="posts-section">
        <div class="section-header">
            <h2 class="section-title">Publications récentes</h2>
            <div class="section-tabs">
                <button class="tab-btn active" data-filter="all">Tous</button>
                <button class="tab-btn" data-filter="popular">Populaires</button>
                <button class="tab-btn" data-filter="recent">Récents</button>
            </div>
        </div>
        
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-author">
                            <?php if (!empty($post['profile_picture'])): ?>
                                <img src="<?= $baseUrl ?>/assets/images/demo/<?= htmlspecialchars($post['profile_picture']) ?>" alt="Photo de profil">
                            <?php else: ?>
                                <img src="<?= $baseUrl ?>/assets/images/default-avatar.png" alt="Photo de profil par défaut">
                            <?php endif; ?>
                            <div>
                                <h3 class="author-name"><?= htmlspecialchars($post['name']) ?></h3>
                                <span class="post-time"><?= date('j M Y', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>
                        <button class="btn-options">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>
                    </div>
                    
                    <div class="post-image">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= $baseUrl ?>/assets/images/demo/<?= htmlspecialchars($post['image']) ?>" alt="Publication">
                        <?php else: ?>
                            <img src="<?= $baseUrl ?>/assets/images/default-post.jpg" alt="Publication par défaut">
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-footer">
                        <div class="post-actions">
                            <button class="btn-action btn-like" data-post-id="<?= $post['id'] ?>">
                                <i class="fa fa-heart-o"></i>
                                <span class="like-count"><?= $post['likes_count'] ?></span>
                            </button>
                            <button class="btn-action btn-comment" data-post-id="<?= $post['id'] ?>">
                                <i class="fa fa-comment-o"></i>
                                <span class="comment-count"><?= $post['comments_count'] ?></span>
                            </button>
                            <button class="btn-action btn-share" data-post-id="<?= $post['id'] ?>">
                                <i class="fa fa-share"></i>
                            </button>
                        </div>
                        
                        <?php if (!empty($post['caption'])): ?>
                            <p class="post-caption">
                                <span class="caption-author"><?= htmlspecialchars($post['name']) ?>:</span>
                                <?= htmlspecialchars($post['caption']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($post['comments_count'] > 0): ?>
                            <a href="#" class="view-comments">Voir les <?= $post['comments_count'] ?> commentaires</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Section Profils Populaires -->
    <div class="popular-profiles-section">
        <h2 class="section-title">Profils populaires</h2>
        <div class="profiles-grid">
            <?php foreach ($popularUsers as $user): ?>
                <div class="profile-card">
                    <div class="profile-header">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?= $baseUrl ?>/assets/images/demo/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil">
                        <?php else: ?>
                            <img src="<?= $baseUrl ?>/assets/images/default-cover.jpg" alt="Photo de profil par défaut">
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h3 class="profile-name">
                            <?= htmlspecialchars($user['name']) ?>
                            <?php if (isset($user['age'])): ?>
                                <span class="profile-age"><?= $user['age'] ?></span>
                            <?php endif; ?>
                        </h3>
                        <?php if (!empty($user['bio'])): ?>
                            <p class="profile-bio"><?= htmlspecialchars($user['bio']) ?></p>
                        <?php endif; ?>
                        <div class="profile-actions">
                            <a href="<?= $baseUrl ?>/profile/view/<?= $user['id'] ?>" class="btn-profile">Voir profil</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="see-more">
            <a href="<?= $baseUrl ?>/search" class="btn btn-outline-primary">Voir plus de profils</a>
        </div>
    </div>
</div>

<!-- Modal pour afficher les stories -->
<div class="story-modal" id="storyModal">
    <div class="story-modal-content">
        <div class="story-header">
            <div class="story-user-info">
                <img src="" alt="Photo de profil" class="story-user-avatar">
                <div class="story-user-details">
                    <h3 class="story-user-name"></h3>
                    <span class="story-timestamp"></span>
                </div>
            </div>
            <button class="close-story">&times;</button>
        </div>
        <div class="story-progress">
            <div class="progress-bar"></div>
        </div>
        <div class="story-image-container">
            <img src="" alt="Story" class="story-image">
        </div>
    </div>
</div>
