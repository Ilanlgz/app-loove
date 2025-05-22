<div class="search-page">
    <div class="search-header">
        <h1>Recherchez des profils</h1>
        <p class="search-subtitle">Trouvez des personnes qui partagent vos centres d'intérêt</p>
    </div>
    
    <div class="search-form-container">
        <form action="<?= $baseUrl ?>/search/results" method="get" class="search-form">
            <div class="form-section">
                <h3 class="section-title">Filtres principaux</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="age_min">Âge minimum</label>
                        <div class="select-wrapper">
                            <select id="age_min" name="age_min" class="form-control">
                                <?php for ($i = 18; $i <= 70; $i++): ?>
                                    <option value="<?= $i ?>" <?= (isset($preferences['age_min']) && $preferences['age_min'] == $i) ? 'selected' : '' ?>>
                                        <?= $i ?> ans
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="age_max">Âge maximum</label>
                        <div class="select-wrapper">
                            <select id="age_max" name="age_max" class="form-control">
                                <?php for ($i = 18; $i <= 99; $i++): ?>
                                    <option value="<?= $i ?>" <?= (isset($preferences['age_max']) && $preferences['age_max'] == $i) ? 'selected' : ($i == 50 ? 'selected' : '') ?>>
                                        <?= $i ?> ans
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Genre</label>
                        <div class="select-wrapper">
                            <select id="gender" name="gender" class="form-control">
                                <option value="all" <?= (isset($preferences['gender']) && $preferences['gender'] == 'all') ? 'selected' : '' ?>>Tous</option>
                                <option value="male" <?= (isset($preferences['gender']) && $preferences['gender'] == 'male') ? 'selected' : '' ?>>Homme</option>
                                <option value="female" <?= (isset($preferences['gender']) && $preferences['gender'] == 'female') ? 'selected' : '' ?>>Femme</option>
                                <option value="other" <?= (isset($preferences['gender']) && $preferences['gender'] == 'other') ? 'selected' : '' ?>>Autre</option>
                            </select>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="distance">Distance maximale</label>
                        <div class="select-wrapper">
                            <select id="distance" name="distance" class="form-control">
                                <option value="10" <?= (isset($preferences['distance']) && $preferences['distance'] == 10) ? 'selected' : '' ?>>10 km</option>
                                <option value="25" <?= (isset($preferences['distance']) && $preferences['distance'] == 25) ? 'selected' : '' ?>>25 km</option>
                                <option value="50" <?= (isset($preferences['distance']) && $preferences['distance'] == 50) ? 'selected' : 'selected' ?>>50 km</option>
                                <option value="100" <?= (isset($preferences['distance']) && $preferences['distance'] == 100) ? 'selected' : '' ?>>100 km</option>
                                <option value="0" <?= (isset($preferences['distance']) && $preferences['distance'] == 0) ? 'selected' : '' ?>>Sans limite</option>
                            </select>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="section-title">Centres d'intérêt</h3>
                
                <div class="form-group">
                    <label for="interests">Centres d'intérêt</label>
                    <div class="input-icon-wrapper">
                        <i class="fa fa-heart-o icon-left"></i>
                        <input type="text" id="interests" name="interests" class="form-control" 
                            value="<?= isset($preferences['interests']) ? htmlspecialchars($preferences['interests']) : '' ?>" 
                            placeholder="Sport, Musique, Cinéma, Voyage, etc.">
                    </div>
                    <small class="form-text">Séparez les intérêts par des virgules</small>
                </div>
                
                <div class="interests-suggestions">
                    <span class="interest-tag" data-value="Sport">Sport</span>
                    <span class="interest-tag" data-value="Musique">Musique</span>
                    <span class="interest-tag" data-value="Cinéma">Cinéma</span>
                    <span class="interest-tag" data-value="Voyages">Voyages</span>
                    <span class="interest-tag" data-value="Cuisine">Cuisine</span>
                    <span class="interest-tag" data-value="Art">Art</span>
                    <span class="interest-tag" data-value="Photographie">Photographie</span>
                    <span class="interest-tag" data-value="Lecture">Lecture</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Rechercher
                </button>
                <button type="reset" class="btn btn-outline">
                    <i class="fa fa-refresh"></i> Réinitialiser
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Styles pour la page de recherche */
    .search-page {
        max-width: 800px;
        margin: 30px auto;
        padding: 0 15px;
    }
    
    .search-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .search-header h1 {
        font-size: 2.2rem;
        margin-bottom: 10px;
        color: #333;
        background: linear-gradient(135deg, #ff4b7d 0%, #ff9271 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .search-subtitle {
        font-size: 1.1rem;
        color: #666;
        margin-bottom: 0;
    }
    
    .search-form-container {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    .search-form {
        padding: 30px;
    }
    
    .form-section {
        margin-bottom: 30px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 20px;
    }
    
    .form-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }
    
    .section-title {
        font-size: 1.2rem;
        margin-bottom: 20px;
        color: #333;
        position: relative;
        padding-left: 15px;
    }
    
    .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(to bottom, #ff4b7d, #ff9271);
        border-radius: 2px;
    }
    
    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        flex: 1;
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #444;
        font-size: 0.95rem;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        color: #333;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #ff4b7d;
        box-shadow: 0 0 0 3px rgba(255, 75, 125, 0.1);
        outline: none;
    }
    
    .select-wrapper {
        position: relative;
    }
    
    .select-wrapper i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        pointer-events: none;
    }
    
    .input-icon-wrapper {
        position: relative;
    }
    
    .icon-left {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }
    
    .input-icon-wrapper .form-control {
        padding-left: 40px;
    }
    
    .form-text {
        display: block;
        font-size: 0.85rem;
        color: #999;
        margin-top: 5px;
    }
    
    .interests-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    
    .interest-tag {
        padding: 6px 12px;
        background-color: #f8f9ff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        font-size: 0.9rem;
        color: #444;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .interest-tag:hover {
        background-color: rgba(255, 75, 125, 0.05);
        border-color: #ff4b7d;
        color: #ff4b7d;
    }
    
    .form-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
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
    
    .btn-outline {
        background-color: white;
        border: 1px solid #e2e8f0;
        color: #666;
    }
    
    .btn-outline:hover {
        background-color: #f8f9ff;
        border-color: #cbd5e0;
        color: #333;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des suggestions d'intérêts
    const interestInput = document.getElementById('interests');
    const interestTags = document.querySelectorAll('.interest-tag');
    
    interestTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            
            // Récupération de la valeur actuelle
            let currentInterests = interestInput.value.split(',')
                .map(interest => interest.trim())
                .filter(interest => interest.length > 0);
            
            // Vérifier si l'intérêt n'est pas déjà présent
            if (!currentInterests.includes(value)) {
                // Ajouter le nouvel intérêt
                currentInterests.push(value);
                
                // Mettre à jour le champ
                interestInput.value = currentInterests.join(', ');
            }
            
            // Focus sur le champ d'entrée
            interestInput.focus();
        });
    });
    
    // Animation du formulaire
    const formSections = document.querySelectorAll('.form-section');
    
    formSections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        section.style.transitionDelay = (index * 0.1) + 's';
        
        setTimeout(() => {
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, 100);
    });
});
</script>
