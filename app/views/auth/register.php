<?php include BASE_PATH . '/app/views/layout/header.php'; ?>

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #FF4458 0%, #FD5068 50%, #FF6B7D 100%);
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        background: #FFFFFF;
        border-radius: 24px;
        box-shadow: 0 32px 64px rgba(255, 68, 88, 0.2);
        max-width: 600px;
        margin: 0 auto;
        padding: 40px;
    }

    .form-title {
        color: #2c2c2c;
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 30px;
        text-align: center;
    }

    .alert-error {
        background: rgba(255, 59, 48, 0.1);
        color: #000000;
        border: 1px solid rgba(255, 59, 48, 0.2);
        font-weight: 600;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #2c2c2c;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .form-input {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #E8E8E8;
        border-radius: 12px;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s ease;
        background: #FFFFFF;
    }

    .form-input:focus {
        outline: none;
        border-color: #FF4458;
        box-shadow: 0 0 0 4px rgba(255, 68, 88, 0.1);
    }

    .btn-primary {
        width: 100%;
        padding: 18px 24px;
        background: linear-gradient(135deg, #FF4458, #FD5068);
        color: #FFFFFF;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 24px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
    }

    .login-link {
        text-align: center;
        color: #8E8E93;
    }

    .login-link a {
        color: #FF4458;
        text-decoration: none;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .form-row { grid-template-columns: 1fr; }
    }
</style>

<div class="container">
    <h2 class="form-title">Créer un compte</h2>
    
    <?php if($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?php echo $error; ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="/loove/public/auth/processRegister">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="first_name">Prénom *</label>
                <input type="text" id="first_name" name="first_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="last_name">Nom *</label>
                <input type="text" id="last_name" name="last_name" class="form-input" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email *</label>
            <input type="email" id="email" name="email" class="form-input" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="password">Mot de passe *</label>
                <input type="password" id="password" name="password" class="form-input" required minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirmer *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="date_of_birth">Date de naissance *</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="gender">Genre *</label>
                <select id="gender" name="gender" class="form-input" required>
                    <option value="">Sélectionner...</option>
                    <option value="male">Homme</option>
                    <option value="female">Femme</option>
                    <option value="other">Autre</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="location">Ville *</label>
            <input type="text" id="location" name="location" class="form-input" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="occupation">Profession</label>
                <input type="text" id="occupation" name="occupation" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label" for="height">Taille (cm)</label>
                <input type="number" id="height" name="height" class="form-input" min="140" max="220">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="interests">Centres d'intérêt</label>
            <input type="text" id="interests" name="interests" class="form-input">
        </div>

        <div class="form-group">
            <label class="form-label" for="bio">Présentation</label>
            <textarea id="bio" name="bio" class="form-input" style="min-height: 100px;"></textarea>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fas fa-heart"></i>
            Créer mon compte
        </button>
    </form>

    <div class="login-link">
        Déjà membre ? <a href="/loove/public/auth/login">Se connecter</a>
    </div>
</div>

<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
