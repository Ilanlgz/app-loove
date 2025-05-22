<div class="profile-container">
    <h1>Modifier mon profil</h1>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            Votre profil a été mis à jour avec succès.
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?= $baseUrl ?>/profile/edit" method="post" enctype="multipart/form-data" class="profile-form">
        <div class="form-group">
            <label for="name">Nom complet</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="gender">Genre</label>
            <select id="gender" name="gender" class="form-control">
                <option value="">-- Sélectionner --</option>
                <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Homme</option>
                <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Femme</option>
                <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Autre</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="birthdate">Date de naissance</label>
            <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= htmlspecialchars($user['birthdate'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="bio">À propos de moi</label>
            <textarea id="bio" name="bio" class="form-control" rows="5"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="profile_picture">Photo de profil</label>
            <?php if (!empty($user['profile_picture'])): ?>
                <div class="current-picture">
                    <img src="<?= $baseUrl ?>/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo de profil actuelle" class="preview-img">
                    <p>Photo actuelle</p>
                </div>
            <?php endif; ?>
            <input type="file" id="profile_picture" name="profile_picture" class="form-control-file" accept="image/*">
            <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, GIF. Taille max: 5MB.</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="<?= $baseUrl ?>/profile" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
    // Aperçu de l'image avant upload
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('profile_picture');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let previewImg = document.querySelector('.preview-img');
                        if (!previewImg) {
                            previewImg = document.createElement('img');
                            previewImg.className = 'preview-img';
                            const currentPicture = document.createElement('div');
                            currentPicture.className = 'current-picture';
                            currentPicture.appendChild(previewImg);
                            currentPicture.appendChild(document.createElement('p')).textContent = 'Nouvelle photo';
                            
                            const container = fileInput.parentNode;
                            container.insertBefore(currentPicture, fileInput);
                        }
                        previewImg.src = e.target.result;
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
</script>
