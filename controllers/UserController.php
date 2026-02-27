<?php
/**
 * ລະບົບ ITAM - Controller ຜູ້ໃຊ້
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

    // ດຶງຜູ້ໃຊ້ທັງໝົດ (ໃຊ້ງານ ແລະ ບໍ່ໃຊ້ງານ) ສຳລັບການຈັດການ
    public function getUsers($search = null) {
        $search = trim((string)$search);
        if ($search !== '') {
            return $this->userModel->searchAllUsers($search);
        }
        return $this->userModel->getAllUsers();
    }

    // ດຶງຜູ້ໃຊ້ດຽວ
    public function getUser($id) {
        return $this->userModel->find($id);
    }

    // ສ້າງຜູ້ໃຊ້ໃໝ່
    public function createUser($data) {
        // ກວດສອບອີເມວທີ່ມີຢູ່ແລ້ວ
        if ($this->userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // ກວດສອບລະຫັດຜ່ານ
        if (strlen($data['password']) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        $id = $this->userModel->createUser($data);
        return $id
            ? ['success' => true, 'id' => $id, 'message' => 'User created successfully']
            : ['success' => false, 'message' => 'Failed to create user'];
    }

    // ອັບເດດຜູ້ໃຊ້
    public function updateUser($id, $data) {
        // ກວດສອບອີເມວຊ້ຳກັບຜູ້ໃຊ້ອື່ນ
        if (!empty($data['email'])) {
            $existing = $this->userModel->findByEmail($data['email']);
            if ($existing && $existing['user_id'] != $id) {
                return ['success' => false, 'message' => 'Email already in use by another user'];
            }
        }

        // ຖ້າມີລະຫັດຜ່ານ, ກວດສອບ ແລະ ເຂົ້າລະຫັດ
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

    // ປິດການໃຊ້ງານຜູ້ໃຊ້
    public function deactivateUser($id) {
        // ກວດສອບວ່າຜູ້ໃຊ້ມີຊັບສິນບໍ່
        if ($this->userModel->hasAssets($id)) {
            return ['success' => false, 'message' => 'Cannot deactivate user with assigned assets'];
        }

        // ປ້ອງກັນການປິດບັນຊີຕົນເອງ
        if ($id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'You cannot deactivate your own account'];
        }

        $success = $this->userModel->deactivate($id);
        return $success ? ['success' => true, 'message' => 'User deactivated successfully'] : ['success' => false, 'message' => 'Failed to deactivate user'];
    }

    // ເປີດການໃຊ້ງານຜູ້ໃຊ້ຄືນ
    public function reactivateUser($id) {
        $success = $this->userModel->reactivate($id);
        return $success
            ? ['success' => true, 'message' => 'User reactivated successfully']
            : ['success' => false, 'message' => 'Failed to reactivate user'];
    }

    // ລຶບຜູ້ໃຊ້ຖາວອນ
    public function deleteUser($id) {
        // ປ້ອງກັນການລຶບບັນຊີຕົນເອງ
        if ($id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'You cannot delete your own account'];
        }

        // ກວດສອບວ່າຜູ້ໃຊ້ມີຊັບສິນບໍ່
        if ($this->userModel->hasAssets($id)) {
            return ['success' => false, 'message' => 'Cannot delete user with assigned assets'];
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // ລຶບຮູບໂປຣໄຟລ໌ຖ້າມີ
        if (!empty($user['photo_url'])) {
            $photoPath = __DIR__ . '/../public' . $user['photo_url'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        $success = $this->userModel->delete($id);
        return $success
            ? ['success' => true, 'message' => 'User deleted permanently']
            : ['success' => false, 'message' => 'Failed to delete user'];
    }

    // ດຶງຊັບສິນທີ່ມອບໝາຍໃຫ້ຜູ້ໃຊ້
    public function getUserAssets($userId) {
        return $this->assetModel->getByUser($userId);
    }

    // ດຶງຜູ້ໃຊ້ທີ່ໃຊ້ງານສຳລັບ dropdown
    public function getActiveUsersForSelect() {
        return $this->userModel->getActiveUsers();
    }

    // ນຳເຂົ້າຜູ້ໃຊ້ຈາກໄຟລ໌ Excel (.xlsx)
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

            // ຂ້າມແຖວເປົ່າ
            if (empty(trim($row[0] ?? ''))) continue;

            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');
            $password = trim($row[2] ?? '');
            $role = trim($row[3] ?? 'User');
            $phone = trim($row[4] ?? '');
            $department = trim($row[5] ?? '');

            // ກວດສອບຂໍ້ມູນທີ່ຈຳເປັນ
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

            // ປັບ role
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
