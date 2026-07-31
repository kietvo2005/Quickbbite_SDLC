<?php
/**
 * Admin Reports Page Wrapper
 */
require_once __DIR__ . '/../includes/header.php';
require_admin();
?>
<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="col-lg-9">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-bar-chart-line text-danger me-2"></i>Reports Center</h3>
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <p class="text-muted mb-0">The reports view is now routed to the same admin dashboard analytics workflow.</p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
