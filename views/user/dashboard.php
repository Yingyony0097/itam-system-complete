<?php
/**
 * ລະບົບ ITAM - ແດຊບອດຜູ້ໃຊ້
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

requireAuth();

// ປ່ຽນເສັ້ນທາງຜູ້ດູແລໄປແດຊບອດຜູ້ດູແລ
if (isAdmin()) {
    redirect('/views/admin/dashboard.php');
}

$dashboard = new DashboardController();
$data = $dashboard->getUserDashboard($_SESSION['user_id']);

$pageTitle = 'My Dashboard';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';

function user_initials($name) {
    $name = trim((string)$name);
    if ($name === '') return 'U';
    $parts = preg_split('/\s+/', $name);
    $initials = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $initials .= strtoupper(substr($part, 0, 1));
        if (strlen($initials) >= 2) break;
    }
    return $initials ?: strtoupper(substr($name, 0, 1));
}
?>

<div class="main-content" style="padding: 0; background: transparent;">
    <header class="glass-card" style="margin: 16px; padding: 16px 24px; border-radius: 16px; position: relative; z-index: 1050; overflow: visible;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn-icon d-lg-none me-3" type="button" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                    <i class="bi bi-list" style="font-size: 20px;"></i>
                </button>
                <div>
                    <h1 class="m-0" style="font-size: 24px; font-weight: 700; color: var(--md-sys-color-on-surface);">
                        <?php echo e(tr('My Dashboard')); ?>
                    </h1>
                    <p class="m-0" style="font-size: 14px; color: var(--md-sys-color-on-surface-variant);">
                        <?php echo e(tr('Welcome back,')); ?> <?php echo e($_SESSION['user_name']); ?>
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);"><?php echo e(tr('Current Date')); ?></div>
                    <div class="fw-semibold" style="font-size: 14px; color: var(--md-sys-color-on-surface);"><?php echo date('F j, Y'); ?></div>
                </div>
                <?php echo userAvatar(); ?>
            </div>
        </div>
    </header>

    <main style="padding: 0 16px 16px 16px; min-height: calc(100vh - 120px);">
        <div class="fade-in">
            <!-- ບັດສະຖິຕິ -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card glass-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label m-0"><?php echo e(tr('My Assets')); ?></p>
                                <h2 class="stat-value m-0"><?php echo count($data['my_assets']); ?></h2>
                                <p class="m-0 mt-1" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                    <?php echo e(tr('Assets currently assigned to you')); ?>
                                </p>
                            </div>
                            <div class="stat-icon primary" style="margin-bottom: 0;">
                                <i class="bi bi-box-seam" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card glass-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label m-0"><?php echo e(tr('Activity Records')); ?></p>
                                <h2 class="stat-value m-0"><?php echo count($data['my_history']); ?></h2>
                                <p class="m-0 mt-1" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                    <?php echo e(tr('All Activities')); ?>
                                </p>
                            </div>
                            <div class="stat-icon success" style="margin-bottom: 0;">
                                <i class="bi bi-clock-history" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card glass-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label m-0"><?php echo e(tr('Latest Assignment')); ?></p>
                                <h2 class="stat-value m-0"><?php echo count($data['my_assets']) > 0 ? date('M j', strtotime($data['my_assets'][0]['assigned_date'])) : '-'; ?></h2>
                                <p class="m-0 mt-1" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                    <?php echo e(tr('Assigned Date')); ?>
                                </p>
                            </div>
                            <div class="stat-icon info" style="margin-bottom: 0;">
                                <i class="bi bi-calendar-check" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- ຊັບສິນຂອງຂ້ອຍ -->
                <div class="col-lg-8">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0" style="font-size: 18px; font-weight: 600; color: var(--md-sys-color-on-surface);">
                                <i class="bi bi-box-seam me-2"></i>
                                <?php echo e(tr('My Assigned Assets')); ?>
                            </h5>
                            <a href="/views/user/myassets.php" class="btn-m3-tonal btn-m3-tonal-primary px-3 py-1" style="font-size: 13px; border-radius: var(--md-sys-shape-corner-full);">
                                <?php echo e(tr('View All')); ?>
                            </a>
                        </div>

                        <?php if (empty($data['my_assets'])): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 48px; color: var(--md-sys-color-on-surface-variant);"></i>
                                <p class="mt-3" style="color: var(--md-sys-color-on-surface-variant);"><?php echo e(tr('No assets currently assigned to you')); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach (array_slice($data['my_assets'], 0, 3) as $asset): ?>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center p-3" style="background: var(--md-sys-color-surface-container-lowest); border-radius: var(--md-sys-shape-corner-medium); border: 1px solid var(--md-sys-color-outline-variant);">
                                            <div class="flex-shrink-0">
                                                <div class="stat-icon primary" style="width: 48px; height: 48px; font-size: 20px; margin-bottom: 0;">
                                                    <i class="bi bi-box-seam"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1" style="color: var(--md-sys-color-on-surface);"><?php echo e($asset['asset_name']); ?></h6>
                                                <small style="color: var(--md-sys-color-on-surface-variant);">
                                                    <?php echo e($asset['asset_code']); ?> &bull;
                                                    <?php echo e(tr($asset['category'])); ?> &bull;
                                                    <?php echo e(tr('Assigned')); ?>: <?php echo date('M j, Y', strtotime($asset['assigned_date'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge-custom badge-in-use"><?php echo e(tr('In Use')); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ກິດຈະກຳຫຼ້າສຸດ -->
                <div class="col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <h5 class="mb-4" style="font-size: 18px; font-weight: 600; color: var(--md-sys-color-on-surface);">
                            <i class="bi bi-activity me-2"></i>
                            <?php echo e(tr('My Activity')); ?>
                        </h5>
                        <?php if (empty($data['my_history'])): ?>
                            <p class="text-center py-4" style="color: var(--md-sys-color-on-surface-variant);"><?php echo e(tr('No activity records')); ?></p>
                        <?php else: ?>
                            <div class="activity-list">
                                <?php foreach (array_slice($data['my_history'], 0, 5) as $activity): ?>
                                    <?php
                                    $isCheckOut = ($activity['action_type'] ?? '') === 'Check Out';
                                    $actIconBg = $isCheckOut ? '#FFDDB3' : '#C4EED0';
                                    $actIconColor = $isCheckOut ? '#7D5700' : '#386A20';
                                    $statusCls = $isCheckOut ? 'status-checkout' : 'status-checkin';
                                    ?>
                                    <div class="activity-item <?php echo $statusCls; ?>">
                                        <div class="activity-icon" style="background: <?php echo $actIconBg; ?>; color: <?php echo $actIconColor; ?>;">
                                            <i class="bi bi-arrow-<?php echo $isCheckOut ? 'right' : 'left'; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="fw-semibold" style="color: var(--md-sys-color-on-surface);"><?php echo e($activity['asset_name']); ?></div>
                                            <div class="small" style="color: var(--md-sys-color-on-surface-variant);">
                                                <?php echo e(tr($activity['action_type'])); ?> &bull;
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
    </main>

    <footer class="glass-card text-center" style="margin: 0 16px 16px 16px; padding: 16px; border-radius: 12px;">
        <p class="m-0" style="font-size: 14px; color: var(--md-sys-color-on-surface-variant);">
            &copy; 2026 ລະບົບ ITAM - ບໍລິສັດ P-line | ວຽງຈັນ, ລາວ
        </p>
    </footer>
</div>

<style>
.glass-card:hover { transform: none; }
</style>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
