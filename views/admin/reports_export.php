<?php
/**
 * ITAM System - Reports Export
 *
 * Generates printable HTML or CSV (Excel-friendly) reports for assets,
 * users, asset value summary, and activity logs.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Asset.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/CheckLog.php';

requireAdmin();

$type = $_GET['type'] ?? 'assets';
$format = $_GET['format'] ?? 'csv';
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$actionFilter = $_GET['action'] ?? null; // checkin/checkout filter

$assetModel = new Asset();
$userModel = new User();
$logModel = new CheckLog();

function reportActionText($actionType) {
    if ($actionType === 'Check Out') {
        return tr('Check Out');
    }
    if ($actionType === 'Check In') {
        return tr('Check In');
    }
    return tr((string)$actionType);
}

function reportFilterActionText($actionFilter) {
    if ($actionFilter === 'checkout') {
        return tr('Check Out');
    }
    if ($actionFilter === 'checkin') {
        return tr('Check In');
    }
    return tr('All');
}

function sendCsv($filename, $headers, $rows) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

function printHtml($title, $headers, $rows, $metaLines = []) {
    $printFont = currentLang() === LANG_LO
        ? '"Noto Sans Lao","Phetsarath OT","Phetsarath",Arial,sans-serif'
        : 'Arial,sans-serif';
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo e(currentLang()); ?>">
    <head>
        <meta charset="UTF-8">
        <title><?php echo e($title); ?></title>
        <style>
            body { font-family: <?php echo $printFont; ?>; margin: 24px; }
            h1 { margin-bottom: 4px; }
            .meta { margin: 0; color: #555; font-size: 13px; }
            table { border-collapse: collapse; width: 100%; margin-top: 16px; }
            th, td { border: 1px solid #ccc; padding: 8px; font-size: 13px; }
            th { background: #f3f4f6; text-align: left; }
            caption { text-align: left; margin-bottom: 8px; font-weight: bold; }
        </style>
    </head>
    <body onload="window.print()">
        <h1><?php echo e($title); ?></h1>
        <?php if (!empty($metaLines)): ?>
            <?php foreach ($metaLines as $line): ?>
                <p class="meta"><?php echo e($line); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <table>
            <thead>
            <tr>
                <?php foreach ($headers as $h): ?>
                    <th><?php echo e($h); ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?php echo e($cell); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

$metaLines = [];
switch ($type) {
    case 'assets':
        $assets = $assetModel->getAllWithUsers();
        $headers = [tr('Asset Code'), tr('Name'), tr('Category'), tr('Serial Number'), tr('Status'), tr('Assigned To'), tr('Purchase Date'), tr('Price')];
        $rows = [];
        foreach ($assets as $a) {
            $rows[] = [
                $a['asset_code'],
                $a['asset_name'],
                tr((string)$a['category']),
                $a['serial_number'],
                tr((string)$a['status']),
                $a['assigned_user_name'] ?: '-',
                $a['purchase_date'],
                number_format($a['purchase_price'], 2)
            ];
        }
        $title = tr('All Assets Report');
        break;

    case 'users':
        $users = $userModel->all('name');
        $headers = [tr('Name'), tr('Email'), tr('Role'), tr('Active'), tr('Assets Assigned')];
        $rows = [];
        foreach ($users as $u) {
            $sql = "SELECT COUNT(*) FROM assets WHERE assigned_to = ?";
            $stmt = Database::getInstance()->getConnection()->prepare($sql);
            $stmt->execute([$u['user_id']]);
            $countAssets = $stmt->fetchColumn();
            $rows[] = [
                $u['name'],
                $u['email'],
                tr((string)$u['role']),
                $u['is_active'] ? tr('Yes') : tr('No'),
                $countAssets
            ];
        }
        $title = tr('User Assets Report');
        break;

    case 'value':
        $assets = $assetModel->all('category');
        $byCategory = [];
        foreach ($assets as $a) {
            if (!isset($byCategory[$a['category']])) {
                $byCategory[$a['category']] = ['count' => 0, 'value' => 0];
            }
            $byCategory[$a['category']]['count']++;
                $byCategory[$a['category']]['value'] += (float)$a['purchase_price'];
        }
        $headers = [tr('Category'), tr('Asset Count'), tr('Total Value ($)')];
        $rows = [];
        foreach ($byCategory as $cat => $data) {
            $rows[] = [
                tr((string)$cat),
                $data['count'],
                number_format($data['value'], 2)
            ];
        }
        $title = tr('Asset Value Report');
        break;

    case 'activity':
    case 'custom':
        $sql = "
            SELECT cl.*, a.asset_name, a.asset_code, u.name AS user_name, p.name AS performed_by_name
            FROM check_logs cl
            JOIN assets a ON cl.asset_id = a.asset_id
            JOIN users u ON cl.user_id = u.user_id
            JOIN users p ON cl.performed_by = p.user_id
            WHERE 1=1
        ";
        $params = [];
        if ($from) {
            $sql .= " AND DATE(cl.action_date) >= ?";
            $params[] = $from;
        }
        if ($to) {
            $sql .= " AND DATE(cl.action_date) <= ?";
            $params[] = $to;
        }
        if ($actionFilter === 'checkout') {
            $sql .= " AND cl.action_type = 'Check Out'";
        } elseif ($actionFilter === 'checkin') {
            $sql .= " AND cl.action_type = 'Check In'";
        }
        $sql .= " ORDER BY cl.action_date DESC";

        $stmt = Database::getInstance()->getConnection()->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        $headers = [tr('Date'), tr('Action'), tr('Asset'), tr('User'), tr('Performed By'), tr('Notes')];
        $rows = [];
        foreach ($logs as $l) {
            $rows[] = [
                $l['action_date'],
                reportActionText((string)$l['action_type']),
                $l['asset_code'] . ' - ' . $l['asset_name'],
                $l['user_name'],
                $l['performed_by_name'],
                $l['notes']
            ];
        }
        $title = tr('Activity Log Report');
        if ($from || $to || $actionFilter) {
            $metaLines[] = tr('From') . ': ' . ($from ?: tr('Any'));
            $metaLines[] = tr('To') . ': ' . ($to ?: tr('Any'));
            $metaLines[] = tr('Action') . ': ' . reportFilterActionText($actionFilter);
        }
        break;

    default:
        http_response_code(400);
        echo e(tr('Invalid report type.'));
        exit;
}

if ($format === 'csv' || $format === 'excel') {
    $filename = 'report_' . preg_replace('/[^a-z0-9_]+/i', '_', strtolower((string)$type)) . '.csv';
    sendCsv($filename, $headers, $rows);
} elseif ($format === 'print') {
    printHtml($title, $headers, $rows, $metaLines);
} else {
    http_response_code(400);
    echo e(tr('Unsupported format.'));
}
