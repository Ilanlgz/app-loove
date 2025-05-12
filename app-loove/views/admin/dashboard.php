<?php
// admin/dashboard.php

// Include necessary files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../controllers/AdminController.php';

// Initialize the AdminController
$adminController = new AdminController();

// Fetch user statistics and reports
$userStats = $adminController->getUserStatistics();
$reports = $adminController->getUserReports();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <title>Tableau de bord Administrateur</title>
</head>
<body>
    <?php include '../layouts/header.php'; ?>

    <div class="container">
        <h1>Tableau de bord Administrateur</h1>

        <section class="stats">
            <h2>Statistiques des utilisateurs</h2>
            <ul>
                <li>Nombre total d'utilisateurs : <?php echo $userStats['total_users']; ?></li>
                <li>Utilisateurs actifs : <?php echo $userStats['active_users']; ?></li>
                <li>Utilisateurs premium : <?php echo $userStats['premium_users']; ?></li>
            </ul>
        </section>

        <section class="reports">
            <h2>Rapports d'utilisateurs</h2>
            <?php if (count($reports) > 0): ?>
                <ul>
                    <?php foreach ($reports as $report): ?>
                        <li>
                            <strong><?php echo $report['user_name']; ?></strong> - <?php echo $report['reason']; ?>
                            <a href="view_report.php?id=<?php echo $report['id']; ?>">Voir le rapport</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucun rapport à afficher.</p>
            <?php endif; ?>
        </section>
    </div>

    <?php include '../layouts/footer.php'; ?>
    <script src="/assets/js/admin/dashboard.js"></script>
</body>
</html>