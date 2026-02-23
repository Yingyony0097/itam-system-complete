<?php
/**
 * ITAM System - 403 Forbidden Page
 */
require_once __DIR__ . '/../../config/config.php';

$pageTitle = 'Access Denied';
include __DIR__ . '/../layouts/header.php';
?>

<div class="login-container">
    <div class="login-card text-center">
        <div class="login-logo" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
            <i class="bi bi-shield-exclamation"></i>
        </div>
        <h3 class="mb-3">403 - Access Denied</h3>
        <p class="text-muted mb-4">
            You do not have permission to access this page.
        </p>
        <a href="/views/auth/login.php" class="btn btn-primary-gradient w-100">
            <i class="bi bi-house me-2"></i>Go Home
        </a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
