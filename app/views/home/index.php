<div class="hero-section">
    <h1><?php echo htmlspecialchars($title ?? 'Welcome to Loove!'); ?></h1>
    <p><?php echo htmlspecialchars($description ?? 'Find your perfect match today.'); ?></p>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="<?php echo APP_URL; ?>/register" class="btn btn-primary">Join Now</a>
        <a href="<?php echo APP_URL; ?>/login" class="btn btn-secondary">Login</a>
    <?php else: ?>
        <a href="<?php echo APP_URL; ?>/dashboard" class="btn btn-primary">Go to Dashboard</a>
    <?php endif; ?>
</div>

<section class="features">
    <h2>Why Choose Loove?</h2>
    <div class="feature-grid">
        <div class="feature-item">
            <img src="<?php echo APP_URL; ?>/img/icons/search-heart.svg" alt="Find Matches" class="feature-icon">
            <h3>Find Compatible Matches</h3>
            <p>Our advanced algorithm helps you find people who truly match your preferences.</p>
        </div>
        <div class="feature-item">
            <img src="<?php echo APP_URL; ?>/img/icons/chat-bubbles.svg" alt="Chat Securely" class="feature-icon">
            <h3>Chat Securely</h3>
            <p>Connect and chat in real-time with your matches in a safe environment.</p>
        </div>
        <div class="feature-item">
            <img src="<?php echo APP_URL; ?>/img/icons/geo-pin.svg" alt="Local Dating" class="feature-icon">
            <h3>Local Dating</h3>
            <p>Discover singles near you with our geolocation feature.</p>
        </div>
    </div>
</section>
