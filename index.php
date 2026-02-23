<?php
/**
 * ITAM System - Front Controller
 * Entry point for all requests
 */

require_once __DIR__ . '/config/config.php';

// Simple routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/itam-system', '', $uri);

// Route to appropriate page
switch ($uri) {
    case '/':
    case '/login':
        redirect('/views/auth/login.php');
        break;

    case '/dashboard':
        redirect(isAdmin() ? '/views/admin/dashboard.php' : '/views/user/dashboard.php');
        break;

    case '/assets':
        redirect('/views/admin/assets.php');
        break;

    case '/logout':
        redirect('/views/auth/logout.php');
        break;

    default:
        // Check if file exists
        $file = __DIR__ . $uri;
        if (file_exists($file) && is_file($file)) {
            require $file;
        } else {
            // 404 page
            http_response_code(404);
            require __DIR__ . '/views/errors/404.php';
        }
}
