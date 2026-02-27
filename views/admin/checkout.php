<?php
/**
 * ລະບົບ ITAM - ການຈັດການເບີກ/ຄືນ
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AssetController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

requireAdmin();

$assetController = new AssetController();
$userController = new UserController();

// ຈັດການການເບີກ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $assetId = $_POST['asset_id'] ?? '';
        $userId = $_POST['user_id'] ?? '';

        // ສຳຮອງ: ຖ້າ JS ບໍ່ໄດ້ map ID, ວິເຄາະຂໍ້ຄວາມທີ່ປ້ອນ
        if (!is_numeric($assetId)) {
            $assetSearch = trim((string)($_POST['asset_search'] ?? ''));
            if ($assetSearch !== '') {
                $code = $assetSearch;
                if (strpos($assetSearch, ' - ') !== false) {
                    $code = explode(' - ', $assetSearch, 2)[0];
                } else {
                    $parts = preg_split('/\s+/', $assetSearch);
                    $code = $parts[0] ?? $assetSearch;
                }

                require_once __DIR__ . '/../../models/Asset.php';
                $assetModel = new Asset();
                $assetRow = $assetModel->findOneBy('asset_code', $code);
                if ($assetRow && isset($assetRow['asset_id'])) {
                    $assetId = (int)$assetRow['asset_id'];
                }
            }
        }

        if (!is_numeric($userId)) {
            $userSearch = trim((string)($_POST['user_search'] ?? ''));
            if ($userSearch !== '') {
                $email = $userSearch;
                if (preg_match('/\(([^)]+)\)\s*$/', $userSearch, $m)) {
                    $email = $m[1];
                }

                if (strpos($email, '@') !== false) {
                    require_once __DIR__ . '/../../models/User.php';
                    $userModel = new User();
                    $userRow = $userModel->findByEmail($email);
                    if ($userRow && ($userRow['role'] ?? '') === 'User' && !empty($userRow['is_active'])) {
                        $userId = (int)$userRow['user_id'];
                    }
                }
            }
        }

        if (!is_numeric($assetId) || (int)$assetId <= 0) {
            $_SESSION['error'] = 'Please select an available asset.';
            redirect('/views/admin/checkout.php');
        }
        if (!is_numeric($userId) || (int)$userId <= 0) {
            $_SESSION['error'] = 'Please select a valid user.';
            redirect('/views/admin/checkout.php');
        }

        $result = $assetController->checkOut(
            (int)$assetId,
            (int)$userId,
            $_POST['notes'] ?? ''
        );
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } else {
        $_SESSION['error'] = 'Invalid security token';
    }
    redirect('/views/admin/checkout.php');
}

// ຈັດການການຄືນ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkin') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $assetId = $_POST['asset_id'] ?? '';

        // ສຳຮອງ: ຖ້າ JS ບໍ່ໄດ້ map ID, ວິເຄາະຂໍ້ຄວາມທີ່ປ້ອນ
        if (!is_numeric($assetId)) {
            $assetSearch = trim((string)($_POST['asset_search'] ?? ''));
            if ($assetSearch !== '') {
                $code = $assetSearch;
                if (strpos($assetSearch, ' - ') !== false) {
                    $code = explode(' - ', $assetSearch, 2)[0];
                } else {
                    $parts = preg_split('/\s+/', $assetSearch);
                    $code = $parts[0] ?? $assetSearch;
                }

                require_once __DIR__ . '/../../models/Asset.php';
                $assetModel = new Asset();
                $assetRow = $assetModel->findOneBy('asset_code', $code);
                if ($assetRow && isset($assetRow['asset_id'])) {
                    $assetId = (int)$assetRow['asset_id'];
                }
            }
        }

        if (!is_numeric($assetId) || (int)$assetId <= 0) {
            $_SESSION['error'] = 'Please select an asset in use.';
            redirect('/views/admin/checkout.php');
        }

        $result = $assetController->checkIn(
            (int)$assetId,
            $_POST['notes'] ?? ''
        );
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } else {
        $_SESSION['error'] = 'Invalid security token';
    }
    redirect('/views/admin/checkout.php');
}

$availableAssets = $assetController->getAvailableAssets();
$inUseAssets = $assetController->getInUseAssets();
$activeUsers = $userController->getActiveUsersForSelect();

$pageTitle = 'Check In / Check Out';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<div class="main-content">
    <header class="top-header">
        <button class="btn btn-icon d-md-none" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <?php echo userAvatar(); ?>
        </div>
    </header>

    <div class="fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Check In / Check Out</h1>
                <p class="text-muted mb-0">Manage asset assignments</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- ເບີກ -->
            <div class="col-md-6">
                <div class="glass-card p-4">
                    <h5 class="mb-4"><i class="bi bi-arrow-right-circle text-primary me-2"></i>Check Out Asset</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="checkout">

                        <div class="mb-3">
                            <label class="form-label">Select Asset (Available)</label>
                            <div class="itam-ac" id="checkoutAssetAc">
                                <i class="bi bi-chevron-down itam-ac-caret"></i>
                                <input type="hidden" name="asset_id" id="checkoutAssetId">
                                <input type="text" name="asset_search" class="form-control form-control-glass itam-ac-input" id="checkoutAssetSearch" placeholder="Choose an available asset..." autocomplete="off" required>
                                <div class="itam-ac-menu glass-card" id="checkoutAssetMenu" role="listbox" aria-label="Available assets"></div>
                            </div>
                            <div class="form-text">Type to search by asset code or name.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign To User</label>
                            <div class="itam-ac" id="checkoutUserAc">
                                <i class="bi bi-chevron-down itam-ac-caret"></i>
                                <input type="hidden" name="user_id" id="checkoutUserId">
                                <input type="text" name="user_search" class="form-control form-control-glass itam-ac-input" id="checkoutUserSearch" placeholder="Select user..." autocomplete="off" required>
                                <div class="itam-ac-menu glass-card" id="checkoutUserMenu" role="listbox" aria-label="Users"></div>
                            </div>
                            <div class="form-text">Type to search by user name or email.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control form-control-glass" rows="3" placeholder="Add any notes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary-gradient w-100">
                            <i class="bi bi-check-lg me-2"></i>Confirm Check Out
                        </button>
                    </form>
                </div>
            </div>

            <!-- ຄືນ -->
            <div class="col-md-6">
                <div class="glass-card p-4">
                    <h5 class="mb-4"><i class="bi bi-arrow-left-circle text-success me-2"></i>Check In Asset</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="checkin">

                        <div class="mb-3">
                            <label class="form-label">Select Asset (In Use)</label>
                            <div class="itam-ac" id="checkinAssetAc">
                                <i class="bi bi-chevron-down itam-ac-caret"></i>
                                <input type="hidden" name="asset_id" id="checkinAssetId">
                                <input type="text" name="asset_search" class="form-control form-control-glass itam-ac-input" id="checkinAssetSearch" placeholder="Choose asset in use..." autocomplete="off" required>
                                <div class="itam-ac-menu glass-card" id="checkinAssetMenu" role="listbox" aria-label="Assets in use"></div>
                            </div>
                            <div class="form-text">Type to search by asset code or name.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Condition Notes (Optional)</label>
                            <textarea name="notes" class="form-control form-control-glass" rows="3" placeholder="Asset condition, issues, etc..."></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="returnCondition" name="condition_check">
                                <label class="form-check-label" for="returnCondition">
                                    Asset returned in good condition
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100" style="background: linear-gradient(135deg, #10B981, #059669); border: none; padding: 12px; border-radius: 12px; font-weight: 600; color: white;">
                            <i class="bi bi-check-lg me-2"></i>Confirm Check In
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ການມອບໝາຍປັດຈຸບັນ -->
        <div class="mt-4">
            <div class="glass-card p-4">
                <h5 class="mb-4">Current Assignments</h5>
                <div class="table-glass">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Assigned To</th>
                                <th>Date Assigned</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inUseAssets)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No assets currently in use</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inUseAssets as $asset): ?>
                                    <?php 
                                        $assignedUser = array_filter($activeUsers, function($u) use ($asset) {
                                            return $u['user_id'] == $asset['assigned_to'];
                                        });
                                        $assignedUser = reset($assignedUser);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo e($asset['asset_name']); ?></div>
                                            <small class="text-muted"><?php echo e($asset['asset_code']); ?></small>
                                        </td>
                                        <td><?php echo $assignedUser ? e($assignedUser['name']) : 'Unknown'; ?></td>
                                        <td><?php echo $asset['assigned_date'] ? date('M j, Y', strtotime($asset['assigned_date'])) : 'N/A'; ?></td>
                                        <td><span class="badge-custom badge-in-use">In Use</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

document.addEventListener('DOMContentLoaded', function () {
    const availableAssetOptions = <?php
        $options = [];
        foreach ($availableAssets as $a) {
            $label = ($a['asset_code'] ?? '') . ' - ' . ($a['asset_name'] ?? '');
            $options[] = [
                'id' => (int)($a['asset_id'] ?? 0),
                'label' => $label,
                'code' => (string)($a['asset_code'] ?? '')
            ];
        }
        echo json_encode($options);
    ?>;

    const userOptions = <?php
        $options = [];
        foreach ($activeUsers as $u) {
            if (($u['role'] ?? '') !== 'User') continue;
            $label = ($u['name'] ?? '') . ' (' . ($u['email'] ?? '') . ')';
            $options[] = [
                'id' => (int)($u['user_id'] ?? 0),
                'label' => $label,
                'email' => (string)($u['email'] ?? '')
            ];
        }
        echo json_encode($options);
    ?>;

    const inUseAssetOptions = <?php
        $options = [];
        $usersById = [];
        foreach ($activeUsers as $u) {
            if (isset($u['user_id'])) {
                $usersById[(int)$u['user_id']] = $u;
            }
        }
        foreach ($inUseAssets as $a) {
            $label = ($a['asset_code'] ?? '') . ' - ' . ($a['asset_name'] ?? '');
            $assignedTo = $a['assigned_to'] ?? null;
            if ($assignedTo !== null && isset($usersById[(int)$assignedTo])) {
                $assignedName = $usersById[(int)$assignedTo]['name'] ?? '';
                if ($assignedName !== '') {
                    $label .= ' (' . $assignedName . ')';
                }
            }
            $options[] = [
                'id' => (int)($a['asset_id'] ?? 0),
                'label' => $label,
                'code' => (string)($a['asset_code'] ?? '')
            ];
        }
        echo json_encode($options);
    ?>;

    function makeIndex(options, key) {
        const map = {};
        for (const o of options) {
            const k = (o && o[key]) ? String(o[key]).trim() : '';
            if (!k) continue;
            map[k] = String(o.id);
        }
        return map;
    }

    function createAutocomplete(acRootId, inputId, hiddenId, menuId, options, fallbackIndex) {
        const root = document.getElementById(acRootId);
        const input = document.getElementById(inputId);
        const hidden = document.getElementById(hiddenId);
        const menu = document.getElementById(menuId);
        if (!root || !input || !hidden || !menu) return null;

        const maxResults = 100;
        const normalized = options.map(o => ({
            ...o,
            searchText: (String(o.label || '') + ' ' + String(o.code || '') + ' ' + String(o.email || '')).toLowerCase()
        }));

        let open = false;
        let activeIndex = -1;
        let filtered = normalized.slice();

        const close = () => {
            open = false;
            activeIndex = -1;
            root.classList.remove('open');
            menu.classList.remove('show');
            menu.innerHTML = '';
        };

        const openMenu = () => {
            if (open) return;
            open = true;
            root.classList.add('open');
            menu.classList.add('show');
            render();
        };

        const setHiddenFromInput = () => {
            const raw = (input.value || '').trim();
            hidden.value = '';

            if (!raw) return;

            if (fallbackIndex && fallbackIndex.labelToId && fallbackIndex.labelToId[raw]) {
                hidden.value = fallbackIndex.labelToId[raw];
                return;
            }

            if (fallbackIndex && fallbackIndex.secondaryToId) {
                let secondary = raw;
                if (raw.includes(' - ')) {
                    secondary = raw.split(' - ')[0].trim();
                } else if (raw.includes('@')) {
                    secondary = raw;
                } else {
                    secondary = (raw.split(/\s+/)[0] || raw).trim();
                }

                if (fallbackIndex.secondaryToId[secondary]) {
                    hidden.value = fallbackIndex.secondaryToId[secondary];
                }
            }
        };

        const filterOptions = () => {
            const q = (input.value || '').trim().toLowerCase();
            if (!q) {
                filtered = normalized.slice();
            } else {
                filtered = normalized.filter(o => o.searchText.includes(q));
            }
            activeIndex = filtered.length ? 0 : -1;
            render();
        };

        const select = (opt) => {
            if (!opt) return;
            input.value = String(opt.label || '');
            hidden.value = String(opt.id || '');
            close();
        };

        const render = () => {
            if (!open) return;
            menu.innerHTML = '';

            const list = filtered.slice(0, maxResults);
            if (list.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'itam-ac-empty';
                empty.textContent = 'No matches found';
                menu.appendChild(empty);
                return;
            }

            list.forEach((opt, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'itam-ac-item' + (idx === activeIndex ? ' active' : '');
                btn.setAttribute('role', 'option');
                btn.setAttribute('aria-selected', idx === activeIndex ? 'true' : 'false');
                btn.textContent = String(opt.label || '');

                btn.addEventListener('mousedown', function (e) {
                    // ປ້ອງກັນ blur ກ່ອນເລືອກ
                    e.preventDefault();
                    select(opt);
                });

                menu.appendChild(btn);
            });
        };

        input.addEventListener('focus', function () {
            setHiddenFromInput();
            openMenu();
            filterOptions();
        });
        input.addEventListener('click', function () {
            setHiddenFromInput();
            openMenu();
            filterOptions();
        });
        input.addEventListener('input', function () {
            setHiddenFromInput();
            openMenu();
            filterOptions();
        });
        input.addEventListener('keydown', function (e) {
            if (!open && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                openMenu();
                filterOptions();
            }

            const listLen = Math.min(filtered.length, maxResults);
            if (!listLen) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, listLen - 1);
                render();
                const el = menu.querySelector('.itam-ac-item.active');
                if (el) el.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                render();
                const el = menu.querySelector('.itam-ac-item.active');
                if (el) el.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                if (open && activeIndex >= 0) {
                    e.preventDefault();
                    select(filtered[activeIndex]);
                }
            } else if (e.key === 'Escape') {
                close();
            }
        });

        document.addEventListener('mousedown', function (e) {
            if (!root.contains(e.target)) {
                close();
            }
        });

        // ສົ່ງອອກ helper ສຳລັບກວດສອບກ່ອນສົ່ງ
        return { input, hidden, setHiddenFromInput, close };
    }

    const availableAssetIndex = {
        labelToId: makeIndex(availableAssetOptions, 'label'),
        secondaryToId: makeIndex(availableAssetOptions, 'code')
    };
    const userIndex = {
        labelToId: makeIndex(userOptions, 'label'),
        secondaryToId: makeIndex(userOptions, 'email')
    };
    const inUseAssetIndex = {
        labelToId: makeIndex(inUseAssetOptions, 'label'),
        secondaryToId: makeIndex(inUseAssetOptions, 'code')
    };

    const checkoutAssetAc = createAutocomplete(
        'checkoutAssetAc',
        'checkoutAssetSearch',
        'checkoutAssetId',
        'checkoutAssetMenu',
        availableAssetOptions,
        availableAssetIndex
    );

    const checkoutUserAc = createAutocomplete(
        'checkoutUserAc',
        'checkoutUserSearch',
        'checkoutUserId',
        'checkoutUserMenu',
        userOptions,
        userIndex
    );

    const checkinAssetAc = createAutocomplete(
        'checkinAssetAc',
        'checkinAssetSearch',
        'checkinAssetId',
        'checkinAssetMenu',
        inUseAssetOptions,
        inUseAssetIndex
    );

    // ກວດສອບກ່ອນສົ່ງ (ປ້ອງກັນຂໍ້ຄວາມຜິດພາດທີ່ສັບສົນ)
    const checkoutForm = checkoutAssetAc ? checkoutAssetAc.input.closest('form') : null;
    if (checkoutForm && checkoutAssetAc && checkoutUserAc) {
        checkoutForm.addEventListener('submit', function (e) {
            checkoutAssetAc.setHiddenFromInput();
            checkoutUserAc.setHiddenFromInput();

            if (!checkoutAssetAc.hidden.value) {
                e.preventDefault();
                showToast('Please select an available asset from the list.', 'error');
                checkoutAssetAc.input.focus();
                return;
            }
            if (!checkoutUserAc.hidden.value) {
                e.preventDefault();
                showToast('Please select a user from the list.', 'error');
                checkoutUserAc.input.focus();
            }
        });
    }

    const checkinForm = checkinAssetAc ? checkinAssetAc.input.closest('form') : null;
    if (checkinForm && checkinAssetAc) {
        checkinForm.addEventListener('submit', function (e) {
            checkinAssetAc.setHiddenFromInput();
            if (!checkinAssetAc.hidden.value) {
                e.preventDefault();
                showToast('Please select an asset in use from the list.', 'error');
                checkinAssetAc.input.focus();
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
