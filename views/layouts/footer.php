    <?php
    $mainJsFile = __DIR__ . '/../../public/assets/js/main.js';
    $mainJsVersion = file_exists($mainJsFile) ? (string)filemtime($mainJsFile) : APP_VERSION;
    ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!-- Custom JS -->
    <script src="/public/assets/js/main.js?v=<?php echo e($mainJsVersion); ?>"></script>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Display session messages as toasts
        <?php if (isset($_SESSION['success'])): ?>
            showToast('<?php echo e(tr($_SESSION['success'])); ?>', 'success');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            showToast('<?php echo e(tr($_SESSION['error'])); ?>', 'error');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['info'])): ?>
            showToast('<?php echo e(tr($_SESSION['info'])); ?>', 'info');
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
