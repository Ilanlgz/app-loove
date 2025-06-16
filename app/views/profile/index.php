<?php include BASE_PATH . '/app/views/layout/header.php'; ?>
<?php include BASE_PATH . '/app/views/layout/navbar.php'; ?>

<style>
.profile-container {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
}

.profile-sidebar {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
}

.profile-avatar-large {
    width: 120px;
    height: 120px;
    background: #FF4458;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
    font-weight: bold;
    margin: 0 auto 1rem;
}

.profile-main {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-input, .form-textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-input:focus, .form-textarea:focus {
    outline: none;
    border-color: #FF4458;
    box-shadow: 0 0 0 3px rgba(255,68,88,0.1);
}

.btn-primary {
    background: #FF4458;
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
}
</style>

<div class="container">
    <h1 style='margin-bottom: 2rem; text-align: center;'>Mon profil</h1>
    
    <?php if($success): ?>
        <div class="alert alert-success">✓ <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-error">✗ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-avatar-large">
                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
            </div>
            <h2 style="text-align: center;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <p style="text-align: center; color: #64748b;"><?php echo htmlspecialchars($user['email']); ?></p>
            
            <div style="margin-top: 2rem;">
                <h3>Statistiques</h3>
                <div style="margin-bottom: 0.8rem;">Profil complété: 85%</div>
                <div style="margin-bottom: 0.8rem;">Membre depuis: <?php echo date('F Y', strtotime($user['created_at'])); ?></div>
                <div style="margin-bottom: 0.8rem;">Vues du profil: 127</div>
            </div>
        </div>

        <div class="profile-main">
            <form method="POST" action="/loove/public/profile/update">
                <div class="form-section">
                    <h3>Informations personnelles</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Âge</label>
                            <input type="number" name="age" class="form-input" value="<?php echo $user['age']; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <input type="text" name="location" class="form-input" value="<?php echo htmlspecialchars($user['location']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>À propos de moi</h3>
                    <div class="form-group">
                        <label class="form-label">Biographie</label>
                        <textarea name="bio" class="form-textarea" style="min-height: 120px;"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Centres d'intérêt</label>
                        <input type="text" name="interests" class="form-input" value="<?php echo htmlspecialchars($user['interests']); ?>">
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Sauvegarder les modifications
                </button>
            </form>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
