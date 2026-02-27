<?php
/**
 * ລະບົບ ITAM - ຕົວຈັດການການດຳເນີນການຊັບສິນ
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AssetController.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/views/admin/assets.php');
}

// ກວດສອບ CSRF
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

// ຈັດການອັບໂຫຼດຮູບ
$hasNewPhoto = !empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK;
$removePhoto = !empty($_POST['remove_photo']);

if ($hasNewPhoto) {
    $uploadResult = $assetController->uploadPhoto($_FILES['photo']);
    if ($uploadResult['success']) {
        $data['photo_url'] = $uploadResult['photo_url'];
    } else {
        $_SESSION['error'] = $uploadResult['message'];
        redirect('/views/admin/assets.php');
    }
}

if ($action === 'create') {
    $result = $assetController->createAsset($data);
} else {
    $assetId = (int)($_POST['asset_id'] ?? 0);

    // ລຶບຮູບເກົ່າຖ້າປ່ຽນ ຫຼື ລຶບ
    if ($hasNewPhoto || $removePhoto) {
        $existing = $assetController->getAsset($assetId);
        if ($existing && !empty($existing['photo_url'])) {
            $assetController->deletePhoto($existing['photo_url']);
        }
    }

    if ($removePhoto && !$hasNewPhoto) {
        $data['photo_url'] = null;
    }

    $result = $assetController->updateAsset($assetId, $data);
}

if ($result['success']) {
    $_SESSION['success'] = $result['message'] ?? 'Operation successful';
} else {
    $_SESSION['error'] = $result['message'] ?? 'Operation failed';
}

redirect('/views/admin/assets.php');
