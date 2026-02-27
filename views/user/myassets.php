<?php
/**
 * ລະບົບ ITAM - ຊັບສິນຂອງຂ້ອຍ (ໜ້າຜູ້ໃຊ້)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Asset.php';

requireAuth();

$assetModel = new Asset();
$myAssets = $assetModel->getByUser($_SESSION['user_id']);

$pageTitle = 'My Assets';
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
                <h1 class="page-title">My Assigned Assets</h1>
                <p class="text-muted mb-0">Assets currently assigned to you</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (empty($myAssets)): ?>
                <div class="col-12">
                    <div class="glass-card p-5 text-center">
                        <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                        <h5 class="mt-3 text-muted">No Assets Assigned</h5>
                        <p class="text-muted">You currently don't have any assets assigned to you.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($myAssets as $asset): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="glass-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge-custom badge-category"><?php echo e($asset['category']); ?></span>
                                <span class="badge-custom badge-in-use">In Use</span>
                            </div>
                            <h5><?php echo e($asset['asset_name']); ?></h5>
                            <p class="text-muted mb-3"><?php echo e($asset['brand'] . ' ' . $asset['model']); ?></p>

                            <div class="mb-3">
                                <small class="text-muted d-block">Asset Code</small>
                                <code><?php echo e($asset['asset_code']); ?></code>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Serial Number</small>
                                <code><?php echo e($asset['serial_number']); ?></code>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Assigned Date</small>
                                <div><?php echo date('F j, Y', strtotime($asset['assigned_date'])); ?></div>
                            </div>

                            <div class="mb-0">
                                <small class="text-muted d-block">Purchase Price</small>
                                <div class="fw-semibold">₭<?php echo number_format($asset['purchase_price']); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
