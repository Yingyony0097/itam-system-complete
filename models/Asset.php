<?php
/**
 * ITAM System - Asset Model
 */

require_once __DIR__ . '/Model.php';

class Asset extends Model {
    protected $table = 'assets';
    protected $primaryKey = 'asset_id';

    // Get all assets with assigned user info
    public function getAllWithUsers($filters = []) {
        $sql = "
            SELECT a.*, u.name as assigned_user_name, u.email as assigned_user_email 
            FROM {$this->table} a 
            LEFT JOIN users u ON a.assigned_to = u.user_id 
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND a.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (a.asset_name LIKE ? OR a.serial_number LIKE ? OR a.asset_code LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY a.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get assets by status
    public function getByStatus($status) {
        return $this->findBy('status', $status);
    }

    // Get assets assigned to user
    public function getByUser($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE assigned_to = ? ORDER BY assigned_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // Get available assets
    public function getAvailable() {
        return $this->getByStatus(STATUS_AVAILABLE);
    }

    // Get assets in use
    public function getInUse() {
        return $this->getByStatus(STATUS_IN_USE);
    }

    // Check out asset
    public function checkOut($assetId, $userId, $notes = '') {
        $this->db->beginTransaction();

        try {
            // Update asset
            $sql = "UPDATE {$this->table} SET status = ?, assigned_to = ?, assigned_date = CURDATE() WHERE asset_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([STATUS_IN_USE, $userId, $assetId]);

            // Create check log
            $sql = "INSERT INTO check_logs (asset_id, user_id, action_type, action_date, notes, performed_by) VALUES (?, ?, 'Check Out', NOW(), ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$assetId, $userId, $notes, $_SESSION['user_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Check out error: " . $e->getMessage());
            return false;
        }
    }

    // Check in asset
    public function checkIn($assetId, $notes = '') {
        $asset = $this->find($assetId);
        if (!$asset) return false;

        $previousUser = $asset['assigned_to'];

        $this->db->beginTransaction();

        try {
            // Update asset
            $sql = "UPDATE {$this->table} SET status = ?, assigned_to = NULL, assigned_date = NULL WHERE asset_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([STATUS_AVAILABLE, $assetId]);

            // Create check log
            $sql = "INSERT INTO check_logs (asset_id, user_id, action_type, action_date, notes, performed_by) VALUES (?, ?, 'Check In', NOW(), ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$assetId, $previousUser, $notes, $_SESSION['user_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Check in error: " . $e->getMessage());
            return false;
        }
    }

    // Generate unique asset code
    public function generateAssetCode() {
        $sql = "SELECT MAX(asset_id) as max_id FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $nextId = ($result['max_id'] ?? 0) + 1;
        return 'AST-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }

    // Get dashboard statistics
    public function getStatistics() {
        $stats = [
            'total' => $this->count(),
            'available' => $this->count("status = ?", [STATUS_AVAILABLE]),
            'in_use' => $this->count("status = ?", [STATUS_IN_USE]),
        ];

        // Calculate total value
        $sql = "SELECT SUM(purchase_price) as total_value FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $stats['total_value'] = $result['total_value'] ?? 0;

        return $stats;
    }

    // Get assets by category
    public function getByCategory() {
        $sql = "SELECT category, COUNT(*) as asset_count FROM {$this->table} WHERE category IS NOT NULL AND category != '' GROUP BY category ORDER BY asset_count DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Get daily total asset count for the last 7 days
    public function getDailyTrend($days = 7) {
        $sql = "
            SELECT DATE(d.day) as day_date, COUNT(a.asset_id) as total
            FROM (
                SELECT CURDATE() - INTERVAL n DAY as day
                FROM (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) nums
            ) d
            LEFT JOIN {$this->table} a ON DATE(a.created_at) <= d.day
            GROUP BY d.day
            ORDER BY d.day ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Get daily available asset count for the last 7 days
    public function getAvailableTrend($days = 7) {
        $sql = "
            SELECT DATE(d.day) as day_date,
                   (SELECT COUNT(*) FROM {$this->table} WHERE status = ? AND DATE(created_at) <= d.day) as available
            FROM (
                SELECT CURDATE() - INTERVAL n DAY as day
                FROM (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) nums
            ) d
            ORDER BY d.day ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([STATUS_AVAILABLE]);
        return $stmt->fetchAll();
    }
}
?>
