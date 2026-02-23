<?php
/**
 * ITAM System - Admin Dashboard
 *
 * This page is styled to match `Dashboard.html` provided by the user.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

requireAdmin();

$dashboard = new DashboardController();
$data = $dashboard->getAdminDashboard();

function itam_initials($name) {
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

function itam_time_ago($datetime) {
    $ts = strtotime((string)$datetime);
    if (!$ts) return '';

    $diff = time() - $ts;
    if ($diff < 0) $diff = 0;

    if ($diff < 60) return 'just now';

    $minutes = (int)floor($diff / 60);
    if ($minutes < 60) return $minutes . ($minutes === 1 ? ' minute ago' : ' minutes ago');

    $hours = (int)floor($minutes / 60);
    if ($hours < 24) return $hours . ($hours === 1 ? ' hour ago' : ' hours ago');

    $days = (int)floor($hours / 24);
    return $days . ($days === 1 ? ' day ago' : ' days ago');
}

$totalAssets = (int)($data['stats']['total'] ?? 0);
$availableAssets = (int)($data['stats']['available'] ?? 0);
$inUseAssets = (int)($data['stats']['in_use'] ?? 0);
$totalValue = (float)($data['stats']['total_value'] ?? 0);

$safeTotal = max(1, $totalAssets);
$availablePct = (int)round(($availableAssets / $safeTotal) * 100);
$inUsePct = (int)round(($inUseAssets / $safeTotal) * 100);

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<div class="main-content" style="padding: 0; background: transparent;">
    <header class="glass-card dashboard-header" style="margin: 16px; padding: 16px 24px; border-radius: 16px; position: relative; z-index: 1050; overflow: visible;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="header-left d-flex align-items-center">
                <button class="btn-icon d-lg-none me-3" type="button" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                    <i class="bi bi-list" style="font-size: 20px;"></i>
                </button>

                <div>
                    <h1 class="m-0" style="font-size: 24px; font-weight: 700; color: var(--gray-800);">
                        Admin Dashboard
                    </h1>
                    <p class="m-0" style="font-size: 14px; color: var(--gray-600);">
                        P-line Company - Vientiane, Laos
                    </p>
                </div>
            </div>

            <div class="header-right d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="d-flex align-items-center gap-3 border-0 bg-transparent" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <div class="user-avatar" style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px;">
                            <?php echo e(itam_initials($_SESSION['user_name'] ?? '')); ?>
                        </div>

                        <div class="d-none d-md-block text-start">
                            <p class="m-0" style="font-size: 14px; font-weight: 600; color: var(--gray-800);">
                                <?php echo e($_SESSION['user_name'] ?? ''); ?>
                            </p>
                            <p class="m-0" style="font-size: 12px; color: var(--gray-600);">
                                <?php echo e($_SESSION['user_role'] ?? ''); ?>
                            </p>
                        </div>

                        <i class="bi bi-chevron-down" style="font-size: 16px; color: var(--gray-600);"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end glass-card" style="min-width: 220px; border-radius: 12px; padding: 8px; margin-top: 8px;">
                        <li>
                            <div class="dropdown-item-text px-3 py-2">
                                <p class="m-0 fw-semibold" style="font-size: 14px;"><?php echo e($_SESSION['user_name'] ?? ''); ?></p>
                                <p class="m-0 text-muted" style="font-size: 12px;"><?php echo e($_SESSION['user_email'] ?? ''); ?></p>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="/views/admin/profile.php" style="border-radius: 8px; padding: 10px 12px;">
                                <i class="bi bi-person" style="font-size: 16px;"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="/views/admin/profile.php#changePasswordSection" style="border-radius: 8px; padding: 10px 12px;">
                                <i class="bi bi-lock" style="font-size: 16px;"></i>
                                <span>Change Password</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="/views/auth/logout.php" style="border-radius: 8px; padding: 10px 12px;">
                                <i class="bi bi-box-arrow-right" style="font-size: 16px;"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- M3 Search Bar -->
    <div style="margin: 0 16px 16px 16px;">
        <div class="m3-search-container">
            <i class="bi bi-search m3-search-icon"></i>
            <input type="text"
                   class="m3-search-input"
                   id="globalSearch"
                   placeholder="<?php echo e(tr('Search assets, users...')); ?>"
                   autocomplete="off">
            <div class="m3-search-results" id="globalSearchResults"></div>
        </div>
    </div>

    <main class="page-content" style="padding: 0 16px 16px 16px; min-height: calc(100vh - 120px);">
        <div class="dashboard-content">
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stat-card glass-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label m-0"><?php echo e(tr('Total Assets')); ?></p>
                                <h2 class="stat-value m-0"><?php echo $totalAssets; ?></h2>
                                <p class="m-0 mt-1 d-flex align-items-center gap-1" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                    <canvas id="sparkTotal" width="60" height="20" style="vertical-align:middle;"></canvas>
                                    <span><?php echo e(tr('7-day trend')); ?></span>
                                </p>
                            </div>
                            <div class="stat-icon primary" style="margin-bottom: 0;">
                                <i class="bi bi-box-seam" style="font-size: 28px;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stat-card glass-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label m-0"><?php echo e(tr('Available')); ?></p>
                                <h2 class="stat-value m-0"><?php echo $availableAssets; ?></h2>
                                <p class="m-0 mt-1 d-flex align-items-center gap-1" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                    <canvas id="sparkAvailable" width="60" height="20" style="vertical-align:middle;"></canvas>
                                    <span><?php echo $availablePct; ?>%</span>
                                </p>
                            </div>
                            <div class="stat-icon success" style="margin-bottom: 0;">
                                <i class="bi bi-check-circle" style="font-size: 28px;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stat-card glass-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label m-0"><?php echo e(tr('In Use')); ?></p>
                                <h2 class="stat-value m-0"><?php echo $inUseAssets; ?></h2>
                                <p class="m-0 mt-1" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                    <i class="bi bi-arrow-up-circle" style="font-size: 14px;"></i>
                                    <?php echo $inUsePct; ?>% <?php echo e(tr('In Use')); ?>
                                </p>
                            </div>
                            <div class="stat-icon warning" style="margin-bottom: 0;">
                                <i class="bi bi-arrow-repeat" style="font-size: 28px;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stat-card glass-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label m-0"><?php echo e(tr('Total Value')); ?></p>
                                <h2 class="stat-value m-0">$<?php echo number_format($totalValue, 2); ?></h2>
                                <p class="m-0 mt-1" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                    <i class="bi bi-currency-dollar" style="font-size: 14px;"></i>
                                    <?php echo e(tr('Asset worth')); ?>
                                </p>
                            </div>
                            <div class="stat-icon purple" style="margin-bottom: 0;">
                                <i class="bi bi-currency-dollar" style="font-size: 28px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-7">
                    <div class="glass-card p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="m-0" style="font-size: 18px; font-weight: 600; color: var(--md-sys-color-on-surface);">
                                <i class="bi bi-activity me-2" style="font-size: 18px;"></i>
                                <?php echo e(tr('Recent Activities')); ?>
                            </h3>
                            <a href="/views/admin/history.php" class="btn-m3-tonal btn-m3-tonal-primary px-3 py-1" style="font-size: 13px; border-radius: var(--md-sys-shape-corner-full);">
                                <?php echo e(tr('View All')); ?>
                                <i class="bi bi-arrow-right ms-1" style="font-size: 14px;"></i>
                            </a>
                        </div>

                        <div class="activity-list">
                            <?php if (empty($data['recent_activity'])): ?>
                                <p class="text-muted text-center py-4 m-0">No recent activity</p>
                            <?php else: ?>
                                <?php foreach ($data['recent_activity'] as $activity): ?>
                                    <?php
                                    $actionType = $activity['action_type'] ?? '';
                                    if ($actionType === 'Check Out') {
                                        $iconBg = '#FFDDB3';
                                        $iconColor = '#7D5700';
                                        $iconClass = 'bi-arrow-up-circle';
                                        $statusClass = 'status-checkout';
                                    } elseif ($actionType === 'Overdue') {
                                        $iconBg = '#FFDAD6';
                                        $iconColor = '#BA1A1A';
                                        $iconClass = 'bi-exclamation-circle';
                                        $statusClass = 'status-overdue';
                                    } else {
                                        $iconBg = '#C4EED0';
                                        $iconColor = '#386A20';
                                        $iconClass = 'bi-arrow-down-circle';
                                        $statusClass = 'status-checkin';
                                    }
                                    ?>
                                    <div class="activity-item d-flex align-items-center gap-3 p-3 mb-2 <?php echo $statusClass; ?>" style="background: var(--md-sys-color-surface-container-lowest); border-radius: 12px;">
                                        <div class="activity-icon" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: <?php echo $iconBg; ?>;">
                                            <i class="bi <?php echo $iconClass; ?>" style="font-size: 20px; color: <?php echo $iconColor; ?>;"></i>
                                        </div>

                                        <div class="flex-grow-1">
                                            <p class="m-0 fw-semibold" style="font-size: 14px; color: var(--md-sys-color-on-surface);">
                                                <?php echo e(tr($activity['action_type'] ?? '')); ?>: <?php echo e($activity['asset_name'] ?? ''); ?>
                                            </p>
                                            <p class="m-0" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                                <?php echo e($activity['user_name'] ?? ''); ?>
                                                &bull;
                                                <span class="badge" style="font-size: 11px; background: var(--md-sys-color-primary-container); color: var(--md-sys-color-on-primary-container);">
                                                    <?php echo e($activity['asset_code'] ?? ''); ?>
                                                </span>
                                            </p>
                                        </div>

                                        <div class="text-end">
                                            <p class="m-0" style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                                                <?php echo e(tr(itam_time_ago($activity['action_date'] ?? ''))); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="glass-card p-4">
                        <h3 class="mb-3" style="font-size: 18px; font-weight: 600; color: var(--md-sys-color-on-surface);">
                            <i class="bi bi-pie-chart me-2" style="font-size: 18px;"></i>
                            <?php echo e(tr('Assets by Category')); ?>
                        </h3>

                        <?php
                        $categories = $data['categories'] ?? [];
                        $m3Colors = ['#2563EB', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#0EA5E9', '#14B8A6', '#F97316', '#6366F1'];
                        $totalCategoryItems = 0;
                        foreach ($categories as $cat) {
                            $totalCategoryItems += (int)($cat['asset_count'] ?? $cat['count'] ?? 0);
                        }
                        ?>

                        <?php if (empty($categories)): ?>
                            <p class="text-center py-4 m-0" style="color: var(--md-sys-color-on-surface-variant);">
                                <?php echo e(tr('No category data')); ?>
                            </p>
                        <?php else: ?>
                            <div class="category-list">
                                <?php foreach ($categories as $index => $category): ?>
                                    <?php
                                    $categoryName = trim((string)($category['category'] ?? ''));
                                    if ($categoryName === '') $categoryName = 'Unknown';
                                    $categoryCount = (int)($category['asset_count'] ?? $category['count'] ?? 0);
                                    $categoryPercent = $totalCategoryItems > 0
                                        ? (int)round(($categoryCount / $totalCategoryItems) * 100)
                                        : 0;
                                    $categoryColor = $m3Colors[$index % count($m3Colors)];
                                    ?>
                                    <div class="category-item mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="fw-medium" style="font-size: 14px; color: var(--md-sys-color-on-surface-variant);">
                                                <?php echo e(tr($categoryName)); ?>
                                            </span>
                                            <span class="fw-semibold" style="font-size: 14px; color: var(--md-sys-color-on-surface);">
                                                <?php echo $categoryCount; ?> <?php echo e(tr($categoryCount === 1 ? 'item' : 'items')); ?>
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 8px; background: var(--md-sys-color-surface-container-highest); border-radius: 4px;">
                                            <div class="progress-bar"
                                                 role="progressbar"
                                                 style="width: <?php echo $categoryPercent; ?>%; background: <?php echo e($categoryColor); ?>; border-radius: 4px;"
                                                 aria-valuenow="<?php echo $categoryPercent; ?>"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="glass-card p-4">
                <h3 class="mb-3" style="font-size: 18px; font-weight: 600; color: var(--md-sys-color-on-surface);">
                    <i class="bi bi-lightning-charge me-2"></i>
                    <?php echo e(tr('Quick Actions')); ?>
                </h3>

                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="/views/admin/assets.php?action=add" class="btn-m3-tonal btn-m3-tonal-primary w-100 d-flex flex-column align-items-center gap-2 py-3">
                            <i class="bi bi-plus-circle" style="font-size: 24px;"></i>
                            <span><?php echo e(tr('Add Asset')); ?></span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="/views/admin/users.php?action=add" class="btn-m3-tonal btn-m3-tonal-success w-100 d-flex flex-column align-items-center gap-2 py-3">
                            <i class="bi bi-person-plus" style="font-size: 24px;"></i>
                            <span><?php echo e(tr('Add User')); ?></span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="/views/admin/checkout.php" class="btn-m3-tonal btn-m3-tonal-warning w-100 d-flex flex-column align-items-center gap-2 py-3">
                            <i class="bi bi-arrow-left-right" style="font-size: 24px;"></i>
                            <span><?php echo e(tr('Check Out')); ?></span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="/views/admin/reports.php" class="btn-m3-tonal btn-m3-tonal-purple w-100 d-flex flex-column align-items-center gap-2 py-3">
                            <i class="bi bi-file-text" style="font-size: 24px;"></i>
                            <span><?php echo e(tr('Reports')); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="glass-card dashboard-footer text-center" style="margin: 0 16px 16px 16px; padding: 16px; border-radius: 12px;">
        <p class="m-0" style="font-size: 14px; color: var(--gray-600);">
            &copy; 2026 ITAM System - P-line Company | Vientiane, Laos
        </p>
    </footer>
</div>

<style>
.dashboard-header.glass-card:hover,
.dashboard-footer.glass-card:hover {
    transform: none;
}

.dashboard-content .activity-item:hover {
    background: white !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
</style>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

// Sparkline renderer
function drawSparkline(canvasId, dataPoints, color) {
    var canvas = document.getElementById(canvasId);
    if (!canvas || !dataPoints || !dataPoints.length) return;
    var ctx = canvas.getContext('2d');
    var w = canvas.width, h = canvas.height;
    var nums = dataPoints.map(Number);
    var min = Math.min.apply(null, nums);
    var max = Math.max.apply(null, nums);
    var range = max - min || 1;
    var step = w / (nums.length - 1 || 1);

    ctx.clearRect(0, 0, w, h);
    ctx.beginPath();
    ctx.strokeStyle = color;
    ctx.lineWidth = 1.5;
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';

    nums.forEach(function(val, i) {
        var x = i * step;
        var y = h - ((val - min) / range) * (h - 4) - 2;
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    });
    ctx.stroke();
}

var totalTrendData = <?php echo json_encode(array_column($data['total_trend'] ?? [], 'total')); ?>;
var availTrendData = <?php echo json_encode(array_column($data['available_trend'] ?? [], 'available')); ?>;
drawSparkline('sparkTotal', totalTrendData, '#4355B9');
drawSparkline('sparkAvailable', availTrendData, '#386A20');
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
