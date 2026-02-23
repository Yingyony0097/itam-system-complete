<?php
/**
 * ITAM System - Login Page
 */
require_once __DIR__ . '/../../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? '/views/admin/dashboard.php' : '/views/user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../controllers/AuthController.php';

    $auth = new AuthController();
    $result = $auth->login($_POST['email'], $_POST['password']);

    if ($result['success']) {
        $dashboard = $result['role'] === ROLE_ADMIN ? '/views/admin/dashboard.php' : '/views/user/dashboard.php';
        redirect($dashboard);
    } else {
        $error = tr($result['message'] ?? '');
    }
}

$pageTitle = t('auth.login');
include __DIR__ . '/../layouts/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="d-flex justify-content-end mb-3">
            <div class="btn-group btn-group-sm" role="group" aria-label="Language switch">
                <a href="<?php echo e(langUrl('en')); ?>" class="btn <?php echo currentLang() === 'en' ? 'btn-primary' : 'btn-outline-primary'; ?>">EN</a>
                <a href="<?php echo e(langUrl('lo')); ?>" class="btn <?php echo currentLang() === 'lo' ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo e(t('common.lao')); ?></a>
            </div>
        </div>
        <div class="login-logo">
            <i class="bi bi-box-seam"></i>
        </div>
        <h3 class="text-center mb-2">ITAM System</h3>
        <p class="text-muted text-center mb-4">
            <?php echo e(t('auth.system_title')); ?><br>
            <small><?php echo e(t('auth.company_line')); ?></small>
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="mb-3">
                <label class="form-label"><?php echo e(t('auth.email_address')); ?></label>
                <input type="email" name="email" class="form-control form-control-glass" placeholder="admin@pline.com" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label"><?php echo e(t('auth.password')); ?></label>
                <input type="password" name="password" class="form-control form-control-glass" placeholder="••••••••" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label" for="rememberMe" style="font-size: 14px;"><?php echo e(t('auth.remember_me')); ?></label>
                </div>
                <span class="text-muted" style="font-size: 14px;"><?php echo e(t('auth.forgot_password_contact_admin')); ?></span>
            </div>

            <button type="submit" class="btn btn-primary-gradient w-100 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i><?php echo e(t('auth.sign_in')); ?>
            </button>
        </form>

        <div class="text-center">
            <p class="text-muted mb-2" style="font-size: 12px;"><?php echo e(t('auth.demo_accounts')); ?></p>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillDemo('admin')"><?php echo e(t('auth.admin_demo')); ?></button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillDemo('user')"><?php echo e(t('auth.user_demo')); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(type) {
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');

    if (type === 'admin') {
        email.value = 'admin@pline.com';
        password.value = 'password';
    } else {
        email.value = 'user@pline.com';
        password.value = 'password';
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
