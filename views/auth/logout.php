<?php
/**
 * ITAM System - Logout
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

if (!isLoggedIn()) {
    redirect('/views/auth/login.php');
}

$fallback = isAdmin() ? '/views/admin/dashboard.php' : '/views/user/dashboard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['error'] = 'Invalid logout request';
    redirect($fallback);
}

if (!validateCSRFToken((string)($_POST['csrf_token'] ?? ''))) {
    $_SESSION['error'] = 'Invalid request token';
    redirect($fallback);
}

$auth = new AuthController();
$auth->logout();

redirect('/views/auth/login.php');
