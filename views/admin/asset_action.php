<?php
/**
 * ITAM System - Asset Action Handler
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

$assetController = new AssetController();
$action = $_POST['action'] ?? 'create';

$data = [
    'asset_name' => $_POST['asset_name'] ?? '',
    'category' => $_POST['category'] ?? '',
    'serial_number' => $_POST['serial_number'] ?? '',
    'brand' => $_POST['brand'] ?? '',
    'model' => $_POST['model'] ?? '',
    'purchase_date' => $_POST['purchase_date'] ?? null,
    'purchase_price' => $_POST['purchase_price'] ?? 0,
    'status' => $_POST['status'] ?? 'Available'
];

if ($action === 'create') {
    $result = $assetController->createAsset($data);
} else {
    $assetId = $_POST['asset_id'] ?? 0;
    $result = $assetController->updateAsset($assetId, $data);
}

if ($result['success']) {
    $_SESSION['success'] = $result['message'] ?? 'Operation successful';
} else {
    $_SESSION['error'] = $result['message'] ?? 'Operation failed';
}

redirect('/views/admin/assets.php');
