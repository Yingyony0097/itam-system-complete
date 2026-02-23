<?php
/**
 * ITAM System - Authentication Controller
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Login process
    public function login($email, $password) {
        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Update last login (optional)
            // $this->userModel->update($user['user_id'], ['last_login' => date('Y-m-d H:i:s')]);

            return ['success' => true, 'role' => $user['role']];
        }

        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    // Logout process
    public function logout() {
        // Clear all session data
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();

        return true;
    }

    // Change password
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        // Update password
        if ($this->userModel->updatePassword($userId, $newPassword)) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update password'];
    }
}
?>
