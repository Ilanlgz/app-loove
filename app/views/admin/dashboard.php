<?php include BASE_PATH . '/app/views/layout/header.php'; ?>

<style>
/* ...existing code... (styles depuis admin/dashboard.php) */
</style>

<header class="admin-header">
    <div class="header-container">
        <div class="admin-logo">
            <i class="fas fa-shield-alt"></i>
            <h1>Admin Loove</h1>
        </div>
        
        <nav class="admin-nav">
            <a href="/loove/public/admin/dashboard" class="nav-link active">Dashboard</a>
            <a href="/loove/public/admin/users" class="nav-link">Utilisateurs</a>
            <a href="/loove/public/admin/reports" class="nav-link">Signalements</a>
            <a href="/loove/public/admin/transactions" class="nav-link">Transactions</a>
        </nav>
        
        <div class="admin-user">
            <span>Bonjour <?php echo htmlspecialchars($_SESSION["admin_name"]); ?></span>
            <a href="/loove/public/admin/logout" class="btn-logout">Déconnexion</a>
        </div>
    </div>
</header>

<main class="main-content">
    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_users'] ?? 0; ?></div>
            <div class="stat-label">Utilisateurs</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['pending_reports'] ?? 0; ?></div>
            <div class="stat-label">Signalements en attente</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['active_subscriptions'] ?? 0; ?></div>
            <div class="stat-label">Abonnements Premium</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_revenue'] ?? 0; ?> €</div>
            <div class="stat-label">Revenu total</div>
        </div>
    </div>
    
    <!-- ...existing code... (contenu du dashboard) -->
</main>

<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
