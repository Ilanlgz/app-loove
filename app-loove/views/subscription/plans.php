<?php
// This file displays subscription plans available to users.

require_once '../../config/config.php';
require_once '../../models/Subscription.php';

// Initialize the Subscription model
$subscriptionModel = new Subscription();

// Fetch available subscription plans
$plans = $subscriptionModel->getAllPlans();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/assets/css/main.css">
    <title>Plans d'abonnement</title>
</head>
<body>
    <header>
        <h1>Plans d'abonnement</h1>
    </header>
    
    <main>
        <section>
            <h2>Choisissez votre plan</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom du plan</th>
                        <th>Prix</th>
                        <th>Durée</th>
                        <th>Fonctionnalités</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($plan['name']); ?></td>
                            <td><?php echo htmlspecialchars($plan['price']); ?> €</td>
                            <td><?php echo htmlspecialchars($plan['duration']); ?> mois</td>
                            <td><?php echo htmlspecialchars($plan['features']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> App Loove. Tous droits réservés.</p>
    </footer>
</body>
</html>