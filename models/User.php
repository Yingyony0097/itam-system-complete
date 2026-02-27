<?php
/**
 * ລະບົບ ITAM - Model ຜູ້ໃຊ້
 */

require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    // ຊອກຫາຜູ້ໃຊ້ຕາມອີເມວ
    public function findByEmail($email) {
        return $this->findOneBy('email', $email);
    }

    // ຢືນຢັນຕົວຕົນຜູ້ໃຊ້
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // ດຶງຜູ້ໃຊ້ທີ່ໃຊ້ງານຢູ່
    public function getActiveUsers() {
        return $this->findBy('is_active', 1);
    }

    // ຄົ້ນຫາຜູ້ໃຊ້ທີ່ໃຊ້ງານຢູ່ຕາມຊື່/ອີເມວ
    public function searchActiveUsers($search) {
        $search = trim((string)$search);
        if ($search === '') {
            return $this->getActiveUsers();
        }

        $like = '%' . $search . '%';
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 AND (name LIKE ? OR email LIKE ?) ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    // ດຶງຜູ້ໃຊ້ທັງໝົດ (ໃຊ້ງານ ແລະ ບໍ່ໃຊ້ງານ)
    public function getAllUsers() {
        $sql = "SELECT * FROM {$this->table} ORDER BY is_active DESC, name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ຄົ້ນຫາຜູ້ໃຊ້ທັງໝົດຕາມຊື່/ອີເມວ
    public function searchAllUsers($search) {
        $search = trim((string)$search);
        if ($search === '') {
            return $this->getAllUsers();
        }

        $like = '%' . $search . '%';
        $sql = "SELECT * FROM {$this->table} WHERE (name LIKE ? OR email LIKE ?) ORDER BY is_active DESC, name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    // ເປີດການໃຊ້ງານຜູ້ໃຊ້ຄືນ
    public function reactivate($userId) {
        return $this->update($userId, ['is_active' => 1]);
    }

    // ສ້າງຜູ້ໃຊ້ໃໝ່ພ້ອມເຂົ້າລະຫັດລະຫັດຜ່ານ
    public function createUser($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->create($data);
    }

    // ອັບເດດລະຫັດຜ່ານຜູ້ໃຊ້
    public function updatePassword($userId, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($userId, ['password' => $hashed]);
    }

    // ປິດການໃຊ້ງານຜູ້ໃຊ້ (ລຶບແບບ soft delete)
    public function deactivate($userId) {
        return $this->update($userId, ['is_active' => 0]);
    }

    // ກວດສອບວ່າຜູ້ໃຊ້ມີຊັບສິນທີ່ມອບໝາຍບໍ່
    public function hasAssets($userId) {
        $sql = "SELECT COUNT(*) FROM assets WHERE assigned_to = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }
}
?>
