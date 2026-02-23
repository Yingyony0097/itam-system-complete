<?php
/**
 * ITAM System - Assets Management
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AssetController.php';

requireAdmin();

$assetController = new AssetController();

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $result = $assetController->deleteAsset($_GET['delete']);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    redirect('/views/admin/assets.php');
}

// Get filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'category' => $_GET['category'] ?? '',
    'status' => $_GET['status'] ?? ''
];

$assets = $assetController->getAssets($filters);

$pageTitle = 'Asset Management';
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
                <h1 class="page-title">Asset Management</h1>
                <p class="text-muted mb-0">Manage and track all IT assets</p>
            </div>
            <button class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#assetModal" onclick="resetAssetForm()">
                <i class="bi bi-plus-lg me-2"></i>Add New Asset
            </button>
        </div>

        <!-- Search and Filter Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, serial number..." value="<?php echo e($filters['search']); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <option value="Computer" <?php echo $filters['category'] === 'Computer' ? 'selected' : ''; ?>>Computers</option>
                            <option value="Phone" <?php echo $filters['category'] === 'Phone' ? 'selected' : ''; ?>>Phones</option>
                            <option value="Printer" <?php echo $filters['category'] === 'Printer' ? 'selected' : ''; ?>>Printers</option>
                            <option value="Accessory" <?php echo $filters['category'] === 'Accessory' ? 'selected' : ''; ?>>Accessories</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="Available" <?php echo $filters['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                            <option value="In Use" <?php echo $filters['status'] === 'In Use' ? 'selected' : ''; ?>>In Use</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="/views/admin/assets.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Assets Table -->
        <div class="table-glass">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Asset ID</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Serial Number</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No assets found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assets as $asset): ?>
                            <tr>
                                <td><code><?php echo e($asset['asset_code']); ?></code></td>
                                <td>
                                    <div class="fw-semibold"><?php echo e($asset['asset_name']); ?></div>
                                    <small class="text-muted"><?php echo e($asset['brand'] . ' ' . $asset['model']); ?></small>
                                </td>
                                <td><span class="badge-custom badge-category"><?php echo e($asset['category']); ?></span></td>
                                <td><small class="font-monospace"><?php echo e($asset['serial_number']); ?></small></td>
                                <td>
                                    <span class="badge-custom badge-<?php echo $asset['status'] === 'Available' ? 'available' : 'in-use'; ?>">
                                        <?php echo e($asset['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $asset['assigned_user_name'] ? e($asset['assigned_user_name']) : '-'; ?></td>
                                <td class="text-end">
                                    <button class="btn-icon me-1" onclick="viewAsset(<?php echo $asset['asset_id']; ?>)" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn-icon me-1" onclick="editAsset(<?php echo htmlspecialchars(json_encode($asset)); ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?delete=<?php echo $asset['asset_id']; ?>" class="btn-icon btn-icon-danger" onclick="return confirmDelete('Are you sure you want to delete this asset?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">Showing <?php echo count($assets); ?> assets</div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Asset Modal -->
<div class="modal fade modal-glass" id="assetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/views/admin/asset_action.php" id="assetForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="asset_id" id="assetId">
                <input type="hidden" name="action" id="formAction" value="create">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Asset Code</label>
                            <input type="text" class="form-control form-control-glass" id="assetCode" readonly placeholder="Auto-generated">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Asset Name *</label>
                            <input type="text" name="asset_name" class="form-control form-control-glass" id="assetName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select form-control-glass" id="assetCategory" required>
                                <option value="">Select Category</option>
                                <option value="Computer">Computer</option>
                                <option value="Phone">Phone</option>
                                <option value="Printer">Printer</option>
                                <option value="Accessory">Accessory</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control form-control-glass" id="assetSerial">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control form-control-glass" id="assetBrand" placeholder="e.g., Dell, Apple, HP">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control form-control-glass" id="assetModel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control form-control-glass" id="assetPurchaseDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Price ($)</label>
                            <input type="number" name="purchase_price" class="form-control form-control-glass" id="assetPrice" min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-control-glass" id="assetStatus">
                                <option value="Available">Available</option>
                                <option value="In Use">In Use</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-gradient">
                        <i class="bi bi-check-lg me-2"></i>Save Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

function resetAssetForm() {
    document.getElementById('assetForm').reset();
    document.getElementById('assetId').value = '';
    document.getElementById('formAction').value = 'create';
    document.getElementById('modalTitle').textContent = 'Add New Asset';
    document.getElementById('assetCode').value = '';
}

function editAsset(asset) {
    document.getElementById('assetId').value = asset.asset_id;
    document.getElementById('formAction').value = 'update';
    document.getElementById('modalTitle').textContent = 'Edit Asset';
    document.getElementById('assetCode').value = asset.asset_code;
    document.getElementById('assetName').value = asset.asset_name;
    document.getElementById('assetCategory').value = asset.category;
    document.getElementById('assetSerial').value = asset.serial_number;
    document.getElementById('assetBrand').value = asset.brand;
    document.getElementById('assetModel').value = asset.model;
    document.getElementById('assetPurchaseDate').value = asset.purchase_date;
    document.getElementById('assetPrice').value = asset.purchase_price;
    document.getElementById('assetStatus').value = asset.status;

    new bootstrap.Modal(document.getElementById('assetModal')).show();
}

function viewAsset(id) {
    // Fetch asset details via AJAX or redirect to detail page
    window.location.href = '/views/admin/asset_detail.php?id=' + id;
}
</script>

<?php if (($_GET['action'] ?? '') === 'add'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    resetAssetForm();
    new bootstrap.Modal(document.getElementById('assetModal')).show();
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
