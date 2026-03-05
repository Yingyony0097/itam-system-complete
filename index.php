<?php
/**
 * ລະບົບ ITAM - Front Controller
 * ຈຸດເຂົ້າຫຼັກຂອງທຸກ request
 */

require_once __DIR__ . '/config/config.php';

// ການ routing ແບບງ່າຍ
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/itam-system', '', $uri);

// ນຳທາງໄປໜ້າທີ່ເໝາະສົມ
switch ($uri) {
    case '/':
    case '/login':
        redirect('/views/auth/login.php');
        break;

    case '/index.php':
        // Prevent recursive self-include when index.php is requested directly
        redirect('/');
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
        // ກວດສອບວ່າໄຟລ໌ມີຢູ່ບໍ່
        $file = __DIR__ . $uri;
        $realFile = realpath($file);
        $rootPath = realpath(__DIR__);

        if (
            $realFile !== false &&
            $rootPath !== false &&
            is_file($realFile) &&
            strpos($realFile, $rootPath . DIRECTORY_SEPARATOR) === 0 &&
            $realFile !== __FILE__
        ) {
            require $realFile;
        } else {
            // ໜ້າ 404
            http_response_code(404);
            require __DIR__ . '/views/errors/404.php';
        }
}
