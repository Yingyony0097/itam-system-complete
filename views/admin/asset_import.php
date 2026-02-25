<?php
/**
 * ITAM System - Asset Import Handler
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AssetController.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/views/admin/assets.php');
}

// Validate CSRF
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token';
    redirect('/views/admin/assets.php');
}

if (empty($_FILES['import_file']['tmp_name']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Please select an Excel file to import';
    redirect('/views/admin/assets.php');
}

$assetController = new AssetController();
$result = $assetController->importAssets($_FILES['import_file']);

if ($result['success']) {
    $msg = $result['created'] . ' asset(s) imported successfully';
    if (!empty($result['errors'])) {
        $msg .= '. ' . count($result['errors']) . ' error(s): ' . implode('; ', array_slice($result['errors'], 0, 5));
        if (count($result['errors']) > 5) {
            $msg .= '... and ' . (count($result['errors']) - 5) . ' more';
        }
    }
    if ($result['created'] > 0) {
        $_SESSION['success'] = $msg;
    } else {
        $_SESSION['error'] = $msg;
    }
} else {
    $_SESSION['error'] = $result['message'];
}

redirect('/views/admin/assets.php');
