<?php
/**
 * ITAM System - Header Layout
 */
require_once __DIR__ . '/../../config/config.php';

$styleFile = __DIR__ . '/../../public/assets/css/style.css';
$styleVersion = file_exists($styleFile) ? (string)filemtime($styleFile) : APP_VERSION;
$faviconFile = __DIR__ . '/../../public/favicon.ico';
$faviconHref = '/public/favicon.ico';

if (is_dir($faviconFile) && file_exists($faviconFile . '/favicon.ico')) {
    $faviconFile = $faviconFile . '/favicon.ico';
    $faviconHref = '/public/favicon.ico/favicon.ico';
}

$faviconVersion = file_exists($faviconFile) ? (string)filemtime($faviconFile) : APP_VERSION;
?>
<!DOCTYPE html>
<html lang="<?php echo e(currentLang()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e(tr($pageTitle)) . ' - ' : ''; ?><?php echo e(tr('ITAM System')); ?></title>
    <meta name="description" content="IT Asset Management System for P-line Company">
    <link rel="icon" type="image/x-icon" href="<?php echo e($faviconHref); ?>?v=<?php echo e($faviconVersion); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Noto+Sans+Lao:wght@100..900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/assets/css/style.css?v=<?php echo e($styleVersion); ?>">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        window.ITAM_LANG = <?php echo json_encode(currentLang(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.ITAM_TRANSLATIONS = <?php echo json_encode(clientTextMap(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
</head>
<body>
