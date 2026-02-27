<?php
/**
 * ລະບົບ ITAM - Controller ການຢືນຢັນຕົວຕົນ
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // ຂະບວນການເຂົ້າລະບົບ
    public function login($email, $password) {
        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            // ຕັ້ງຄ່າ session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_photo'] = $user['photo_url'] ?? null;

            return ['success' => true, 'role' => $user['role']];
        }

        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    // ຂະບວນການອອກຈາກລະບົບ
    public function logout() {
        // ລ້າງຂໍ້ມູນ session ທັງໝົດ
        $_SESSION = [];

        // ລຶບ cookie ຂອງ session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // ທຳລາຍ session
        session_destroy();

        return true;
    }

    // ປ່ຽນລະຫັດຜ່ານ
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // ກວດສອບລະຫັດຜ່ານປັດຈຸບັນ
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // ກວດສອບລະຫັດຜ່ານໃໝ່
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        // ອັບເດດລະຫັດຜ່ານ
        if ($this->userModel->updatePassword($userId, $newPassword)) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update password'];
    }
}
?>
