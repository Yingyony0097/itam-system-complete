<?php
/**
 * ITAM System - Dashboard Controller
 */

require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/CheckLog.php';
require_once __DIR__ . '/../models/User.php';

class DashboardController {
    private $assetModel;
    private $checkLogModel;
    private $userModel;

    public function __construct() {
        $this->assetModel = new Asset();
        $this->checkLogModel = new CheckLog();
        $this->userModel = new User();
    }

    // Get admin dashboard data
    public function getAdminDashboard() {
        $stats = $this->assetModel->getStatistics();
        $recentActivity = $this->checkLogModel->getRecentActivity(5);
        $categories = $this->assetModel->getByCategory();

        return [
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'categories' => $categories
        ];
    }

    // Get user dashboard data
    public function getUserDashboard($userId) {
        $myAssets = $this->assetModel->getByUser($userId);
        $myHistory = $this->checkLogModel->getByUser($userId);

        return [
            'my_assets' => $myAssets,
            'my_history' => $myHistory
        ];
    }
}
?>
