<?php
/**
 * Customer Wishlist Page
 * Displays foods the customer has saved for quicker reordering.
 */

$pageTitle = 'Wishlist';
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';

require_customer();

$db = Database::getInstance();
$userId = (int) ($_SESSION['user_id'] ?? 0);

$wishlistItems = $db->queryAll(
    "SELECT f.*, r.`name` AS `restaurant_name`, w.`id` AS `wishlist_id`
     FROM `wishlist` w
     JOIN `foods` f ON w.`food_id` = f.`id`
     JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
     WHERE w.`user_id` = ?
     ORDER BY w.`created_at` DESC",
    [$userId]
);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-5 animate-fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-heart-fill text-danger me-2"></i>Your Wishlist</h3>
        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Continue Shopping
        </a>
    </div>

    <?php if (empty($wishlistItems)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center">
            <div class="mb-3">
                <i class="bi bi-heart text-danger" style="font-size: 3.5rem;"></i>
            </div>
            <h4 class="fw-bold mb-2">Your Wishlist is Empty</h4>
            <p class="text-muted mb-4">Save favorites here for quicker reordering and better customer convenience.</p>
            <div>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-brand px-4 py-2">Explore Foods</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($wishlistItems as $food): ?>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="food-card food-card-clickable" data-detail-url="<?php echo BASE_URL; ?>pages/customer/food-detail.php?id=<?php echo (int) $food['id']; ?>" tabindex="0" role="button" aria-label="View details for <?php echo e($food['name']); ?>">
                        <button type="button" class="wishlist-btn" aria-label="Remove from wishlist" data-food-id="<?php echo (int) $food['id']; ?>">
                            <i class="bi bi-heart-fill text-danger"></i>
                        </button>
                        <div class="food-img-container">
                            <?php $img = image_url($food['image_path'] ?? '', 'assets/images/default_food.jpg'); ?>
                            <img src="<?php echo $img; ?>" alt="<?php echo e($food['name']); ?>" class="food-img">
                        </div>
                        <div class="p-3">
                            <p class="text-muted small mb-1"><i class="bi bi-shop me-1 text-danger"></i><?php echo e($food['restaurant_name']); ?></p>
                            <h5 class="fw-bold mb-2 text-truncate"><?php echo e($food['name']); ?></h5>
                            <p class="text-muted small text-truncate"><?php echo e($food['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="food-price"><?php echo format_currency($food['price']); ?></span>
                                <a href="<?php echo BASE_URL; ?>pages/customer/food-detail.php?id=<?php echo $food['id']; ?>" class="btn btn-sm btn-brand">Order Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const foodId = btn.dataset.foodId;
            if (!foodId) return;

            try {
                const res = await fetch(window.BASE_URL + 'pages/customer/wishlist-toggle.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ food_id: foodId })
                });
                const data = await res.json();

                if (!data.success) {
                    if (res.status === 401) window.location.href = window.BASE_URL + 'login.php';
                    return;
                }

                // On this page, removing = item disappears from the list entirely.
                if (data.action === 'removed') {
                    const card = btn.closest('.col-lg-3, .col-md-6, .col-sm-6');
                    if (card) {
                        card.remove();
                    }
                    // If no items remain, reload to show the empty state.
                    if (!document.querySelector('.wishlist-btn')) {
                        window.location.reload();
                    }
                }
            } catch (err) {
                // Silent fail — user can retry.
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>