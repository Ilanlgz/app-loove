<header>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo APP_URL; ?>/dashboard" class="navbar-brand logo-font">Loove</a>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo APP_URL; ?>/dashboard">Dashboard</a></li>
                    <li><a href="<?php echo APP_URL; ?>/profile">Profile</a></li>
                    <li><a href="<?php echo APP_URL; ?>/search">Find Matches</a></li> <!-- Example link -->
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="<?php echo APP_URL; ?>/admin/dashboard">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo APP_URL; ?>/user/logout">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                <?php else: ?>
                    <!-- This case should ideally not be reached if pages are protected -->
                    <!-- and unauthenticated users are on the landing page -->
                    <li><a href="<?php echo APP_URL; ?>">Login/Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
<style>
    .logo-font { /* Ensure Pacifico is loaded if used here */
        font-family: 'Pacifico', cursive; 
    }
</style>
