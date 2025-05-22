<div class="search-container">
    <div class="search-header">
        <h2>Résultats de recherche</h2>
        
        <?php if (isset($searchCriteria)): ?>
        <div class="filter-summary">
            <?php if (isset($searchCriteria['ageMin']) && isset($searchCriteria['ageMax'])): ?>
                <div class="filter-tag">
                    <i class="fa fa-birthday-cake"></i>
                    <?= htmlspecialchars($searchCriteria['ageMin']) ?> - <?= htmlspecialchars($searchCriteria['ageMax']) ?> ans
                </div>
            <?php endif; ?>
            
            <?php if (isset($searchCriteria['gender'])): ?>
                <div class="filter-tag">
                    <i class="fa fa-user"></i>
                    <?= htmlspecialchars($searchCriteria['gender'] === 'male' ? 'Homme' : ($searchCriteria['gender'] === 'female' ? 'Femme' : ($searchCriteria['gender'] === 'other' ? 'Autre' : 'Tous'))) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($searchCriteria['distance']) && $searchCriteria['distance'] > 0): ?>
                <div class="filter-tag">
                    <i class="fa fa-map-marker"></i>
                    <?= htmlspecialchars($searchCriteria['distance']) ?> km
                </div>
            <?php endif; ?>
            
            <a href="<?= $baseUrl ?>/search" class="btn-link">
                <i class="fa fa-sliders"></i> Modifier les filtres
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isset($searchResults) && is_array($searchResults) && count($searchResults) > 0): ?>
        <div class="search-results-grid">
            <?php foreach ($searchResults as $user): ?>
                <div class="profile-card">
                    <div class="profile-card-cover">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?= $baseUrl ?>/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil">
                        <?php else: ?>
                            <img src="<?= $baseUrl ?>/assets/images/default-cover.jpg" alt="Couverture par défaut">
                        <?php endif; ?>
                        
                        <?php if (isset($user['match_percentage'])): ?>
                            <div class="match-percentage">
                                <i class="fa fa-heart"></i> <?= htmlspecialchars($user['match_percentage']) ?>% compatibilité
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-card-avatar">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?= $baseUrl ?>/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil">
                        <?php else: ?>
                            <img src="<?= $baseUrl ?>/assets/images/default-avatar.png" alt="Avatar par défaut">
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-card-info">
                        <h3 class="profile-card-name">
                            <?= htmlspecialchars($user['name']) ?>
                            <?php if (isset($user['age'])): ?>
                                <span class="profile-card-age"><?= htmlspecialchars($user['age']) ?></span>
                            <?php endif; ?>
                        </h3>
                        
                        <?php if (isset($user['location'])): ?>
                            <div class="profile-card-location">
                                <i class="fa fa-map-marker"></i> <?= htmlspecialchars($user['location']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($user['bio'])): ?>
                            <p class="profile-card-bio"><?= htmlspecialchars($user['bio']) ?></p>
                        <?php endif; ?>
                        
                        <div class="profile-card-actions">
                            <a href="<?= $baseUrl ?>/profile/view/<?= htmlspecialchars($user['id']) ?>" class="btn-profile">
                                <i class="fa fa-user"></i> Voir profil
                            </a>
                            <a href="<?= $baseUrl ?>/message/new/<?= htmlspecialchars($user['id']) ?>" class="btn-message">
                                <i class="fa fa-comment"></i> Message
                            </a>
                            <button class="btn-like" data-id="<?= htmlspecialchars($user['id']) ?>">
                                <i class="fa fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <img src="<?= $baseUrl ?>/assets/images/no-results.svg" alt="Aucun résultat" class="no-results-image">
            <h3>Aucun profil trouvé</h3>
            <p>Nous n'avons pas trouvé de profils correspondant à vos critères de recherche.</p>
            <p>Essayez d'élargir vos critères pour découvrir plus de personnes.</p>
            <a href="<?= $baseUrl ?>/search" class="btn btn-primary">Modifier la recherche</a>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Styles pour la page de recherche */
    .search-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 15px;
    }
    
    .search-header {
        margin-bottom: 25px;
    }
    
    .search-header h2 {
        font-size: 1.8rem;
        margin-bottom: 15px;
        color: #333;
        position: relative;
        display: inline-block;
    }
    
    .search-header h2::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        border-radius: 3px;
    }
    
    .filter-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        background-color: #f8f9ff;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
    }
    
    .filter-tag {
        background-color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        color: #333;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    
    .filter-tag i {
        color: #ff4b7d;
    }
    
    .btn-link {
        margin-left: auto;
        color: #ff4b7d;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    
    .btn-link:hover {
        text-decoration: underline;
    }
    
    /* Grid pour les résultats */
    .search-results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        margin-top: 25px;
    }
    
    /* Cartes de profil */
    .profile-card {
        background-color: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .profile-card-cover {
        height: 120px;
        position: relative;
        overflow: hidden;
    }
    
    .profile-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .match-percentage {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 75, 125, 0.9);
        color: white;
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 0.8rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .profile-card-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
        position: absolute;
        top: 80px;
        left: 20px;
        border: 4px solid white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .profile-card-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .profile-card-info {
        padding: 50px 20px 20px;
    }
    
    .profile-card-name {
        font-size: 1.2rem;
        margin-bottom: 5px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .profile-card-age {
        font-size: 0.9rem;
        color: #666;
        font-weight: normal;
    }
    
    .profile-card-location {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .profile-card-bio {
        color: #444;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 20px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .profile-card-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-profile, .btn-message {
        flex: 1;
        padding: 8px 0;
        text-align: center;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    
    .btn-profile {
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        color: white;
    }
    
    .btn-message {
        border: 1px solid #ff4b7d;
        color: #ff4b7d;
        background-color: white;
    }
    
    .btn-profile:hover {
        box-shadow: 0 4px 10px rgba(255, 75, 125, 0.3);
    }
    
    .btn-message:hover {
        background-color: rgba(255, 75, 125, 0.05);
    }
    
    .btn-like {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        background-color: white;
        color: #aaa;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-like:hover, .btn-like.active {
        background-color: #ff4b7d;
        color: white;
        border-color: #ff4b7d;
        box-shadow: 0 4px 10px rgba(255, 75, 125, 0.3);
    }
    
    /* Pas de résultats */
    .no-results {
        text-align: center;
        padding: 60px 20px;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .no-results-image {
        width: 150px;
        margin-bottom: 20px;
        opacity: 0.7;
    }
    
    .no-results h3 {
        font-size: 1.5rem;
        margin-bottom: 15px;
        color: #333;
    }
    
    .no-results p {
        color: #666;
        max-width: 500px;
        margin: 0 auto 10px;
    }
    
    .no-results .btn {
        margin-top: 20px;
        display: inline-block;
        padding: 10px 25px;
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        color: white;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .no-results .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 75, 125, 0.3);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .search-results-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
        
        .filter-summary {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .btn-link {
            margin-left: 0;
            margin-top: 10px;
        }
    }
    
    @media (max-width: 480px) {
        .search-results-grid {
            grid-template-columns: 1fr;
        }
        
        .profile-card-actions {
            flex-wrap: wrap;
        }
        
        .btn-profile, .btn-message {
            width: calc(50% - 5px);
        }
        
        .btn-like {
            margin-top: 10px;
            width: 100%;
            border-radius: 6px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des boutons "like"
        const likeButtons = document.querySelectorAll('.btn-like');
        
        likeButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.classList.toggle('active');
                const userId = this.getAttribute('data-id');
                
                // Ici, vous pouvez ajouter une requête AJAX pour enregistrer le "like"
                console.log('Like toggle for user ID:', userId);
            });
        });
        
        // Animation d'apparition des cartes
        const profileCards = document.querySelectorAll('.profile-card');
        
        profileCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.transitionDelay = (index * 0.05) + 's';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    });
</script>