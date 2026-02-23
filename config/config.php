<?php
/**
 * ITAM System - Application Configuration
 */

// Session Configuration (2 hours)
define('SESSION_TIMEOUT', 7200 );
ini_set('session.gc_maxlifetime', (string) SESSION_TIMEOUT);
ini_set('session.cookie_lifetime', (string) SESSION_TIMEOUT);

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Force logout after 2 hours of inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    session_start();
}

$_SESSION['last_activity'] = time();

// Language Configuration
define('LANG_EN', 'en');
define('LANG_LO', 'lo');
define('DEFAULT_LANG', LANG_EN);
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';

function supportedLanguages() {
    return [LANG_EN, LANG_LO];
}

if ($currentPath === '/views/auth/login.php' && isset($_GET['lang'])) {
    $requestedLang = strtolower(trim((string)$_GET['lang']));

    if (in_array($requestedLang, supportedLanguages(), true)) {
        $_SESSION['lang'] = $requestedLang;
    }

    // Remove `lang` from URL after switching so links stay clean.
    $params = $_GET;
    unset($params['lang']);

    $query = http_build_query($params);
    $redirectUrl = $currentPath . ($query !== '' ? '?' . $query : '');
    header('Location: ' . $redirectUrl);
    exit();
}

if (!isset($_SESSION['lang']) || !in_array($_SESSION['lang'], supportedLanguages(), true)) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

// Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Vientiane');

// Application Constants
define('APP_NAME', 'ITAM System');
define('APP_VERSION', '1.0.1');
define('COMPANY_NAME', 'P-line Company');
define('COMPANY_LOCATION', 'Vientiane, Laos');

// Asset Status
define('STATUS_AVAILABLE', 'Available');
define('STATUS_IN_USE', 'In Use');

// User Roles
define('ROLE_ADMIN', 'Admin');
define('ROLE_USER', 'User');

// Pagination
define('ITEMS_PER_PAGE', 10);

