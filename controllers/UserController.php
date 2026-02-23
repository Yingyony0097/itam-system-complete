<?php
/**
 * ITAM System - User Controller
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Asset.php';

class UserController {
    private $userModel;
    private $assetModel;

    public function __construct() {
        $this->userModel = new User();
        $this->assetModel = new Asset();
    }

    // Get all active users
    public function getUsers($search = null) {
        $search = trim((string)$search);
        if ($search !== '') {
            return $this->userModel->searchActiveUsers($search);
        }
        return $this->userModel->getActiveUsers();
    }

    // Get single user
    public function getUser($id) {
        return $this->userModel->find($id);
    }

    // Create user
    public function createUser($data) {
        // Check if email exists
        if ($this->userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Validate password
        if (strlen($data['password']) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        $id = $this->userModel->createUser($data);
        return $id
            ? ['success' => true, 'id' => $id, 'message' => 'User created successfully']
            : ['success' => false, 'message' => 'Failed to create user'];
    }

    // Update user
    public function updateUser($id, $data) {
        // Check if email exists for other users
        if (!empty($data['email'])) {
            $existing = $this->userModel->findByEmail($data['email']);
            if ($existing && $existing['user_id'] != $id) {
                return ['success' => false, 'message' => 'Email already in use by another user'];
            }
        }

        // If password is provided, validate and hash it
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                return ['success' => false, 'message' => 'Password must be at least 8 characters'];
            }
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }

        $success = $this->userModel->update($id, $data);
        return $success
            ? ['success' => true, 'message' => 'User updated successfully']
            : ['success' => false, 'message' => 'Failed to update user'];
    }

    // Deactivate user
    public function deactivateUser($id) {
        // Check if user has assets
        if ($this->userModel->hasAssets($id)) {
            return ['success' => false, 'message' => 'Cannot deactivate user with assigned assets'];
        }

        // Prevent self-deactivation
        if ($id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'You cannot deactivate your own account'];
        }

        $success = $this->userModel->deactivate($id);
        return $success ? ['success' => true, 'message' => 'User deactivated successfully'] : ['success' => false, 'message' => 'Failed to deactivate user'];
    }

    // Get user's assigned assets
    public function getUserAssets($userId) {
        return $this->assetModel->getByUser($userId);
    }

    // Get active users for dropdown
    public function getActiveUsersForSelect() {
        return $this->userModel->getActiveUsers();
    }
}
?>
