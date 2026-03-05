<?php
/**
 * ລະບົບ ITAM - ໜ້າເຂົ້າລະບົບ
 */
require_once __DIR__ . '/../../config/config.php';

// ປ່ຽນເສັ້ນທາງຖ້າເຂົ້າລະບົບແລ້ວ
if (isLoggedIn()) {
    redirect(isAdmin() ? '/views/admin/dashboard.php' : '/views/user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken((string)($_POST['csrf_token'] ?? ''))) {
        $error = tr('Invalid request token. Please refresh and try again.');
    } else {
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
}

$pageTitle = t('auth.login');
include __DIR__ . '/../layouts/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="d-flex justify-content-end mb-4">
            <div class="m3-lang-toggle" role="group" aria-label="Language switch">
                <a href="<?php echo e(langUrl('en')); ?>" class="m3-lang-btn <?php echo currentLang() === 'en' ? 'active' : ''; ?>">EN</a>
                <a href="<?php echo e(langUrl('lo')); ?>" class="m3-lang-btn <?php echo currentLang() === 'lo' ? 'active' : ''; ?>"><?php echo e(t('common.lao')); ?></a>
            </div>
        </div>

        <div class="login-logo">
            <i class="bi bi-box-seam"></i>
        </div>

        <h3 style="text-align: center; font-size: 24px; font-weight: 600; color: var(--md-sys-color-on-surface); margin-bottom: 4px;">
            ITAM System
        </h3>
        <p style="text-align: center; margin-bottom: 32px; color: var(--md-sys-color-on-surface-variant); font-size: 14px; line-height: 1.5;">
            <?php echo e(t('auth.system_title')); ?><br>
            <span style="font-size: 13px;"><?php echo e(t('auth.company_line')); ?></span>
        </p>

        <?php if ($error): ?>
            <div class="m3-error-container" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <span><?php echo e($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="m3-text-field">
                <input type="email" name="email" id="loginEmail" placeholder=" " required autofocus>
                <label for="loginEmail"><?php echo e(t('auth.email_address')); ?></label>
            </div>

            <div class="m3-text-field">
                <input type="password" name="password" id="loginPassword" placeholder=" " required>
                <label for="loginPassword"><?php echo e(t('auth.password')); ?></label>
            </div>

            <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 32px;">
                <label class="m3-checkbox">
                    <input type="checkbox" name="remember" id="rememberMe">
                    <span><?php echo e(t('auth.remember_me')); ?></span>
                </label>
                <span class="login-footer-text" style="font-size: 13px;"><?php echo e(t('auth.forgot_password_contact_admin')); ?></span>
            </div>

            <button type="submit" class="m3-btn-filled">
                <i class="bi bi-box-arrow-in-right"></i>
                <?php echo e(t('auth.sign_in')); ?>
            </button>
        </form>

    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
