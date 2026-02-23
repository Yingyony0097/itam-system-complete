<?php
/**
 * ITAM System - User Profile
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../models/Asset.php';

requireAuth();

// Keep role-specific URLs clean.
if (isAdmin()) {
    redirect('/views/admin/profile.php');
}

$userController = new UserController();
$assetModel = new Asset();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? ''
    ];

    $result = $userController->updateUser($_SESSION['user_id'], $data);
    if ($result['success']) {
        $_SESSION['user_name'] = $data['name'];
        $_SESSION['user_email'] = $data['email'];
        $_SESSION['success'] = 'Profile updated successfully';
    } else {
        $_SESSION['error'] = $result['message'];
    }
    redirect($_SERVER['PHP_SELF']);
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $auth = new AuthController();
    $result = $auth->changePassword(
        $_SESSION['user_id'],
        $_POST['current_password'] ?? '',
        $_POST['new_password'] ?? ''
    );

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    redirect($_SERVER['PHP_SELF']);
}

$currentUser = $userController->getUser($_SESSION['user_id']);
$myAssets = $assetModel->getByUser($_SESSION['user_id']);

$pageTitle = 'My Profile';
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
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="profile-card">
                    <div class="profile-avatar-large"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                    <h4><?php echo e($_SESSION['user_name']); ?></h4>
                    <p class="text-muted mb-4"><?php echo e($_SESSION['user_email']); ?></p>

                    <div class="d-flex justify-content-center gap-3 mb-4">
                        <div class="text-center">
                            <div class="fw-bold text-primary"><?php echo count($myAssets); ?></div>
                            <small class="text-muted">Assets</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-success"><?php echo e($_SESSION['user_role']); ?></div>
                            <small class="text-muted">Role</small>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="#changePasswordSection" class="btn btn-outline-primary">
                            <i class="bi bi-key me-2"></i>Change Password
                        </a>
                        <a href="/views/auth/logout.php" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card p-4 mb-4">
                    <h5 class="mb-4">Account Information</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control form-control-glass" value="<?php echo e($currentUser['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-glass" value="<?php echo e($currentUser['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control form-control-glass" value="<?php echo e($currentUser['role']); ?>" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control form-control-glass" value="<?php echo date('F j, Y', strtotime($currentUser['created_at'])); ?>" readonly disabled>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary-gradient">
                                <i class="bi bi-check-lg me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <div class="glass-card p-4" id="changePasswordSection">
                    <h5 class="mb-4">Change Password</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control form-control-glass" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control form-control-glass" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control form-control-glass" required minlength="8">
                        </div>
                        <button type="submit" class="btn btn-primary-gradient">
                            <i class="bi bi-shield-check me-2"></i>Update Password
                        </button>
                    </form>
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

