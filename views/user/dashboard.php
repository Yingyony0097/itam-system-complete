<?php
/**
 * ITAM System - User Dashboard
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

requireAuth();

// Redirect admin to admin dashboard
if (isAdmin()) {
    redirect('/views/admin/dashboard.php');
}

$dashboard = new DashboardController();
$data = $dashboard->getUserDashboard($_SESSION['user_id']);

$pageTitle = 'My Dashboard';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<div class="main-content">
    <header class="top-header">
        <button class="btn btn-icon d-md-none" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
        </div>
    </header>

    <div class="fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">My Dashboard</h1>
                <p class="text-muted mb-0">Welcome back, <?php echo e($_SESSION['user_name']); ?>!</p>
            </div>
            <div class="text-end">
                <div class="text-muted" style="font-size: 14px;">Current Date</div>
                <div class="fw-semibold"><?php echo date('F j, Y'); ?></div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-value"><?php echo count($data['my_assets']); ?></div>
                    <div class="stat-label">My Assets</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-value"><?php echo count($data['my_history']); ?></div>
                    <div class="stat-label">Activity Records</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo count($data['my_assets']) > 0 ? date('M j', strtotime($data['my_assets'][0]['assigned_date'])) : '-'; ?></div>
                    <div class="stat-label">Latest Assignment</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- My Assets -->
            <div class="col-lg-8">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">My Assigned Assets</h5>
                        <a href="/views/user/myassets.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>

                    <?php if (empty($data['my_assets'])): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-3">No assets currently assigned to you</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach (array_slice($data['my_assets'], 0, 3) as $asset): ?>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="flex-shrink-0">
                                            <div class="stat-icon primary" style="width: 48px; height: 48px; font-size: 20px;">
                                                <i class="bi bi-box-seam"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1"><?php echo e($asset['asset_name']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo e($asset['asset_code']); ?> • 
                                                <?php echo e($asset['category']); ?> • 
                                                Assigned: <?php echo date('M j, Y', strtotime($asset['assigned_date'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge-custom badge-in-use">In Use</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-4">
                <div class="glass-card p-4 h-100">
                    <h5 class="mb-4">My Activity</h5>
                    <?php if (empty($data['my_history'])): ?>
                        <p class="text-muted text-center py-4">No activity records</p>
                    <?php else: ?>
                        <div class="activity-list">
                            <?php foreach (array_slice($data['my_history'], 0, 5) as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $activity['action_type'] === 'Check Out' ? 'bg-warning' : 'bg-success'; ?>" style="color: white;">
                                        <i class="bi bi-arrow-<?php echo $activity['action_type'] === 'Check Out' ? 'right' : 'left'; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="fw-semibold"><?php echo e($activity['asset_name']); ?></div>
                                        <div class="text-muted small">
                                            <?php echo e($activity['action_type']); ?> • 
                                            <?php echo date('M j, Y', strtotime($activity['action_date'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
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
