<?php
/**
 * ລະບົບ ITAM - Controller ຊັບສິນ
 */

require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/CheckLog.php';

class AssetController {
    private $assetModel;
    private $checkLogModel;

    public function __construct() {
        $this->assetModel = new Asset();
        $this->checkLogModel = new CheckLog();
    }

    // ດຶງຊັບສິນທັງໝົດພ້ອມການກັ່ນຕອງ
    public function getAssets($filters = []) {
        return $this->assetModel->getAllWithUsers($filters);
    }

    // ດຶງຊັບສິນດຽວ
    public function getAsset($id) {
        return $this->assetModel->find($id);
    }

    // ສ້າງຊັບສິນໃໝ່
    public function createAsset($data) {
        // ສ້າງລະຫັດຊັບສິນ
        $data['asset_code'] = $this->assetModel->generateAssetCode();

        // ຕັ້ງສະຖານະເລີ່ມຕົ້ນ
        if (empty($data['status'])) {
            $data['status'] = STATUS_AVAILABLE;
        }

        $id = $this->assetModel->create($data);
        return $id ? ['success' => true, 'id' => $id, 'asset_code' => $data['asset_code']] : ['success' => false, 'message' => 'Failed to create asset'];
    }

    // ອັບເດດຊັບສິນ
    public function updateAsset($id, $data) {
        // ປ້ອງກັນການປ່ຽນລະຫັດຊັບສິນ
        unset($data['asset_code']);

        // ຖ້າສະຖານະປ່ຽນເປັນ Available, ລ້າງການມອບໝາຍ
        if (isset($data['status']) && $data['status'] === STATUS_AVAILABLE) {
            $data['assigned_to'] = null;
            $data['assigned_date'] = null;
        }

        $success = $this->assetModel->update($id, $data);
        return $success ? ['success' => true, 'message' => 'Asset updated successfully'] : ['success' => false, 'message' => 'Failed to update asset'];
    }

    // ລຶບຊັບສິນ (ລຶບບັນທຶກແບບ cascade)
    public function deleteAsset($id) {
        // ກວດສອບວ່າຊັບສິນມີບໍ່
        $asset = $this->assetModel->find($id);
        if (!$asset) {
            return ['success' => false, 'message' => 'Asset not found'];
        }

        // ລຶບໄຟລ໌ຮູບພາບ
        if (!empty($asset['photo_url'])) {
            $this->deletePhoto($asset['photo_url']);
        }

        // ລຶບບັນທຶກກ່ອນ (cascade)
        $this->checkLogModel->deleteByAsset($id);

        // ລຶບຊັບສິນ
        $success = $this->assetModel->delete($id);
        return $success ? ['success' => true, 'message' => 'Asset deleted successfully'] : ['success' => false, 'message' => 'Failed to delete asset'];
    }

    // ອັບໂຫຼດຮູບຊັບສິນ
    public function uploadPhoto($file) {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No file uploaded or upload error'];
        }

