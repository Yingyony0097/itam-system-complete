<?php
/**
 * ITAM System - Authentication Controller
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';

class AuthController {
    private const LOGIN_MAX_ATTEMPTS = 5;
    private const LOGIN_WINDOW_SECONDS = 900;

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login($email, $password) {
        $rateLimit = $this->checkLoginRateLimit();
        if (!$rateLimit['allowed']) {
            return [
                'success' => false,
                'message' => 'Too many login attempts. Please try again in ' . $rateLimit['retry_after'] . ' seconds.'
            ];
        }

        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            $this->clearLoginFailures();
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_photo'] = $user['photo_url'] ?? null;

            return ['success' => true, 'role' => $user['role']];
        }

        $this->recordLoginFailure();
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    public function logout() {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();

        return true;
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        if ($this->userModel->updatePassword($userId, $newPassword)) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update password'];
    }

    private function getClientIp() {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($headers as $header) {
            if (empty($_SERVER[$header])) {
                continue;
            }

            $value = trim((string)$_SERVER[$header]);
            if ($header === 'HTTP_X_FORWARDED_FOR') {
                $parts = explode(',', $value);
                $value = trim((string)$parts[0]);
            }

            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }

        return 'unknown';
    }

    private function getRateLimitFilePath() {
        $baseDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'itam_login_rate_limit';

        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0700, true);
        }

        $key = hash('sha256', $this->getClientIp());
        return $baseDir . DIRECTORY_SEPARATOR . $key . '.json';
    }

    private function readLoginFailures() {
        $path = $this->getRateLimitFilePath();

        if (!is_file($path)) {
            return [];
        }

        $json = @file_get_contents($path);
        if ($json === false || $json === '') {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['failures']) || !is_array($data['failures'])) {
            return [];
        }

        $cutoff = time() - self::LOGIN_WINDOW_SECONDS;
        $failures = [];

        foreach ($data['failures'] as $failureTime) {
            $ts = (int)$failureTime;
            if ($ts >= $cutoff) {
                $failures[] = $ts;
            }
        }

        sort($failures, SORT_NUMERIC);
        return $failures;
    }

    private function writeLoginFailures($failures) {
        $path = $this->getRateLimitFilePath();

        if (empty($failures)) {
            if (is_file($path)) {
                @unlink($path);
            }
            return;
        }

        $payload = json_encode(['failures' => array_values($failures)], JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return;
        }

        @file_put_contents($path, $payload, LOCK_EX);
    }

    private function checkLoginRateLimit() {
        $failures = $this->readLoginFailures();
        $this->writeLoginFailures($failures);

        if (count($failures) < self::LOGIN_MAX_ATTEMPTS) {
            return ['allowed' => true, 'retry_after' => 0];
        }

        $oldest = (int)$failures[0];
        $retryAfter = self::LOGIN_WINDOW_SECONDS - (time() - $oldest);

        return [
            'allowed' => false,
            'retry_after' => max(1, (int)$retryAfter)
        ];
    }

    private function recordLoginFailure() {
        $failures = $this->readLoginFailures();
        $failures[] = time();
        $this->writeLoginFailures($failures);
    }

    private function clearLoginFailures() {
        $this->writeLoginFailures([]);
    }
}
?>
