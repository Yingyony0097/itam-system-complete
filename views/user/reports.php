<?php
/**
 * ITAM System - User Reports
 */
require_once __DIR__ . '/../../config/config.php';

requireAuth();

// Keep role-specific URLs clean.
if (isAdmin()) {
    redirect('/views/admin/reports.php');
}

$pageTitle = 'Reports';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
?>

<div class="main-content">
    <header class="top-header">
        <button class="btn btn-icon d-md-none" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
        </div>
    </header>

    <div class="fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title"><?php echo e(tr('Reports')); ?></h1>
                <p class="text-muted mb-0"><?php echo e(tr('Generate and export your personal reports')); ?></p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h5><?php echo e(tr('My Assets Report')); ?></h5>
                    <p class="text-muted mb-4"><?php echo e(tr('Assets currently assigned to your account with assignment and valuation details.')); ?></p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="generateReport('my_assets', 'print')">
                            <i class="bi bi-printer me-1"></i><?php echo e(tr('Print')); ?>
                        </button>
                        <button class="btn btn-outline-success btn-sm flex-fill" onclick="generateReport('my_assets', 'csv')">
                            <i class="bi bi-file-earmark-excel me-1"></i><?php echo e(tr('Excel')); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="report-card">
                    <div class="report-icon" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h5><?php echo e(tr('My Activity Report')); ?></h5>
                    <p class="text-muted mb-4"><?php echo e(tr('Your check-in/check-out history with timestamps and notes.')); ?></p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="generateReport('my_activity', 'print')">
                            <i class="bi bi-printer me-1"></i><?php echo e(tr('Print')); ?>
                        </button>
                        <button class="btn btn-outline-success btn-sm flex-fill" onclick="generateReport('my_activity', 'csv')">
                            <i class="bi bi-file-earmark-excel me-1"></i><?php echo e(tr('Excel')); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card p-4 mt-4">
            <h5 class="mb-4"><?php echo e(tr('Custom Date Range (My Activity)')); ?></h5>
            <form class="row g-3" onsubmit="event.preventDefault(); generateCustomReport();">
                <div class="col-md-3">
                    <label class="form-label"><?php echo e(tr('From Date')); ?></label>
                    <input type="date" class="form-control form-control-glass" id="fromDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?php echo e(tr('To Date')); ?></label>
                    <input type="date" class="form-control form-control-glass" id="toDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?php echo e(tr('Report Type')); ?></label>
                    <select class="form-select form-control-glass" id="actionType">
                        <option value=""><?php echo e(tr('All Activities')); ?></option>
                        <option value="checkout"><?php echo e(tr('Check-outs Only')); ?></option>
                        <option value="checkin"><?php echo e(tr('Check-ins Only')); ?></option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary-gradient w-100">
                        <i class="bi bi-funnel me-2"></i><?php echo e(tr('Generate')); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

function generateReport(type, format) {
    const url = `/views/user/reports_export.php?type=${encodeURIComponent(type)}&format=${encodeURIComponent(format)}`;
    window.open(url, '_blank');
}

function generateCustomReport() {
    let from = document.getElementById('fromDate').value;
    let to = document.getElementById('toDate').value;
    const action = document.getElementById('actionType').value;

    // Treat a single provided date as a 1-day range.
    if (from && !to) to = from;
    if (to && !from) from = to;

    if (!from && !to) {
        showToast('Please select a date range', 'error');
        return;
    }

    if (from && to && from > to) {
        showToast('From Date must be earlier than To Date', 'error');
        return;
    }

    const params = new URLSearchParams();
    params.append('type', 'my_activity');
    params.append('format', 'print');
    if (from) params.append('from', from);
    if (to) params.append('to', to);
    if (action) params.append('action', action);

    const url = `/views/user/reports_export.php?${params.toString()}`;
    window.open(url, '_blank');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
