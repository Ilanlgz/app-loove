<?php
// users.php - Admin User Management View

// Include header
include_once '../layouts/header.php';

// Fetch users from the database (this should be done in the controller)
$users = []; // This should be populated with user data from the database

?>

<div class="container">
    <h1>Gestion des utilisateurs</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['status']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo htmlspecialchars($user['id']); ?>">Modifier</a>
                        <a href="delete.php?id=<?php echo htmlspecialchars($user['id']); ?>">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Include footer
include_once '../layouts/footer.php';
?>