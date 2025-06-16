<?php include BASE_PATH . '/app/views/layout/header.php'; ?>

<style>
.admin-login-container {
    max-width: 400px;
    margin: 100px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.admin-logo {
    text-align: center;
    margin-bottom: 20px;
}

.admin-logo i {
    font-size: 50px;
    color: #007bff;
}

.admin-title {
    text-align: center;
    font-size: 24px;
    margin-bottom: 20px;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.alert-error i {
    margin-right: 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

.btn-admin-login {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
}

.btn-admin-login i {
    margin-right: 5px;
}

.back-link {
    text-align: center;
    margin-top: 10px;
}

.back-link a {
    color: #007bff;
    text-decoration: none;
}

.back-link a i {
    margin-right: 5px;
}
</style>

<div class="admin-login-container">
    <div class="admin-logo">
        <i class="fas fa-shield-alt"></i>
    </div>
    
    <h2 class="admin-title">Panel Administrateur</h2>
    
    <?php if($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/loove/public/admin/processLogin">
        <div class="form-group">
            <label class="form-label" for="email">Email administrateur</label>
            <input type="email" id="email" name="email" class="form-input" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Mot de passe</label>
            <input type="password" id="password" name="password" class="form-input" required>
        </div>

        <button type="submit" class="btn-admin-login">
            <i class="fas fa-sign-in-alt"></i>
            Se connecter
        </button>
    </form>

    <div class="back-link">
        <a href="/loove/public">
            <i class="fas fa-arrow-left"></i> Retour au site
        </a>
    </div>
</div>

<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>