<?php include BASE_PATH . '/app/views/layout/header.php'; ?>

<style>
.admin-header {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    color: white;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.users-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}
</style>

<header class="admin-header">
    <div class="header-container">
        <div class="admin-logo">
            <i class="fas fa-shield-alt"></i>
            <h1>Admin Loove</h1>
        </div>
        
        <nav class="admin-nav">
            <a href="/loove/public/admin/dashboard" class="nav-link">Dashboard</a>
            <a href="/loove/public/admin/users" class="nav-link active">Utilisateurs</a>
            <a href="/loove/public/admin/reports" class="nav-link">Signalements</a>
            <a href="/loove/public/admin/transactions" class="nav-link">Transactions</a>
        </nav>
        
        <div class="admin-user">
            <span>Bonjour Admin</span>
            <a href="/loove/public/admin/logout" class="btn-logout">Déconnexion</a>
        </div>
    </div>
</header>

<main class="main-content">
    <h1 class="page-title">Gestion des utilisateurs</h1>
    
    <div class="users-table">
        <table>
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Premium</th>
                    <th>Inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                        <small><?php echo $user['age']; ?> ans</small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="status-active">Actif</span>
                            </td>
                            <td>
                                <?php if ($user['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php else: ?>
                                    <span>Gratuit</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button class="btn-action">Actions</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
