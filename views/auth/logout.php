<?php
/**
 * ລະບົບ ITAM - ອອກຈາກລະບົບ
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->logout();

redirect('/views/auth/login.php');
