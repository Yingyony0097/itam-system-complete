<?php
/**
 * ITAM System - Global Search API Endpoint
 * Returns JSON results for the dashboard search bar.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Asset.php';

requireAuth();

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$assetModel = new Asset();
$results = $assetModel->getAllWithUsers(['search' => $query]);

$out = [];
$basePath = isAdmin() ? '/views/admin/asset_detail.php' : '/views/user/myassets.php';

foreach (array_slice($results, 0, 8) as $r) {
    $out[] = [
        'name'     => $r['asset_name'],
        'code'     => $r['asset_code'],
        'category' => $r['category'],
        'status'   => $r['status'],
        'url'      => $basePath . '?id=' . $r['asset_id'],
    ];
}

echo json_encode(['results' => $out]);
