<?php
// reports.php

// Include necessary files
require_once '../../config/config.php';
require_once '../../models/Report.php';

// Initialize Report model
$reportModel = new Report();

// Fetch reports from the database
$reports = $reportModel->getAllReports();

// Include header
include '../layouts/header.php';
?>

<div class="container">
    <h1>User Reports</h1>
    
    <?php if (count($reports) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Reported By</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['id']); ?></td>
                        <td><?php echo htmlspecialchars($report['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($report['reported_by']); ?></td>
                        <td><?php echo htmlspecialchars($report['reason']); ?></td>
                        <td><?php echo htmlspecialchars($report['created_at']); ?></td>
                        <td>
                            <a href="resolve.php?id=<?php echo htmlspecialchars($report['id']); ?>">Resolve</a>
                            <a href="delete.php?id=<?php echo htmlspecialchars($report['id']); ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No reports found.</p>
    <?php endif; ?>
</div>

<?php
// Include footer
include '../layouts/footer.php';
?>