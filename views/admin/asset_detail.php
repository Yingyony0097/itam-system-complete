<?php
/**
 * ITAM System - Asset Detail (Admin)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Asset.php';
require_once __DIR__ . '/../../models/CheckLog.php';
require_once __DIR__ . '/../../models/User.php';

requireAdmin();

$assetId = $_GET['id'] ?? null;
if (!is_numeric($assetId)) {
    $_SESSION['error'] = 'Invalid asset ID';
    redirect('/views/admin/assets.php');
}

$assetId = (int)$assetId;
$assetModel = new Asset();
$logModel = new CheckLog();
$userModel = new User();

$asset = $assetModel->find($assetId);
if (!$asset) {
    $_SESSION['error'] = 'Asset not found';
    redirect('/views/admin/assets.php');
}

$assignedUser = null;
if (!empty($asset['assigned_to'])) {
    $assignedUser = $userModel->find((int)$asset['assigned_to']);
}

$logs = $logModel->getByAsset($assetId);

function itam_asset_icon($category) {
    $category = strtolower(trim((string)$category));
    if ($category === 'computer' || $category === 'laptop' || $category === 'desktop') return 'bi-pc-display';
    if ($category === 'phone' || $category === 'mobile') return 'bi-phone';
    if ($category === 'printer') return 'bi-printer';
    if ($category === 'accessory' || $category === 'accessories') return 'bi-usb-symbol';
    if ($category === 'monitor') return 'bi-display';
    if ($category === 'tablet') return 'bi-tablet';
    if ($category === 'network device' || $category === 'network') return 'bi-router';
    if ($category === 'server') return 'bi-hdd-rack';
    return 'bi-box-seam';
}

$pageTitle = 'Asset Detail';
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
                <h1 class="page-title">Asset Detail</h1>
                <p class="text-muted mb-0">View complete information and history</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/views/admin/assets.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
                <a href="/views/admin/assets.php?delete=<?php echo (int)$asset['asset_id']; ?>" class="btn btn-outline-danger" onclick="return confirmDelete('Are you sure you want to delete this asset?')">
                    <i class="bi bi-trash me-2"></i>Delete
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card p-4 h-100">
                    <?php if (!empty($asset['photo_url'])): ?>
                        <div style="margin: -16px -16px 16px -16px;">
                            <img src="<?php echo e($asset['photo_url']); ?>" alt="<?php echo e($asset['asset_name'] ?? ''); ?>"
                                 style="width: 100%; max-height: 220px; object-fit: cover; border-radius: var(--md-sys-shape-corner-large) var(--md-sys-shape-corner-large) 0 0;">
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="stat-icon primary" style="margin-bottom: 0; width: 56px; height: 56px;">
                            <i class="bi <?php echo itam_asset_icon($asset['category'] ?? ''); ?>"></i>
                        </div>
                        <span class="badge-custom badge-<?php echo ($asset['status'] ?? '') === 'Available' ? 'available' : 'in-use'; ?>">
                            <?php echo e($asset['status'] ?? ''); ?>
                        </span>
                    </div>

                    <h4 class="mb-1"><?php echo e($asset['asset_name'] ?? ''); ?>
                    <div class="text-muted mb-3"><?php echo e($asset['brand'] ?? ''); ?> <?php echo e($asset['model'] ?? ''); ?></div>

                    <div class="mb-3">
                        <div class="text-muted" style="font-size: 12px;">Asset Code</div>
                        <code><?php echo e($asset['asset_code'] ?? ''); ?></code>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted" style="font-size: 12px;">Category</div>
                        <div class="fw-semibold"><?php echo e($asset['category'] ?? ''); ?></div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted" style="font-size: 12px;">Serial Number</div>
                        <div class="fw-semibold"><?php echo e($asset['serial_number'] ?? '-'); ?></div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted" style="font-size: 12px;">Assigned To</div>
                        <?php if ($assignedUser): ?>
                            <div class="fw-semibold"><?php echo e($assignedUser['name']); ?></div>
                            <div class="text-muted" style="font-size: 13px;"><?php echo e($assignedUser['email']); ?></div>
                        <?php else: ?>
                            <div class="fw-semibold">-</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-0">
                        <div class="text-muted" style="font-size: 12px;">Assigned Date</div>
                        <div class="fw-semibold">
                            <?php echo !empty($asset['assigned_date']) ? date('F j, Y', strtotime($asset['assigned_date'])) : '-'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card p-4">
                    <h5 class="mb-3">Asset Information</h5>
                    <div class="table-glass">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 200px;">Purchase Date</td>
                                    <td class="fw-semibold"><?php echo !empty($asset['purchase_date']) ? date('F j, Y', strtotime($asset['purchase_date'])) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Purchase Price</td>
                                    <td class="fw-semibold">$<?php echo number_format((float)($asset['purchase_price'] ?? 0), 2); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Created</td>
                                    <td class="fw-semibold"><?php echo !empty($asset['created_at']) ? date('F j, Y g:i A', strtotime($asset['created_at'])) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Last Updated</td>
                                    <td class="fw-semibold"><?php echo !empty($asset['updated_at']) ? date('F j, Y g:i A', strtotime($asset['updated_at'])) : '-'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <div class="glass-card p-4 mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Check In/Out History</h5>
                <button class="btn btn-outline-primary no-print" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
            </div>

            <div class="table-glass">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Action</th>
                            <th>User</th>
                            <th>Performed By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No history records found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                    $isCheckOut = ($log['action_type'] ?? '') === 'Check Out';
                                    $badgeClass = $isCheckOut ? 'badge-in-use' : 'badge-available';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo date('M j, Y', strtotime($log['action_date'])); ?></div>
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($log['action_date'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge-custom <?php echo $badgeClass; ?>">
                                            <?php echo e($log['action_type'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td><?php echo e($log['user_name'] ?? ''); ?></td>
                                    <td><?php echo e($log['performed_by_name'] ?? ''); ?></td>
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
