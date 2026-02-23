<?php
/**
 * ITAM System - User Action Handler
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/UserController.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/views/admin/users.php');
}

// Validate CSRF
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token';
    redirect('/views/admin/users.php');
}

$userController = new UserController();
$action = $_POST['action'] ?? 'create';

$data = [
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'role' => $_POST['role'] ?? 'User'
];

if (!empty($_POST['password'])) {
    $data['password'] = $_POST['password'];
}

if ($action === 'create') {
    $result = $userController->createUser($data);
} else {
    $userId = $_POST['user_id'] ?? 0;
    $result = $userController->updateUser($userId, $data);
}

$message = $result['message'] ?? ($result['success'] ? 'Operation successful' : 'Operation failed');

if ($result['success']) {
    $_SESSION['success'] = $message;
} else {
    $_SESSION['error'] = $message;
}

redirect('/views/admin/users.php');
