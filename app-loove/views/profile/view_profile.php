<div class="profile-container">
    <div class="profile-header">
        <div class="profile-cover">
            <div class="cover-overlay"></div>
            <?php if (!empty($user['cover_photo'])): ?>
                <img src="<?= $baseUrl ?>/uploads/profiles/covers/<?= htmlspecialchars($user['cover_photo']) ?>" alt="Photo de couverture">
            <?php else: ?>
                <div class="default-cover-bg"></div>
            <?php endif; ?>
            
            <a href="<?= $baseUrl ?>/search" class="btn-back">
                <i class="fa fa-arrow-left"></i> Retour
            </a>
        </div>
        
        <div class="profile-main-info">
            <div class="profile-photo">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= $baseUrl ?>/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil">
                <?php else: ?>
                    <img src="<?= $baseUrl ?>/assets/images/default-avatar.png" alt="Photo de profil par défaut">
                <?php endif; ?>
            </div>
            
            <div class="profile-identity">
                <h1><?= htmlspecialchars($user['name']) ?></h1>
                
                <div class="profile-meta">
                    <?php if (isset($user['age'])): ?>
                        <span class="profile-age"><?= $user['age'] ?> ans</span>
                    <?php endif; ?>
                    
                    <?php if (!empty($user['location'])): ?>
                        <span class="profile-location">
                            <i class="fa fa-map-marker"></i> 
                            <?= htmlspecialchars($user['location']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($matchPercentage)): ?>
                    <div class="match-badge">
                        <i class="fa fa-heart"></i> <?= $matchPercentage ?>% compatibilité
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!$is_own_profile): ?>
                <div class="profile-actions">
                    <a href="<?= $baseUrl ?>/message/new/<?= $user['id'] ?>" class="btn btn-primary">
                        <i class="fa fa-comment"></i> Message
                    </a>
                    <button class="btn btn-like" id="btnLike" data-user-id="<?= $user['id'] ?>">
                        <i class="fa fa-heart<?= isset($hasLiked) && $hasLiked ? '' : '-o' ?>"></i>
                        <?= isset($hasLiked) && $hasLiked ? 'Aimé' : 'J\'aime' ?>
                    </button>
                </div>
            <?php else: ?>
                <div class="profile-actions">
                    <a href="<?= $baseUrl ?>/profile/edit" class="btn btn-outline">
                        <i class="fa fa-pencil"></i> Modifier profil
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="profile-tabs">
            <button class="tab-button active" data-tab="about">À propos</button>
            <button class="tab-button" data-tab="photos">Photos</button>
            <button class="tab-button" data-tab="interests">Intérêts</button>
        </div>
        
        <div class="tab-content active" id="about-tab">
            <div class="profile-card">
                <h3 class="card-title">À propos de moi</h3>
                <?php if (!empty($user['bio'])): ?>
                    <p class="bio-text"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                <?php else: ?>
                    <p class="empty-text">Aucune description disponible.</p>
                <?php endif; ?>
            </div>
            
            <div class="profile-card">
                <h3 class="card-title">Informations personnelles</h3>
                <ul class="info-list">
                    <?php if (!empty($user['gender'])): ?>
                        <li>
                            <span class="info-label">Genre:</span>
                            <span class="info-value">
                                <?= $user['gender'] === 'male' ? 'Homme' : ($user['gender'] === 'female' ? 'Femme' : 'Autre') ?>
                            </span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($user['looking_for'])): ?>
                        <li>
                            <span class="info-label">Je recherche:</span>
                            <span class="info-value"><?= htmlspecialchars($user['looking_for']) ?></span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($user['height'])): ?>
                        <li>
                            <span class="info-label">Taille:</span>
                            <span class="info-value"><?= htmlspecialchars($user['height']) ?> cm</span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($user['relationship_status'])): ?>
                        <li>
                            <span class="info-label">Statut:</span>
                            <span class="info-value"><?= htmlspecialchars($user['relationship_status']) ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="tab-content" id="photos-tab">
            <div class="profile-card">
                <h3 class="card-title">Photos</h3>
                <?php if (isset($photos) && count($photos) > 0): ?>
                    <div class="photos-grid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-item">
                                <img src="<?= $baseUrl ?>/uploads/profiles/photos/<?= htmlspecialchars($photo['filename']) ?>" 
                                     alt="Photo de <?= htmlspecialchars($user['name']) ?>"
                                     data-photo-id="<?= $photo['id'] ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-text">Aucune photo partagée.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="tab-content" id="interests-tab">
            <div class="profile-card">
                <h3 class="card-title">Centres d'intérêt</h3>
                <?php if (isset($interests) && count($interests) > 0): ?>
                    <div class="interests-tags">
                        <?php foreach ($interests as $interest): ?>
                            <span class="interest-tag"><?= htmlspecialchars($interest['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-text">Aucun centre d'intérêt renseigné.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modale photo -->
<div class="photo-modal" id="photoModal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <button class="close-modal">&times;</button>
        <img src="" alt="Photo agrandie" id="modalImage">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des onglets
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Désactiver tous les onglets
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Activer l'onglet cliqué
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Gestion du bouton J'aime
        const likeButton = document.getElementById('btnLike');
        if (likeButton) {
            likeButton.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const icon = this.querySelector('i');
                
                // Toggle de l'état
                if (icon.classList.contains('fa-heart-o')) {
                    icon.classList.remove('fa-heart-o');
                    icon.classList.add('fa-heart');
                    this.textContent = '';
                    this.appendChild(icon);
                    this.appendChild(document.createTextNode(' Aimé'));
                } else {
                    icon.classList.remove('fa-heart');
                    icon.classList.add('fa-heart-o');
                    this.textContent = '';
                    this.appendChild(icon);
                    this.appendChild(document.createTextNode(' J\'aime'));
                }
                
                // Envoyer la requête AJAX (à implémenter)
                // toggleLike(userId);
            });
        }
        
        // Affichage des photos en grand
        const photoItems = document.querySelectorAll('.photo-item img');
        const photoModal = document.getElementById('photoModal');
        const modalImage = document.getElementById('modalImage');
        const closeModal = document.querySelector('.close-modal');
        
        photoItems.forEach(photo => {
            photo.addEventListener('click', function() {
                modalImage.src = this.src;
                photoModal.style.display = 'flex';
            });
        });
        
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                photoModal.style.display = 'none';
            });
        }
        
        // Fermer la modale en cliquant à l'extérieur
        if (photoModal) {
            photoModal.addEventListener('click', function(e) {
                if (e.target === this || e.target.classList.contains('modal-overlay')) {
                    photoModal.style.display = 'none';
                }
            });
        }
    });
