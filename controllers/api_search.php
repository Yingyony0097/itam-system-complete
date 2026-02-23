<?php
/**
 * ITAM System - Global Search API Endpoint
 * Returns JSON results for the dashboard search bar.
 * Searches both assets and users.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/User.php';

requireAuth();

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$out = [];

// Search assets
$assetModel = new Asset();
$assetResults = $assetModel->getAllWithUsers(['search' => $query]);
$assetBasePath = isAdmin() ? '/views/admin/asset_detail.php' : '/views/user/myassets.php';

foreach (array_slice($assetResults, 0, 5) as $r) {
    $out[] = [
        'type'     => 'asset',
        'name'     => $r['asset_name'],
        'code'     => $r['asset_code'],
        'category' => $r['category'],
        'status'   => $r['status'],
        'url'      => $assetBasePath . '?id=' . $r['asset_id'],
    ];
}

// Search users (admin only)
if (isAdmin()) {
    $userModel = new User();
    $userResults = $userModel->searchActiveUsers($query);

    foreach (array_slice($userResults, 0, 3) as $u) {
        $out[] = [
            'type'  => 'user',
            'name'  => $u['name'],
            'email' => $u['email'],
            'role'  => $u['role'],
            'url'   => '/views/admin/users.php',
        ];
    }
}

echo json_encode(['results' => $out]);
