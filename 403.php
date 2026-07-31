<?php
$pageTitle = 'Access Denied';
require_once __DIR__ . '/includes/header.php';
http_response_code(403);
?>
<div class="container my-5 py-5 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <i class="bi bi-shield-lock-fill text-warning" style="font-size: 5rem;"></i>
                <h1 class="fw-bold mt-3">403</h1>
                <h4 class="fw-bold mb-3">Access Denied</h4>
                <p class="text-muted mb-4">You do not have permission to view this page. Please sign in with the correct account role.</p>
                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-brand px-4 py-2">Login</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
