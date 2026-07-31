<?php
/**
 * Review Moderator - Food Delivery System Admin Panel
 * Lists and moderates customer feedback scores and reviews.
 */

$pageTitle = "Manage Reviews";
require_once __DIR__ . '/../../includes/header.php';

// Guards
require_admin();

$db = Database::getInstance();

// Handle deletion (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
    $db->execute("DELETE FROM `reviews` WHERE `id` = ?", [$targetId]);
    set_flash('info', 'Customer feedback review deleted successfully.');
    redirect(BASE_URL . 'pages/admin/reviews.php');
}

// Fetch reviews list
$sql = "SELECT r.*, u.`username`, u.`email`, rs.`name` AS `restaurant_name`
        FROM `reviews` r
        JOIN `users` u ON r.`user_id` = u.`id`
        JOIN `restaurants` rs ON r.`restaurant_id` = rs.`id`
        ORDER BY r.`id` DESC";
$reviews = $db->queryAll($sql);
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <!-- Review moderator panel -->
        <div class="col-lg-9">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-star text-danger me-2"></i>Moderate Reviews</h3>
            
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <?php if (empty($reviews)): ?>
                    <p class="text-muted small">No reviews have been posted to restaurants yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-muted small">
                                <tr>
                                    <th scope="col">User</th>
                                    <th scope="col">Restaurant</th>
                                    <th scope="col">Rating</th>
                                    <th scope="col">Comments</th>
                                    <th scope="col">Date Posted</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviews as $rev): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark d-block small"><?php echo e($rev['username']); ?></span>
                                            <span class="text-muted text-xs"><?php echo e($rev['email']); ?></span>
                                        </td>
                                        <td class="small fw-semibold"><?php echo e($rev['restaurant_name']); ?></td>
                                        <td class="text-warning small">
                                            <?php echo str_repeat('&#9733;', $rev['rating']); ?><?php echo str_repeat('&#9734;', 5 - $rev['rating']); ?>
                                        </td>
                                        <td class="small text-muted" style="max-width: 250px; font-style: italic;">
                                            "<?php echo e($rev['comment']); ?>"
                                        </td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></td>
                                        <td class="text-end">
                                            <a href="reviews.php?action=delete&id=<?php echo $rev['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanently delete this review from the system?');">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
