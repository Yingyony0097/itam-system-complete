<?php
/**
 * ລະບົບ ITAM - Model ບັນທຶກການເບີກ/ຄືນ
 */

require_once __DIR__ . '/Model.php';

class CheckLog extends Model {
    protected $table = 'check_logs';
    protected $primaryKey = 'log_id';

    // ດຶງບັນທຶກທັງໝົດພ້ອມລາຍລະອຽດຊັບສິນ ແລະ ຜູ້ໃຊ້
    public function getAllWithDetails($limit = null) {
        $sql = "
            SELECT cl.*,
                   a.asset_name, a.asset_code,
                   u.name as user_name,
                   p.name as performed_by_name
            FROM {$this->table} cl
            JOIN assets a ON cl.asset_id = a.asset_id
            JOIN users u ON cl.user_id = u.user_id
            JOIN users p ON cl.performed_by = p.user_id
            ORDER BY cl.action_date DESC
        ";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // ດຶງບັນທຶກຕາມຊັບສິນ
    public function getByAsset($assetId) {
        $sql = "
            SELECT cl.*, u.name as user_name, p.name as performed_by_name
            FROM {$this->table} cl
            JOIN users u ON cl.user_id = u.user_id
            JOIN users p ON cl.performed_by = p.user_id
            WHERE cl.asset_id = ?
            ORDER BY cl.action_date DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assetId]);
        return $stmt->fetchAll();
    }

    // ດຶງບັນທຶກຕາມຜູ້ໃຊ້
    public function getByUser($userId) {
        $sql = "
            SELECT cl.*, a.asset_name, a.asset_code, p.name as performed_by_name
            FROM {$this->table} cl
            JOIN assets a ON cl.asset_id = a.asset_id
            JOIN users p ON cl.performed_by = p.user_id
            WHERE cl.user_id = ?
            ORDER BY cl.action_date DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // ດຶງກິດຈະກຳຫຼ້າສຸດສຳລັບແດຊບອດ
    public function getRecentActivity($limit = 5) {
        return $this->getAllWithDetails($limit);
    }

    // ລຶບບັນທຶກຕາມຊັບສິນ (ລຶບແບບ cascade)
    public function deleteByAsset($assetId) {
        $sql = "DELETE FROM {$this->table} WHERE asset_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$assetId]);
    }
}
?>
