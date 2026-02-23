<?php
/**
 * ITAM System - Sidebar Navigation
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isAdmin = isAdmin();
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h4><i class="bi bi-box-seam me-2"></i>ITAM System</h4>
        <small><?php echo e(t('app.asset_management')); ?></small>
    </div>

    <nav>
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="/views/<?php echo $isAdmin ? 'admin' : 'user'; ?>/dashboard.php" class="sidebar-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span><?php echo e(t('nav.dashboard')); ?></span>
                </a>
            </li>

            <?php if ($isAdmin): ?>
            <li class="sidebar-item">
                <a href="/views/admin/assets.php" class="sidebar-link <?php echo $currentPage === 'assets' ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i>
                    <span><?php echo e(t('nav.assets')); ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/views/admin/checkout.php" class="sidebar-link <?php echo $currentPage === 'checkout' ? 'active' : ''; ?>">
                    <i class="bi bi-arrow-left-right"></i>
                    <span><?php echo e(t('nav.check_in_out')); ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/views/admin/history.php" class="sidebar-link <?php echo $currentPage === 'history' ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    <span><?php echo e(t('nav.history')); ?></span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/views/admin/users.php" class="sidebar-link <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    <span><?php echo e(t('nav.users')); ?></span>
                </a>
            </li>
            <?php else: ?>
            <li class="sidebar-item">
                <a href="/views/user/myassets.php" class="sidebar-link <?php echo $currentPage === 'myassets' ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i>
                    <span><?php echo e(t('nav.my_assets')); ?></span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <li class="sidebar-item">
                    <a href="/views/admin/reports.php" class="sidebar-link <?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                        <i class="bi bi-file-earmark-text"></i>
                        <span><?php echo e(t('nav.reports')); ?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="/views/admin/profile.php" class="sidebar-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                        <i class="bi bi-person-circle"></i>
                        <span><?php echo e(t('nav.profile')); ?></span>
                    </a>
                </li>
            <?php else: ?>
                <li class="sidebar-item">
                    <a href="/views/user/reports.php" class="sidebar-link <?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                        <i class="bi bi-file-earmark-text"></i>
                        <span><?php echo e(t('nav.reports')); ?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="/views/user/profile.php" class="sidebar-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                        <i class="bi bi-person-circle"></i>
                        <span><?php echo e(t('nav.profile')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="mb-3">
            <a href="/views/auth/logout.php" class="btn btn-m3-tonal-error w-100">
                <i class="bi bi-box-arrow-right me-2"></i><?php echo e(t('nav.logout')); ?>
            </a>
        </div>
        Version 1.0.1<br>
        (c) 2026 P-line Co.
    </div>
</aside>
