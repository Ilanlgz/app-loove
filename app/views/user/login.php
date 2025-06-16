<?php $title = "Login"; ?>

<div class="form-container auth-form">
    <h2>Login to Your Account</h2>
    <?php if (isset($errors['login_err'])): ?>
        <p class="message error"><?php echo htmlspecialchars($errors['login_err']); ?></p>
    <?php endif; ?>

    <form action="<?php echo APP_URL; ?>/user/login" method="POST" id="loginForm">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            <?php if (isset($errors['email_err'])): ?>
                <span class="error-text"><?php echo htmlspecialchars($errors['email_err']); ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <?php if (isset($errors['password_err'])): ?>
                <span class="error-text"><?php echo htmlspecialchars($errors['password_err']); ?></span>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p>Don't have an account? <a href="<?php echo APP_URL; ?>/register">Register here</a>.</p>
</div>
<script src="<?php echo APP_URL; ?>/js/auth.js"></script>
