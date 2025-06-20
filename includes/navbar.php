<?php
// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="header">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <i class="fas fa-heart"></i> Loove
        </a>
        <nav class="nav-menu">
            <a href="discover.php" class="nav-link <?php echo ($current_page == 'discover.php') ? 'active' : ''; ?>">
                <i class="fas fa-search"></i> Découvrir
            </a>
            <a href="matches.php" class="nav-link <?php echo ($current_page == 'matches.php') ? 'active' : ''; ?>">
                <i class="fas fa-heart"></i> Matches
            </a>
            <a href="messages.php" class="nav-link <?php echo ($current_page == 'messages.php') ? 'active' : ''; ?>">
                <i class="fas fa-comment-dots"></i> Messages
            </a>
            <a href="profile.php" class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> Profil
            </a>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION["first_name"] ?? '', 0, 1)); ?>
                </div>
                <span>Bonjour <?php echo htmlspecialchars($_SESSION["first_name"] ?? ''); ?> !</span>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </nav>
    </div>
</header>
