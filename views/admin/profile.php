<?php
/**
 * ລະບົບ ITAM - ໂປຣໄຟລ໌ຜູ້ດູແລ
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../models/Asset.php';

requireAuth();

$userController = new UserController();
$assetModel = new Asset();

// ຈັດການອັບເດດໂປຣໄຟລ໌
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? null,
        'department' => $_POST['department'] ?? null
    ];

    // ຈັດການອັບໂຫຼດຮູບ
    $hasNewPhoto = !empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK;
    $removePhoto = !empty($_POST['remove_photo']);

    if ($hasNewPhoto) {
        $file = $_FILES['photo'];
        if ($file['size'] > MAX_FILE_SIZE) {
            $_SESSION['error'] = 'File size exceeds 5MB limit';
            redirect($_SERVER['PHP_SELF']);
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            $_SESSION['error'] = 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS);
            redirect($_SERVER['PHP_SELF']);
        }
        $filename = 'user_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/users/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            // ລຶບຮູບເກົ່າ
            $existing = $userController->getUser($_SESSION['user_id']);
            if (!empty($existing['photo_url'])) {
                $oldPath = __DIR__ . '/../..' . $existing['photo_url'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $data['photo_url'] = '/public/uploads/users/' . $filename;
        }
    } elseif ($removePhoto) {
        $existing = $userController->getUser($_SESSION['user_id']);
        if (!empty($existing['photo_url'])) {
            $oldPath = __DIR__ . '/../..' . $existing['photo_url'];
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $data['photo_url'] = null;
    }

    $result = $userController->updateUser($_SESSION['user_id'], $data);
    if ($result['success']) {
        $_SESSION['user_name'] = $data['name'];
        $_SESSION['user_email'] = $data['email'];
        if (array_key_exists('photo_url', $data)) {
            $_SESSION['user_photo'] = $data['photo_url'];
        }
        $_SESSION['success'] = 'Profile updated successfully';
    } else {
        $_SESSION['error'] = $result['message'];
    }
    redirect($_SERVER['PHP_SELF']);
}

// ຈັດການປ່ຽນລະຫັດຜ່ານ
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
            <?php echo userAvatar(); ?>
        </div>
    </header>

    <div class="fade-in">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="profile-card">
                    <?php if (!empty($currentUser['photo_url'])): ?>
                        <img src="<?php echo e($currentUser['photo_url']); ?>" alt="<?php echo e($currentUser['name']); ?>" class="profile-avatar-photo">
                    <?php else: ?>
                        <div class="profile-avatar-large"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                    <?php endif; ?>
                    <h4><?php echo e($_SESSION['user_name']); ?></h4>
                    <p class="text-muted mb-1"><?php echo e($_SESSION['user_email']); ?></p>
                    <?php if (!empty($currentUser['department'])): ?>
                        <p class="mb-0" style="font-size: 13px; color: var(--md-sys-color-on-surface-variant);">
                            <i class="bi bi-building me-1"></i><?php echo e($currentUser['department']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($currentUser['phone'])): ?>
                        <p class="mb-0" style="font-size: 13px; color: var(--md-sys-color-on-surface-variant);">
                            <i class="bi bi-telephone me-1"></i><?php echo e($currentUser['phone']); ?>
                        </p>
                    <?php endif; ?>

                    <div class="d-flex justify-content-center gap-3 my-4">
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
                    <form method="POST" action="" enctype="multipart/form-data">
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
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control form-control-glass" value="<?php echo e($currentUser['phone'] ?? ''); ?>" placeholder="e.g., +856 20 1234 5678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control form-control-glass" value="<?php echo e($currentUser['department'] ?? ''); ?>" placeholder="e.g., IT, HR, Finance">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control form-control-glass" value="<?php echo e($currentUser['role']); ?>" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control form-control-glass" value="<?php echo date('F j, Y', strtotime($currentUser['created_at'])); ?>" readonly disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="photo" class="form-control form-control-glass" accept="image/jpeg,image/png,image/gif" id="profilePhoto">
                                <small class="text-muted">Max 5MB. Allowed: JPG, PNG, GIF</small>
                                <?php if (!empty($currentUser['photo_url'])): ?>
                                    <div class="mt-2 d-flex align-items-center gap-3">
                                        <img src="<?php echo e($currentUser['photo_url']); ?>" alt="Current photo" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 1px solid var(--md-sys-color-outline-variant);">
                                        <label class="m3-checkbox">
                                            <input type="checkbox" name="remove_photo" value="1">
                                            <span>Remove photo</span>
                                        </label>
                                    </div>
                                <?php endif; ?>
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
