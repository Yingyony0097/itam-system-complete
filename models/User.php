<?php
/**
 * ITAM System - User Model
 */

require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    // Find user by email
    public function findByEmail($email) {
        return $this->findOneBy('email', $email);
    }

    // Authenticate user
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Get active users
    public function getActiveUsers() {
        return $this->findBy('is_active', 1);
    }

    // Search active users by name/email
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

    // Create new user with hashed password
    public function createUser($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->create($data);
    }

    // Update user password
    public function updatePassword($userId, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($userId, ['password' => $hashed]);
    }

    // Deactivate user (soft delete)
    public function deactivate($userId) {
        return $this->update($userId, ['is_active' => 0]);
    }

    // Check if user has assets assigned
    public function hasAssets($userId) {
        $sql = "SELECT COUNT(*) FROM assets WHERE assigned_to = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }
}
?>
