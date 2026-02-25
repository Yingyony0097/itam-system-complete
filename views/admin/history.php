<?php
/**
 * ITAM System - Check In/Out History
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/CheckLog.php';

requireAdmin();

$checkLogModel = new CheckLog();
$logs = $checkLogModel->getAllWithDetails();

$pageTitle = 'History';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<div class="main-content">
    <header class="top-header">
        <button class="btn btn-icon d-md-none" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <?php echo userAvatar(); ?>
        </div>
    </header>

    <div class="fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Check-In/Out History</h1>
                <p class="text-muted mb-0">Complete audit trail of asset movements</p>
            </div>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Print Report
            </button>
        </div>

        <div class="glass-card">
            <div class="table-glass" style="border-radius: 0; border: none;">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Asset</th>
                            <th>Action</th>
                            <th>User</th>
                            <th>Performed By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No history records found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo date('M j, Y', strtotime($log['action_date'])); ?></div>
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($log['action_date'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($log['asset_name']); ?></div>
                                        <small class="text-muted"><?php echo e($log['asset_code']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge-custom badge-<?php echo $log['action_type'] === 'Check Out' ? 'warning' : 'available'; ?>">
                                            <?php echo e($log['action_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo e($log['user_name']); ?></td>
                                    <td><?php echo e($log['performed_by_name']); ?></td>
                                    <td><small class="text-muted"><?php echo e($log['notes'] ?? '-'); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