        // ກວດສອບຂະໜາດໄຟລ໌
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size exceeds 5MB limit'];
        }

        // ກວດສອບນາມສະກຸນ
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS)];
        }

        // ສ້າງຊື່ໄຟລ໌ທີ່ບໍ່ຊ້ຳກັນ
        $filename = 'asset_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../public/uploads/assets/';
        $destPath = $uploadDir . $filename;

        // ກວດສອບໂຟເດີ
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'message' => 'Failed to save uploaded file'];
        }

        return ['success' => true, 'photo_url' => '/public/uploads/assets/' . $filename];
    }

    // ລຶບໄຟລ໌ຮູບພາບ
    public function deletePhoto($photoUrl) {
        if (empty($photoUrl)) return;
        $filePath = __DIR__ . '/..' . $photoUrl;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // ເບີກຊັບສິນ
    public function checkOut($assetId, $userId, $notes = '') {
        // ກວດສອບວ່າຊັບສິນພ້ອມໃຊ້ບໍ່
        $asset = $this->assetModel->find($assetId);
        if (!$asset || $asset['status'] !== STATUS_AVAILABLE) {
            return ['success' => false, 'message' => 'Asset is not available for check out'];
        }

        $success = $this->assetModel->checkOut($assetId, $userId, $notes);
        return $success ? ['success' => true, 'message' => 'Asset checked out successfully'] : ['success' => false, 'message' => 'Failed to check out asset'];
    }

    // ຄືນຊັບສິນ
    public function checkIn($assetId, $notes = '') {
        // ກວດສອບວ່າຊັບສິນຖືກເບີກຢູ່ບໍ່
        $asset = $this->assetModel->find($assetId);
        if (!$asset || $asset['status'] !== STATUS_IN_USE) {
            return ['success' => false, 'message' => 'Asset is not checked out'];
        }

        $success = $this->assetModel->checkIn($assetId, $notes);
        return $success ? ['success' => true, 'message' => 'Asset checked in successfully'] : ['success' => false, 'message' => 'Failed to check in asset'];
    }

    // ດຶງຊັບສິນພ້ອมໃຊ້ສຳລັບ dropdown
    public function getAvailableAssets() {
        return $this->assetModel->getAvailable();
    }

    // ດຶງຊັບສິນກຳລັງໃຊ້ສຳລັບ dropdown
    public function getInUseAssets() {
        return $this->assetModel->getInUse();
    }

    // ດຶງສະຖິຕິ
    public function getStatistics() {
        return $this->assetModel->getStatistics();
    }

    // ດຶງຂໍ້ມູນແຍກຕາມປະເພດ
    public function getCategories() {
        return $this->assetModel->getByCategory();
    }

    // ນຳເຂົ້າຊັບສິນຈາກໄຟລ໌ Excel (.xlsx)
    public function importAssets($file) {
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
        $validCategories = ['Computer', 'Phone', 'Printer', 'Accessory'];
        $validStatuses = [STATUS_AVAILABLE, STATUS_IN_USE];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // ຂ້າມແຖວເປົ່າ
            if (empty(trim($row[0] ?? ''))) continue;

            $assetName = trim($row[0] ?? '');
            $category = trim($row[1] ?? '');
            $serialNumber = trim($row[2] ?? '');
            $brand = trim($row[3] ?? '');
            $model = trim($row[4] ?? '');
            $purchaseDate = trim($row[5] ?? '');
            $purchasePrice = trim($row[6] ?? '0');
            $status = trim($row[7] ?? STATUS_AVAILABLE);

            // ກວດສອບຂໍ້ມູນທີ່ຈຳເປັນ
            if (empty($assetName)) {
                $errors[] = "Row " . ($i + 1) . ": Asset Name is required";
                continue;
            }
            if (empty($category) || !in_array($category, $validCategories)) {
                $errors[] = "Row " . ($i + 1) . ": Invalid category '$category'. Must be: " . implode(', ', $validCategories);
                continue;
            }

            // ປັບສະຖານະ
            if (!in_array($status, $validStatuses)) {
                $status = STATUS_AVAILABLE;
            }

            $data = [
                'asset_name' => $assetName,
                'category' => $category,
                'serial_number' => $serialNumber ?: null,
                'brand' => $brand ?: null,
                'model' => $model ?: null,
                'purchase_date' => $purchaseDate ?: null,
                'purchase_price' => (float)$purchasePrice,
                'status' => $status
            ];

            try {
                $result = $this->createAsset($data);
                if ($result['success']) {
                    $created++;
                } else {
                    $errors[] = "Row " . ($i + 1) . ": " . ($result['message'] ?? 'Failed to create');
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (strpos($msg, 'Duplicate entry') !== false && strpos($msg, 'serial_number') !== false) {
                    $errors[] = "Row " . ($i + 1) . ": Duplicate serial number '$serialNumber'";
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