</script>

<style>
    /* Styles spécifiques pour la page de profil */
    .profile-container {
        max-width: 900px;
        margin: 30px auto;
        background-color: #fff;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .profile-header {
        position: relative;
    }
    
    .profile-cover {
        height: 250px;
        position: relative;
        overflow: hidden;
    }
    
    .profile-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .default-cover-bg {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
    }
    
    .cover-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.3) 100%);
    }
    
    .btn-back {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(255, 255, 255, 0.8);
        color: #333;
        padding: 8px 15px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        z-index: 1;
    }
    
    .btn-back:hover {
        background: white;
        transform: translateX(-3px);
    }
    
    .profile-main-info {
        padding: 0 30px 20px;
        position: relative;
        display: flex;
        align-items: flex-end;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .profile-photo {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 5px solid white;
        overflow: hidden;
        margin-top: -70px;
        position: relative;
        z-index: 2;
        background-color: #f0f0f0;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .profile-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .profile-identity {
        flex: 1;
        min-width: 200px;
    }
    
    .profile-identity h1 {
        margin: 0 0 5px;
        font-size: 1.8rem;
        color: #333;
    }
    
    .profile-meta {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .profile-age {
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        color: white;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 0.9rem;
    }
    
    .profile-location {
        color: #666;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .match-badge {
        background: rgba(255, 75, 125, 0.1);
        color: #ff4b7d;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .profile-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn {
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        border: none;
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
    
    .btn-like {
        background: white;
        color: #666;
        border: 1px solid #ddd;
    }
    
    .btn-like i.fa-heart {
        color: #ff4b7d;
    }
    
    .btn-like:hover {
        background: rgba(255, 75, 125, 0.05);
        color: #ff4b7d;
    }
    
    .btn-outline {
        background: white;
        border: 1px solid #ddd;
        color: #333;
    }
    
    .btn-outline:hover {
        background: #f8f8f8;
    }
    
    .profile-content {
        padding: 0 30px 30px;
    }
    
    .profile-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .tab-button {
        background: none;
        border: none;
        padding: 8px 16px;
        cursor: pointer;
        font-size: 1rem;
        color: #666;
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    
    .tab-button:hover {
        color: #ff4b7d;
        background: rgba(255, 75, 125, 0.05);
    }
    
    .tab-button.active {
        background: linear-gradient(135deg, rgba(255, 75, 125, 0.1) 0%, rgba(255, 146, 113, 0.1) 100%);
        color: #ff4b7d;
        font-weight: 600;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .profile-card {
        background: #f9f9f9;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .card-title {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.2rem;
        color: #333;
        position: relative;
        display: inline-block;
    }
    
    .card-title::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 30px;
        height: 3px;
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        border-radius: 3px;
    }
    
    .bio-text {
        line-height: 1.6;
        color: #444;
        margin: 0;
    }
    
    .empty-text {
        color: #888;
        font-style: italic;
    }
    
    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .info-list li {
        margin-bottom: 10px;
        display: flex;
    }
    
    .info-label {
        width: 120px;
        color: #888;
    }
    
    .info-value {
        flex: 1;
        color: #333;
    }
    
    .photos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }
    
    .photo-item {
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        aspect-ratio: 1 / 1;
    }
    
    .photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .photo-item:hover img {
        transform: scale(1.05);
    }
    
    .interests-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .interest-tag {
        background: white;
        border: 1px solid #ddd;
        border-radius: 20px;
        padding: 5px 15px;
        font-size: 0.9rem;
        color: #333;
        transition: all 0.3s ease;
    }
    
    .interest-tag:hover {
        background: rgba(255, 75, 125, 0.05);
        border-color: #ff4b7d;
        color: #ff4b7d;
    }
    
    /* Modal */
    .photo-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
    }
    
    .modal-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        z-index: 1001;
    }
    
    .close-modal {
        position: absolute;
        top: -40px;
        right: 0;
        background: none;
        border: none;
        color: white;
        font-size: 2rem;
        cursor: pointer;
    }
    
    #modalImage {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 8px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .profile-container {
            margin: 0;
            border-radius: 0;
        }
        
        .profile-cover {
            height: 180px;
        }
        
        .profile-main-info {
            padding: 0 20px 20px;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .profile-photo {
            margin-top: -50px;
            width: 100px;
            height: 100px;
        }
        
        .profile-meta {
            justify-content: center;
        }
        
        .profile-actions {
            width: 100%;
            justify-content: center;
        }
        
        .profile-content {
            padding: 0 20px 20px;
        }
        
        .profile-tabs {
            overflow-x: auto;
            padding-bottom: 15px;
        }
        
        .tab-button {
            white-space: nowrap;
        }
    }
</style>
