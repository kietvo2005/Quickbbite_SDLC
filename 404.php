<?php
$pageTitle = 'Page Not Found';
require_once __DIR__ . '/includes/header.php';
http_response_code(404);
?>
<div class="container my-5 py-5 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <i class="bi bi-exclamation-circle text-danger" style="font-size: 5rem;"></i>
                <h1 class="fw-bold mt-3">404</h1>
                <h4 class="fw-bold mb-3">Page Not Found</h4>
                <p class="text-muted mb-4">The page you requested could not be located. Please check the URL or return to the homepage.</p>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-brand px-4 py-2">Go to Home</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
