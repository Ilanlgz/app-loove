<?php $title = $title ?? "Manage User Reports"; ?>

<div class="admin-reports-container">
    <h2><?php echo htmlspecialchars($title); ?></h2>

    <?php if (empty($reports)): ?>
        <p>No reports found or all reports have been addressed.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reported At</th>
                    <th>Reporter</th>
                    <th>Reported User</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?php echo htmlspecialchars($report['id']); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($report['reported_at']))); ?></td>
                    <td><a href="<?php echo APP_URL; ?>/admin/user/view/<?php echo $report['reporter_user_id']; ?>"><?php echo htmlspecialchars($report['reporter_email'] ?? $report['reporter_user_id']); ?></a></td>
                    <td><a href="<?php echo APP_URL; ?>/admin/user/view/<?php echo $report['reported_user_id']; ?>"><?php echo htmlspecialchars($report['reported_user_email'] ?? $report['reported_user_id']); ?></a></td>
                    <td><?php echo htmlspecialchars($report['reason']); ?></td>
                    <td><span class="status-<?php echo htmlspecialchars(strtolower($report['status'])); ?>"><?php echo htmlspecialchars(ucfirst($report['status'])); ?></span></td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/admin/report/view/<?php echo $report['id']; ?>" class="btn btn-sm btn-secondary">View Details</a>
                        <?php if ($report['status'] === 'pending'): ?>
                        <a href="<?php echo APP_URL; ?>/admin/report/resolve/<?php echo $report['id']; ?>" class="btn btn-sm btn-success">Mark Resolved</a>
                        <a href="<?php echo APP_URL; ?>/admin/report/escalate/<?php echo $report['id']; ?>" class="btn btn-sm btn-warning">Escalate</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
