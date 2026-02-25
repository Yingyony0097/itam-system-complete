<?php
/**
 * ITAM System - Asset Controller
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

    // Get all assets with filtering
    public function getAssets($filters = []) {
        return $this->assetModel->getAllWithUsers($filters);
    }

    // Get single asset
    public function getAsset($id) {
        return $this->assetModel->find($id);
    }

    // Create asset
    public function createAsset($data) {
        // Generate asset code
        $data['asset_code'] = $this->assetModel->generateAssetCode();

        // Set default status
        if (empty($data['status'])) {
            $data['status'] = STATUS_AVAILABLE;
        }

        $id = $this->assetModel->create($data);
        return $id ? ['success' => true, 'id' => $id, 'asset_code' => $data['asset_code']] : ['success' => false, 'message' => 'Failed to create asset'];
    }

    // Update asset
    public function updateAsset($id, $data) {
        // Prevent changing asset_code
        unset($data['asset_code']);

        // If status is being changed to Available, clear assignment
        if (isset($data['status']) && $data['status'] === STATUS_AVAILABLE) {
            $data['assigned_to'] = null;
            $data['assigned_date'] = null;
        }

        $success = $this->assetModel->update($id, $data);
        return $success ? ['success' => true, 'message' => 'Asset updated successfully'] : ['success' => false, 'message' => 'Failed to update asset'];
    }

    // Delete asset (with cascade delete for logs)
    public function deleteAsset($id) {
        // Check if asset exists
        $asset = $this->assetModel->find($id);
        if (!$asset) {
            return ['success' => false, 'message' => 'Asset not found'];
        }

        // Delete photo file from disk
        if (!empty($asset['photo_url'])) {
            $this->deletePhoto($asset['photo_url']);
        }

        // Delete check logs first (cascade)
        $this->checkLogModel->deleteByAsset($id);

        // Delete asset
        $success = $this->assetModel->delete($id);
        return $success ? ['success' => true, 'message' => 'Asset deleted successfully'] : ['success' => false, 'message' => 'Failed to delete asset'];
    }

    // Upload asset photo
    public function uploadPhoto($file) {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No file uploaded or upload error'];
        }

        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size exceeds 5MB limit'];
        }

        // Validate extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS)];
        }

        // Generate unique filename
        $filename = 'asset_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../public/uploads/assets/';
        $destPath = $uploadDir . $filename;

        // Ensure directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'message' => 'Failed to save uploaded file'];
        }

        return ['success' => true, 'photo_url' => '/public/uploads/assets/' . $filename];
    }

    // Delete photo file from disk
    public function deletePhoto($photoUrl) {
        if (empty($photoUrl)) return;
        $filePath = __DIR__ . '/..' . $photoUrl;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Check out asset
    public function checkOut($assetId, $userId, $notes = '') {
        // Validate asset is available
        $asset = $this->assetModel->find($assetId);
        if (!$asset || $asset['status'] !== STATUS_AVAILABLE) {
            return ['success' => false, 'message' => 'Asset is not available for check out'];
        }

        $success = $this->assetModel->checkOut($assetId, $userId, $notes);
        return $success ? ['success' => true, 'message' => 'Asset checked out successfully'] : ['success' => false, 'message' => 'Failed to check out asset'];
    }

    // Check in asset
    public function checkIn($assetId, $notes = '') {
        // Validate asset is in use
        $asset = $this->assetModel->find($assetId);
        if (!$asset || $asset['status'] !== STATUS_IN_USE) {
            return ['success' => false, 'message' => 'Asset is not checked out'];
        }

        $success = $this->assetModel->checkIn($assetId, $notes);
        return $success ? ['success' => true, 'message' => 'Asset checked in successfully'] : ['success' => false, 'message' => 'Failed to check in asset'];
    }

    // Get available assets for dropdown
    public function getAvailableAssets() {
        return $this->assetModel->getAvailable();
    }

    // Get assets in use for dropdown
    public function getInUseAssets() {
        return $this->assetModel->getInUse();
    }

    // Get statistics
    public function getStatistics() {
        return $this->assetModel->getStatistics();
    }

    // Get category breakdown
    public function getCategories() {
        return $this->assetModel->getByCategory();
    }
}
?>
