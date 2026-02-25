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

    // Import users from Excel (.xlsx) file
    public function importUsers($file) {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No file uploaded or upload error'];
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size exceeds 5MB limit'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            return ['success' => false, 'message' => 'Invalid file type. Only .xlsx files are allowed'];
        }

        require_once __DIR__ . '/../libs/SimpleXLSX.php';
        $xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name']);
        if (!$xlsx) {
            return ['success' => false, 'message' => 'Failed to parse Excel file: ' . \Shuchkin\SimpleXLSX::parseError()];
        }

        $rows = $xlsx->rows();
        if (count($rows) < 2) {
            return ['success' => false, 'message' => 'Excel file has no data rows (only header)'];
        }

        $created = 0;
        $errors = [];
        $validRoles = [ROLE_ADMIN, 'User'];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip empty rows
            if (empty(trim($row[0] ?? ''))) continue;

            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');
            $password = trim($row[2] ?? '');
            $role = trim($row[3] ?? 'User');
            $phone = trim($row[4] ?? '');
            $department = trim($row[5] ?? '');

            // Validate required fields
            if (empty($name)) {
                $errors[] = "Row " . ($i + 1) . ": Name is required";
                continue;
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row " . ($i + 1) . ": Valid email is required";
                continue;
            }
            if (empty($password) || strlen($password) < 8) {
                $errors[] = "Row " . ($i + 1) . ": Password must be at least 8 characters";
                continue;
            }

            // Normalize role
            if (!in_array($role, $validRoles)) {
                $role = 'User';
            }

            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ];
            if ($phone) $data['phone'] = $phone;
            if ($department) $data['department'] = $department;

            try {
                $result = $this->createUser($data);
                if ($result['success']) {
                    $created++;
                } else {
                    $errors[] = "Row " . ($i + 1) . ": " . ($result['message'] ?? 'Failed to create');
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (strpos($msg, 'Duplicate entry') !== false) {
                    $errors[] = "Row " . ($i + 1) . ": Email '$email' already exists";
                } else {
                    $errors[] = "Row " . ($i + 1) . ": " . $msg;
                }
            }
        }

        return [
            'success' => true,
            'created' => $created,
            'errors' => $errors
        ];
    }
}
?>
