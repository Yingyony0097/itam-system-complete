<?php
/**
 * ລະບົບ ITAM - ໜ້າ 404 ບໍ່ພົບໜ້າ
 */
require_once __DIR__ . '/../../config/config.php';

$pageTitle = 'Page Not Found';
include __DIR__ . '/../layouts/header.php';
?>

<div class="login-container">
    <div class="login-card text-center">
        <div class="login-logo">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h3 class="mb-3">404 - Page Not Found</h3>
        <p class="text-muted mb-4">
            The page you are looking for does not exist or has been moved.
        </p>
        <a href="/views/auth/login.php" class="btn btn-primary-gradient w-100">
            <i class="bi bi-house me-2"></i>Go Home
        </a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
