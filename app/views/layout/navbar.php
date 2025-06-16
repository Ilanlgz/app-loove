<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <h1><i class="fas fa-heart"></i> Loove</h1>
        </div>
        <div class="nav-menu">
            <a href="/loove/public/dashboard" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
            <a href="/loove/public/discover" class="nav-link">
                <i class="fas fa-users"></i> Découvrir
            </a>
            <a href="/loove/public/messages" class="nav-link">
                <i class="fas fa-comments"></i> Messages
            </a>
            <a href="/loove/public/profile" class="nav-link">
                <i class="fas fa-user"></i> Profil
            </a>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION["first_name"], 0, 1)); ?>
                </div>
                <span>Bienvenue <?php echo htmlspecialchars($_SESSION["first_name"]); ?> !</span>
                <a href="/loove/public/auth/logout" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</nav>
