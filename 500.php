<?php
$pageTitle = 'Server Error';
require_once __DIR__ . '/includes/header.php';
http_response_code(500);
?>
<div class="container my-5 py-5 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <i class="bi bi-gear-wide-connected text-danger" style="font-size: 5rem;"></i>
                <h1 class="fw-bold mt-3">500</h1>
                <h4 class="fw-bold mb-3">Something Went Wrong</h4>
                <p class="text-muted mb-4">A server-side error occurred while loading this page. Please try again shortly.</p>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-brand px-4 py-2">Try Again</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
