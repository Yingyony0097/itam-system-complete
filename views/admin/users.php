<?php
/**
 * ITAM System - User Management
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/UserController.php';

requireAdmin();

$userController = new UserController();
$search = trim((string)($_GET['search'] ?? ''));

// Handle Deactivate
if (isset($_GET['deactivate']) && is_numeric($_GET['deactivate'])) {
    $result = $userController->deactivateUser($_GET['deactivate']);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    $redirect = '/views/admin/users.php';
    if ($search !== '') {
        $redirect .= '?search=' . urlencode($search);
    }
    redirect($redirect);
}

$users = $userController->getUsers($search);

$pageTitle = 'User Management';
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
                <h1 class="page-title">User Management</h1>
                <p class="text-muted mb-0">Manage system users and permissions</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#userImportModal">
                    <i class="bi bi-file-earmark-excel me-2"></i>Import Excel
                </button>
                <button class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetUserForm()">
                    <i class="bi bi-plus-lg me-2"></i>Add New User
                </button>
            </div>
        </div>

        <div class="search-bar">
            <form method="GET" action="" class="row g-2 align-items-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(255, 255, 255, 0.5); border-right: none;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control form-control-glass" placeholder="Search users by name or email" value="<?php echo e($search); ?>" style="border-left: none;">
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search me-2"></i>Search
                    </button>
                </div>
                <?php if ($search !== ''): ?>
                    <div class="col-12 col-md-auto">
                        <a class="btn btn-outline-secondary" href="/views/admin/users.php">
                            <i class="bi bi-x-circle me-2"></i>Clear
                        </a>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto text-muted small">
                        Showing <?php echo count($users); ?> result(s) for "<?php echo e($search); ?>"
                    </div>
                <?php else: ?>
                    <div class="col-12 col-md-auto ms-md-auto text-muted small">
                        Total: <?php echo count($users); ?> active user(s)
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-glass">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Assets Assigned</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        require_once __DIR__ . '/../../models/Asset.php';
                        $assetModel = new Asset();
                        foreach ($users as $user): 
                            $assetCount = count($assetModel->getByUser($user['user_id']));
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($user['photo_url'])): ?>
                                            <img src="<?php echo e($user['photo_url']); ?>" alt="" class="user-avatar-img" style="width:32px;height:32px;">
                                        <?php else: ?>
                                            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 14px;">
                                                <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($user['name']); ?></div>
                                            <?php if (!empty($user['department'])): ?>
                                                <small class="text-muted"><?php echo e($user['department']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo e($user['email']); ?></div>
                                    <?php if (!empty($user['phone'])): ?>
                                        <small class="text-muted"><?php echo e($user['phone']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-custom badge-<?php echo $user['role'] === 'Admin' ? 'admin' : 'category'; ?>">
                                        <?php echo e($user['role']); ?>
                                    </span>
                                </td>
                                <td><span class="badge-custom badge-available">Active</span></td>
                                <td><?php echo $assetCount; ?> assets</td>
                                <td class="text-end">
                                    <button class="btn-icon me-1" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                        <a href="?deactivate=<?php echo $user['user_id']; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="btn-icon btn-icon-danger" onclick="return confirmDelete('Are you sure you want to deactivate this user?')" title="Deactivate">
                                            <i class="bi bi-person-x"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade modal-glass" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/views/admin/user_action.php" id="userForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="user_id" id="userId">
                <input type="hidden" name="action" id="userFormAction" value="create">

                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control form-control-glass" id="userName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control form-control-glass" id="userEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select form-control-glass" id="userRole" required>
                            <option value="User">User</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control form-control-glass" id="userPassword" required>
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-gradient">
                        <i class="bi bi-check-lg me-2"></i>Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Import Modal -->
<div class="modal fade modal-glass" id="userImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/views/admin/user_import.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2"></i>Import Users from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Excel File (.xlsx)</label>
                        <input type="file" name="import_file" class="form-control form-control-glass" accept=".xlsx" required>
                        <small class="text-muted">Max 5MB. Only .xlsx files are supported.</small>
                    </div>
                    <div class="glass-card p-3" style="background: var(--md-sys-color-surface-container-low);">
                        <h6 class="mb-2" style="font-size: 13px;"><i class="bi bi-info-circle me-1"></i>Expected Columns</h6>
                        <div style="font-size: 12px; color: var(--md-sys-color-on-surface-variant);">
                            <code>Name</code> (required), <code>Email</code> (required),
                            <code>Password</code> (required, min 8 chars), <code>Role</code> (Admin or User),
                            <code>Phone</code>, <code>Department</code>
                        </div>
                        <a href="/public/assets/templates/user_import_template.xlsx" class="btn btn-outline-secondary btn-sm mt-2" download>
                            <i class="bi bi-download me-1"></i>Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-gradient">
                        <i class="bi bi-upload me-2"></i>Import
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

function resetUserForm() {
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userFormAction').value = 'create';
    document.getElementById('userModalTitle').textContent = 'Add New User';
    document.getElementById('userPassword').required = true;
}

function editUser(user) {
    document.getElementById('userId').value = user.user_id;
    document.getElementById('userFormAction').value = 'update';
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role;
    document.getElementById('userPassword').required = false;
    document.getElementById('userPassword').value = '';

    new bootstrap.Modal(document.getElementById('userModal')).show();
}

</script>

<?php if (($_GET['action'] ?? '') === 'add'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    resetUserForm();
    new bootstrap.Modal(document.getElementById('userModal')).show();
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