// File Upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Helper function to generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper function to validate CSRF token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Helper function to sanitize output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Helper function to get current language
function currentLang() {
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

// Translation helper
function t($key) {
    static $translations = [
        LANG_EN => [
            'app.asset_management' => 'Asset Management',
            'common.language' => 'Language',
            'common.english' => 'English',
            'common.lao' => 'Lao',
            'nav.dashboard' => 'Dashboard',
            'nav.assets' => 'Assets',
            'nav.check_in_out' => 'Check In/Out',
            'nav.history' => 'History',
            'nav.users' => 'Users',
            'nav.my_assets' => 'My Assets',
            'nav.reports' => 'Reports',
            'nav.profile' => 'Profile',
            'nav.logout' => 'Logout',
            'auth.login' => 'Login',
            'auth.system_title' => 'IT Asset Management System',
            'auth.company_line' => 'P-line Company, Vientiane, Laos',
            'auth.email_address' => 'Email Address',
            'auth.password' => 'Password',
            'auth.remember_me' => 'Remember me',
            'auth.forgot_password_contact_admin' => 'Forgot password? Contact admin',
            'auth.sign_in' => 'Sign In',
            'auth.demo_accounts' => 'Demo Accounts',
            'auth.admin_demo' => 'Admin Demo',
            'auth.user_demo' => 'User Demo',
            'auth.login_required' => 'Please login to access this page.',
            'auth.access_denied_admin' => 'Access denied. Admin privileges required.'
        ],
        LANG_LO => [
            'app.asset_management' => 'ການຈັດການຊັບສິນ',
            'common.language' => 'ພາສາ',
            'common.english' => 'English',
            'common.lao' => 'ລາວ',
            'nav.dashboard' => 'ແດຊບອດ',
            'nav.assets' => 'ຊັບສິນ',
            'nav.check_in_out' => 'ເບີກ/ຄືນ',
            'nav.history' => 'ປະຫວັດ',
            'nav.users' => 'ຜູ້ໃຊ້',
            'nav.my_assets' => 'ຊັບສິນຂອງຂ້ອຍ',
            'nav.reports' => 'ລາຍງານ',
            'nav.profile' => 'ໂປຣໄຟລ໌',
            'nav.logout' => 'ອອກຈາກລະບົບ',
            'auth.login' => 'ເຂົ້າລະບົບ',
            'auth.system_title' => 'ລະບົບຈັດການຊັບສິນ IT',
            'auth.company_line' => 'ບໍລິສັດ P-line, ວຽງຈັນ, ລາວ',
            'auth.email_address' => 'ອີເມວ',
            'auth.password' => 'ລະຫັດຜ່ານ',
            'auth.remember_me' => 'ຈື່ຂ້ອຍໄວ້',
            'auth.forgot_password_contact_admin' => 'ລືມລະຫັດຜ່ານ? ຕິດຕໍ່ແອັດມິນ',
            'auth.sign_in' => 'ເຂົ້າລະບົບ',
            'auth.demo_accounts' => 'ບັນຊີທົດລອງ',
            'auth.admin_demo' => 'ແອັດມິນທົດລອງ',
            'auth.user_demo' => 'ຜູ້ໃຊ້ທົດລອງ',
            'auth.login_required' => 'ກະລຸນາເຂົ້າລະບົບກ່ອນ.',
            'auth.access_denied_admin' => 'ປະຕິເສດການເຂົ້າເຖິງ. ຕ້ອງມີສິດແອັດມິນ.'
        ]
    ];

    $lang = currentLang();
    return $translations[$lang][$key]
        ?? $translations[LANG_EN][$key]
        ?? $key;
}

// Generic UI text translation map (used for legacy hardcoded text across pages)
function uiTextMap() {
    static $map = [
        // Common / navigation
        'Dashboard' => 'ແດຊບອດ',
        'Admin Dashboard' => 'ແດຊບອດຜູ້ດູແລ',
        'My Dashboard' => 'ແດຊບອດຂອງຂ້ອຍ',
        'Assets' => 'ຊັບສິນ',
        'My Assets' => 'ຊັບສິນຂອງຂ້ອຍ',
        'Users' => 'ຜູ້ໃຊ້',
        'Reports' => 'ລາຍງານ',
        'Profile' => 'ໂປຣໄຟລ໌',
        'Logout' => 'ອອກຈາກລະບົບ',
        'History' => 'ປະຫວັດ',
        'Check In/Out' => 'ເບີກ/ຄືນ',
        'Check In / Check Out' => 'ເບີກ / ຄືນ',
        'Asset Management' => 'ການຈັດການຊັບສິນ',
        'IT Asset Management System' => 'ລະບົບຈັດການຊັບສິນ IT',
        'P-line Company, Vientiane, Laos' => 'ບໍລິສັດ P-line, ວຽງຈັນ, ລາວ',
        'P-line Company - Vientiane, Laos' => 'ບໍລິສັດ P-line - ວຽງຈັນ, ລາວ',
        'ITAM System' => 'ລະບົບ ITAM',

        // Buttons / actions
        'View All' => 'ເບິ່ງທັງໝົດ',
        'View' => 'ເບິ່ງ',
        'Add' => 'ເພີ່ມ',
        'Add Asset' => 'ເພີ່ມຊັບສິນ',
        'Add User' => 'ເພີ່ມຜູ້ໃຊ້',
        'Add New Asset' => 'ເພີ່ມຊັບສິນໃໝ່',
        'Add New User' => 'ເພີ່ມຜູ້ໃຊ້ໃໝ່',
        'Edit' => 'ແກ້ໄຂ',
        'Edit User' => 'ແກ້ໄຂຜູ້ໃຊ້',
        'Edit Asset' => 'ແກ້ໄຂຊັບສິນ',
        'Delete' => 'ລຶບ',
        'Deactivate' => 'ປິດການໃຊ້ງານ',
        'Search' => 'ຄົ້ນຫາ',
        'Clear' => 'ລ້າງ',
        'Cancel' => 'ຍົກເລີກ',
        'Save' => 'ບັນທຶກ',
        'Save User' => 'ບັນທຶກຜູ້ໃຊ້',
        'Save Asset' => 'ບັນທຶກຊັບສິນ',
        'Confirm Check Out' => 'ຢືນຢັນການເບີກ',
        'Confirm Check In' => 'ຢືນຢັນການຄືນ',
        'Print' => 'ພິມ',
        'Excel' => 'ເອັກເຊວ',
        'Generate' => 'ສ້າງ',
        'Print Report' => 'ພິມລາຍງານ',
        'Export PDF' => 'ສົ່ງອອກ PDF',
        'Export CSV' => 'ສົ່ງອອກ CSV',
        'Generate Report' => 'ສ້າງລາຍງານ',
        'Reset' => 'ຣີເຊັດ',
        'Close' => 'ປິດ',

        // Roles / status
        'Admin' => 'ແອັດມິນ',
        'User' => 'ຜູ້ໃຊ້',
        'Role' => 'ສິດທິ',
        'Status' => 'ສະຖານະ',
        'Active' => 'ໃຊ້ງານ',
        'Inactive' => 'ບໍ່ໃຊ້ງານ',
        'Available' => 'ພ້ອມໃຊ້',
        'In Use' => 'ກຳລັງໃຊ້',
        'Check Out' => 'ເບີກ',
        'Check In' => 'ຄືນ',
        'Check-outs Only' => 'ສະເພາະການເບີກ',
        'Check-ins Only' => 'ສະເພາະການຄືນ',
        'All Activities' => 'ກິດຈະກຳທັງໝົດ',

        // Dashboard cards and sections
        'Recent Activities' => 'ກິດຈະກຳຫຼ້າສຸດ',
        'Assets by Category' => 'ຊັບສິນຕາມປະເພດ',
        'Quick Actions' => 'ການດຳເນີນການດ່ວນ',
        'Total Assets' => 'ຊັບສິນທັງໝົດ',
        'Total Value' => 'ມູນຄ່າລວມ',
        'Asset worth' => 'ມູນຄ່າຊັບສິນ',
        'Current Date' => 'ວັນທີປັດຈຸບັນ',
        'Welcome back,' => 'ຍິນດີຕ້ອນຮັບກັບ,',
        'My Assigned Assets' => 'ຊັບສິນທີ່ມອບໝາຍໃຫ້ຂ້ອຍ',
        'Assets currently assigned to you' => 'ຊັບສິນທີ່ມອບໝາຍໃຫ້ທ່ານໃນປັດຈຸບັນ',
        'No assets currently assigned to you' => 'ຂະນະນີ້ຍັງບໍ່ມີການມອບໝາຍຊັບສິນໃຫ້ທ່ານ',
        'No Assets Assigned' => 'ບໍ່ມີການມອບໝາຍຊັບສິນ',
        'You currently don\'t have any assets assigned to you.' => 'ຂະນະນີ້ທ່ານຍັງບໍ່ມີຊັບສິນທີ່ຖືກມອບໝາຍ',
        'My Activity' => 'ກິດຈະກຳຂອງຂ້ອຍ',
        'No activity records' => 'ບໍ່ພົບບັນທຶກກິດຈະກຳ',
        'No recent activity' => 'ບໍ່ມີກິດຈະກຳຫຼ້າສຸດ',
        'No category data' => 'ບໍ່ມີຂໍ້ມູນປະເພດ',
        'Latest Assignment' => 'ການມອບໝາຍຫຼ້າສຸດ',
        'Activity Records' => 'ບັນທຶກກິດຈະກຳ',
        'All assets' => 'ຊັບສິນທັງໝົດ',
        'items' => 'ລາຍການ',
        'item' => 'ລາຍການ',
        'just now' => 'ຫາກໍ່ນີ້',
        'minute ago' => 'ນາທີກ່ອນ',
        'minutes ago' => 'ນາທີກ່ອນ',
        'hour ago' => 'ຊົ່ວໂມງກ່ອນ',
        'hours ago' => 'ຊົ່ວໂມງກ່ອນ',
        'day ago' => 'ມື້ກ່ອນ',
        'days ago' => 'ມື້ກ່ອນ',

        // Assets page
        'Manage and track all IT assets' => 'ຈັດການແລະຕິດຕາມຊັບສິນ IT ທັງໝົດ',
        'All Categories' => 'ທຸກປະເພດ',
        'All Status' => 'ທຸກສະຖານະ',
        'Category' => 'ປະເພດ',
        'Computers' => 'ຄອມພິວເຕີ',
        'Computer' => 'ຄອມພິວເຕີ',
        'Phones' => 'ໂທລະສັບ',
        'Phone' => 'ໂທລະສັບ',
        'Printers' => 'ເຄື່ອງພິມ',
        'Printer' => 'ເຄື່ອງພິມ',
        'Accessories' => 'ອຸປະກອນເສີມ',
        'Accessory' => 'ອຸປະກອນເສີມ',
        'Other' => 'ອື່ນໆ',
        'Asset ID' => 'ລະຫັດຊັບສິນ',
        'Asset Name' => 'ຊື່ຊັບສິນ',
        'Asset Code' => 'ລະຫັດຊັບສິນ',
        'Name' => 'ຊື່',
        'Date' => 'ວັນທີ',
        'Asset' => 'ຊັບສິນ',
        'Price' => 'ລາຄາ',
        'Asset Count' => 'ຈຳນວນຊັບສິນ',
        'Total Value ($)' => 'ມູນຄ່າລວມ ($)',
        'Serial Number' => 'ເລກຊີເຣຍວ',
        'Assigned To' => 'ມອບໝາຍໃຫ້',
        'Assigned Date' => 'ວັນທີມອບໝາຍ',
        'Actions' => 'ການດຳເນີນການ',
        'No assets found' => 'ບໍ່ພົບຊັບສິນ',
        'Showing' => 'ສະແດງ',
        'Previous' => 'ກ່ອນໜ້າ',
        'Next' => 'ຕໍ່ໄປ',
        'Select Category' => 'ເລືອກປະເພດ',
        'Brand' => 'ຍີ່ຫໍ້',
        'Model' => 'ຮຸ່ນ',
        'Purchase Date' => 'ວັນທີຊື້',
        'Purchase Price' => 'ລາຄາຊື້',
        'Purchase Price ($)' => 'ລາຄາຊື້ ($)',
        'Asset Photo' => 'ຮູບຊັບສິນ',

        // Users page
        'User Management' => 'ການຈັດການຜູ້ໃຊ້',
        'Manage system users and permissions' => 'ຈັດການຜູ້ໃຊ້ແລະສິດທິໃນລະບົບ',
        'Search users by name or email' => 'ຄົ້ນຫາຜູ້ໃຊ້ຕາມຊື່ ຫຼື ອີເມວ',
        'result(s) for' => 'ຜົນລັບສຳລັບ',
        'Total:' => 'ລວມ:',
        'active user(s)' => 'ຜູ້ໃຊ້ທີ່ໃຊ້ງານ',
        'No users found' => 'ບໍ່ພົບຜູ້ໃຊ້',
        'Email' => 'ອີເມວ',
        'Assets Assigned' => 'ຊັບສິນທີ່ມອບໝາຍ',
        'View Assets' => 'ເບິ່ງຊັບສິນ',
        'Full Name' => 'ຊື່ເຕັມ',
        'Email Address' => 'ອີເມວ',
        'Password' => 'ລະຫັດຜ່ານ',
        'Minimum 8 characters' => 'ຢ່າງໜ້ອຍ 8 ຕົວອັກສອນ',

        // Checkout page
        'Manage asset assignments' => 'ຈັດການການມອບໝາຍຊັບສິນ',
        'Check Out Asset' => 'ເບີກຊັບສິນ',
        'Check In Asset' => 'ຄືນຊັບສິນ',
        'Select Asset (Available)' => 'ເລືອກຊັບສິນ (ພ້ອມໃຊ້)',
        'Select Asset (In Use)' => 'ເລືອກຊັບສິນ (ກຳລັງໃຊ້)',
        'Choose an available asset...' => 'ເລືອກຊັບສິນທີ່ພ້ອມໃຊ້...',
        'Choose asset in use...' => 'ເລືອກຊັບສິນທີ່ກຳລັງໃຊ້...',
        'Assign To User' => 'ມອບໝາຍໃຫ້ຜູ້ໃຊ້',
        'Select user...' => 'ເລືອກຜູ້ໃຊ້...',
        'Type to search by asset code or name.' => 'ພິມເພື່ອຄົ້ນຫາຕາມລະຫັດ ຫຼື ຊື່ຊັບສິນ',
        'Type to search by user name or email.' => 'ພິມເພື່ອຄົ້ນຫາຕາມຊື່ ຫຼື ອີເມວຜູ້ໃຊ້',
        'Notes (Optional)' => 'ໝາຍເຫດ (ທາງເລືອກ)',
        'Condition Notes (Optional)' => 'ໝາຍເຫດສະພາບ (ທາງເລືອກ)',
        'Add any notes...' => 'ເພີ່ມໝາຍເຫດ...',
        'Asset condition, issues, etc...' => 'ສະພາບຊັບສິນ, ບັນຫາ ແລະ ອື່ນໆ...',
        'Asset returned in good condition' => 'ຊັບສິນຖືກຄືນໃນສະພາບດີ',
        'Current Assignments' => 'ການມອບໝາຍປັດຈຸບັນ',
        'Date Assigned' => 'ວັນທີມອບໝາຍ',
        'No assets currently in use' => 'ບໍ່ມີຊັບສິນທີ່ກຳລັງໃຊ້',
        'No matches found' => 'ບໍ່ພົບຂໍ້ມູນທີ່ກົງ',
        'Available assets' => 'ຊັບສິນທີ່ພ້ອມໃຊ້',
        'Assets in use' => 'ຊັບສິນທີ່ກຳລັງໃຊ້',
        'Unknown' => 'ບໍ່ຮູ້ຈັກ',
        'N/A' => 'ບໍ່ມີຂໍ້ມູນ',

        // History
        'Check-In/Out History' => 'ປະຫວັດການເບີກ/ຄືນ',
        'Complete audit trail of asset movements' => 'ບັນທຶກການເຄື່ອນໄຫວຊັບສິນຢ່າງຄົບຖ້ວນ',
        'Date & Time' => 'ວັນທີ & ເວລາ',
        'Action' => 'ການດຳເນີນການ',
        'From' => 'ຈາກ',
        'To' => 'ຫາ',
        'Any' => 'ທັງໝົດ',
        'All' => 'ທັງໝົດ',
        'Yes' => 'ແມ່ນ',
        'No' => 'ບໍ່',
        'Performed By' => 'ດຳເນີນການໂດຍ',
        'Notes' => 'ໝາຍເຫດ',
        'No history records found' => 'ບໍ່ພົບບັນທຶກປະຫວັດ',
        'User:' => 'ຜູ້ໃຊ້:',

        // Reports
        'Generate and export asset reports' => 'ສ້າງແລະສົ່ງອອກລາຍງານຊັບສິນ',
        'Generate and export your personal reports' => 'ສ້າງແລະສົ່ງອອກລາຍງານສ່ວນຕົວຂອງທ່ານ',
        'All Assets Report' => 'ລາຍງານຊັບສິນທັງໝົດ',
        'User Assets Report' => 'ລາຍງານຊັບສິນຂອງຜູ້ໃຊ້',
        'Asset Value Report' => 'ລາຍງານມູນຄ່າຊັບສິນ',
        'Activity Log Report' => 'ລາຍງານບັນທຶກກິດຈະກຳ',
        'My Assets Report' => 'ລາຍງານຊັບສິນຂອງຂ້ອຍ',
        'My Activity Report' => 'ລາຍງານກິດຈະກຳຂອງຂ້ອຍ',
        'Custom Date Range Report' => 'ລາຍງານຊ່ວງວັນທີກຳນົດເອງ',
        'Custom Date Range (My Activity)' => 'ຊ່ວງວັນທີກຳນົດເອງ (ກິດຈະກຳຂອງຂ້ອຍ)',
        'From Date' => 'ຈາກວັນທີ',
        'To Date' => 'ຫາວັນທີ',
        'Report Type' => 'ປະເພດລາຍງານ',
        'Complete inventory list grouped by category and status with valuation.' => 'ລາຍການສິນຄົງຄັງທັງໝົດແຍກຕາມປະເພດແລະສະຖານະພ້ອມມູນຄ່າ',
        'Assets assigned to specific users with assignment dates and history.' => 'ຊັບສິນທີ່ມອບໝາຍໃຫ້ຜູ້ໃຊ້ພ້ອມວັນທີແລະປະຫວັດ',
        'Financial overview with category valuation and depreciation tracking.' => 'ພາບລວມທາງການເງິນພ້ອມມູນຄ່າຕາມປະເພດແລະການຕິດຕາມຄ່າເສື່ອມ',
        'Complete check-in/check-out history with filters and timestamps.' => 'ປະຫວັດການເບີກ/ຄືນທັງໝົດພ້ອມຕົວກອງແລະເວລາ',
        'Assets currently assigned to your account with assignment and valuation details.' => 'ຊັບສິນທີ່ມອບໝາຍໃຫ້ບັນຊີຂອງທ່ານພ້ອມລາຍລະອຽດການມອບໝາຍແລະມູນຄ່າ',
        'Your check-in/check-out history with timestamps and notes.' => 'ປະຫວັດການເບີກ/ຄືນຂອງທ່ານພ້ອມເວລາແລະໝາຍເຫດ',

        // Profile
        'My Profile' => 'ໂປຣໄຟລ໌ຂອງຂ້ອຍ',
        'Account Information' => 'ຂໍ້ມູນບັນຊີ',
        'Change Password' => 'ປ່ຽນລະຫັດຜ່ານ',
        'Current Password' => 'ລະຫັດຜ່ານປັດຈຸບັນ',
        'New Password' => 'ລະຫັດຜ່ານໃໝ່',
        'Confirm New Password' => 'ຢືນຢັນລະຫັດຜ່ານໃໝ່',
        'Member Since' => 'ເປັນສະມາຊິກຕັ້ງແຕ່',

        // Error pages
        'Access denied' => 'ປະຕິເສດການເຂົ້າເຖິງ',
        'Access Denied' => 'ປະຕິເສດການເຂົ້າເຖິງ',
        'Page Not Found' => 'ບໍ່ພົບໜ້າທີ່ຕ້ອງການ',
        'Return to Dashboard' => 'ກັບໄປແດຊບອດ',

        // Auth / account
        'Login' => 'ເຂົ້າລະບົບ',
        'Sign In' => 'ເຂົ້າລະບົບ',
        'Remember me' => 'ຈື່ຂ້ອຍໄວ້',
        'Forgot password? Contact admin' => 'ລືມລະຫັດຜ່ານ? ຕິດຕໍ່ແອັດມິນ',
        'Demo Accounts' => 'ບັນຊີທົດລອງ',
        'Admin Demo' => 'ແອັດມິນທົດລອງ',
        'User Demo' => 'ຜູ້ໃຊ້ທົດລອງ',

        // Toasts / server messages
        'Operation successful' => 'ດຳເນີນການສຳເລັດ',
        'Operation failed' => 'ດຳເນີນການລົ້ມເຫຼວ',
        'Invalid security token' => 'ໂທເຄັນຄວາມປອດໄພບໍ່ຖືກຕ້ອງ',
        'Invalid email or password' => 'ອີເມວ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ',
        'User not found' => 'ບໍ່ພົບຜູ້ໃຊ້',
        'Current password is incorrect' => 'ລະຫັດຜ່ານປັດຈຸບັນບໍ່ຖືກຕ້ອງ',
        'Password must be at least 8 characters' => 'ລະຫັດຜ່ານຕ້ອງຢ່າງໜ້ອຍ 8 ຕົວອັກສອນ',
        'Password changed successfully' => 'ປ່ຽນລະຫັດຜ່ານສຳເລັດ',
        'Failed to update password' => 'ປັບປຸງລະຫັດຜ່ານບໍ່ສຳເລັດ',
        'Email already exists' => 'ອີເມວນີ້ມີຢູ່ແລ້ວ',
        'Email already in use by another user' => 'ອີເມວນີ້ຖືກໃຊ້ໂດຍຜູ້ໃຊ້ອື່ນແລ້ວ',
        'User created successfully' => 'ສ້າງຜູ້ໃຊ້ສຳເລັດ',
        'User updated successfully' => 'ອັບເດດຜູ້ໃຊ້ສຳເລັດ',
        'User deactivated successfully' => 'ປິດການໃຊ້ງານຜູ້ໃຊ້ສຳເລັດ',
        'Failed to create user' => 'ສ້າງຜູ້ໃຊ້ບໍ່ສຳເລັດ',
        'Failed to update user' => 'ອັບເດດຜູ້ໃຊ້ບໍ່ສຳເລັດ',
        'Failed to deactivate user' => 'ປິດການໃຊ້ງານຜູ້ໃຊ້ບໍ່ສຳເລັດ',
        'Cannot deactivate user with assigned assets' => 'ບໍ່ສາມາດປິດການໃຊ້ງານຜູ້ໃຊ້ທີ່ຍັງມີຊັບສິນຖືກມອບໝາຍ',
        'You cannot deactivate your own account' => 'ທ່ານບໍ່ສາມາດປິດການໃຊ້ງານບັນຊີຕົນເອງ',
        'Failed to create asset' => 'ສ້າງຊັບສິນບໍ່ສຳເລັດ',
        'Asset updated successfully' => 'ອັບເດດຊັບສິນສຳເລັດ',
        'Asset deleted successfully' => 'ລຶບຊັບສິນສຳເລັດ',
        'Asset checked out successfully' => 'ເບີກຊັບສິນສຳເລັດ',
        'Asset checked in successfully' => 'ຄືນຊັບສິນສຳເລັດ',
        'Asset is not available for check out' => 'ຊັບສິນນີ້ບໍ່ພ້ອມໃຫ້ເບີກ',
        'Asset is not checked out' => 'ຊັບສິນນີ້ຍັງບໍ່ໄດ້ຖືກເບີກ',
        'Asset not found' => 'ບໍ່ພົບຊັບສິນ',
        'Failed to update asset' => 'ອັບເດດຊັບສິນບໍ່ສຳເລັດ',
        'Failed to delete asset' => 'ລຶບຊັບສິນບໍ່ສຳເລັດ',
        'Failed to check out asset' => 'ເບີກຊັບສິນບໍ່ສຳເລັດ',
        'Failed to check in asset' => 'ຄືນຊັບສິນບໍ່ສຳເລັດ',
        'Profile updated successfully' => 'ອັບເດດໂປຣໄຟລ໌ສຳເລັດ',
        'Please select a date range' => 'ກະລຸນາເລືອກຊ່ວງວັນທີ',
        'From Date must be earlier than To Date' => 'ຈາກວັນທີ ຕ້ອງໄວກວ່າ ຫາວັນທີ',
        'Please select an available asset.' => 'ກະລຸນາເລືອກຊັບສິນທີ່ພ້ອມໃຊ້',
        'Please select a valid user.' => 'ກະລຸນາເລືອກຜູ້ໃຊ້ທີ່ຖືກຕ້ອງ',
        'Please select an asset in use.' => 'ກະລຸນາເລືອກຊັບສິນທີ່ກຳລັງໃຊ້',
        'Please select an available asset from the list.' => 'ກະລຸນາເລືອກຊັບສິນທີ່ພ້ອມໃຊ້ຈາກລາຍການ',
        'Please select a user from the list.' => 'ກະລຸນາເລືອກຜູ້ໃຊ້ຈາກລາຍການ',
        'Please select an asset in use from the list.' => 'ກະລຸນາເລືອກຊັບສິນທີ່ກຳລັງໃຊ້ຈາກລາຍການ',
        'Are you sure you want to delete this item?' => 'ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບລາຍການນີ້?',
        'Are you sure you want to delete this asset?' => 'ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບຊັບສິນນີ້?',
        'Are you sure you want to deactivate this user?' => 'ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການປິດການໃຊ້ງານຜູ້ໃຊ້ນີ້?',
        'An error occurred while fetching data' => 'ເກີດຂໍ້ຜິດພາດໃນຂະນະດຶງຂໍ້ມູນ',
        'Invalid report type.' => 'ປະເພດລາຍງານບໍ່ຖືກຕ້ອງ',
        'Unsupported format.' => 'ຮູບແບບທີ່ເລືອກບໍ່ຮອງຮັບ',

        // Month abbreviations used in formatted dates
        'Jan' => 'ມ.ກ',
        'Feb' => 'ກ.ພ',
        'Mar' => 'ມ.ນ',
        'Apr' => 'ມ.ສ',
        'May' => 'ພ.ພ',
        'Jun' => 'ມິ.ຖ',
        'Jul' => 'ກ.ລ',
        'Aug' => 'ສ.ຫ',
        'Sep' => 'ກ.ຍ',
        'Oct' => 'ຕ.ລ',
        'Nov' => 'ພ.ຈ',
        'Dec' => 'ທ.ວ'
    ];

    return $map;
}

// Translate generic UI text using phrase mapping.
function tr($text) {
    $text = (string)$text;
    if ($text === '' || currentLang() !== LANG_LO) {
        return $text;
    }

    return strtr($text, uiTextMap());
}

// Translation map for frontend JS translation.
function clientTextMap() {
    return currentLang() === LANG_LO ? uiTextMap() : [];
}

// Build current URL with selected language
function langUrl($lang) {
    $lang = strtolower(trim((string)$lang));
    if (!in_array($lang, supportedLanguages(), true)) {
        $lang = DEFAULT_LANG;
    }

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
    $params = $_GET;
    $params['lang'] = $lang;
    return $path . '?' . http_build_query($params);
}

// Helper function to redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

// Helper function to check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === ROLE_ADMIN;
}

// Helper function to require authentication
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = t('auth.login_required');
        redirect("/views/auth/login.php");
    }
}

// Helper function to require admin
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        $_SESSION['error'] = t('auth.access_denied_admin');
        redirect("/views/user/dashboard.php");
    }
}
?>
