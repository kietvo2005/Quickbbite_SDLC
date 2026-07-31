<?php
/**
 * Restaurant Menu & Review Page - Food Delivery System
 * Lists restaurant profiles, menu dishes, and ratings with submission handles.
 */

$pageTitle = "Restaurant Menu";
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance();

$restaurantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($restaurantId <= 0) {
    set_flash('danger', 'Invalid Restaurant Selection.');
    redirect(BASE_URL . 'restaurant.php');
}

// Fetch Restaurant Info
$restaurant = $db->queryRow("SELECT * FROM `restaurants` WHERE `id` = ? AND `status` = 'active'", [$restaurantId]);
if (!$restaurant) {
    set_flash('danger', 'Restaurant not found or is currently inactive.');
    redirect(BASE_URL . 'restaurant.php');
}

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    require_login();
    
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = sanitize_input($_POST['comment'] ?? '');
    
    $errors = [];
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a rating score between 1 and 5 stars.";
    }
    
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $db->execute("INSERT INTO `reviews` (`user_id`, `restaurant_id`, `rating`, `comment`) VALUES (?, ?, ?, ?)", [
            $user_id, $restaurantId, $rating, $comment
        ]);
        set_flash('success', 'Your review has been published successfully.');
        redirect(BASE_URL . 'restaurant-menu.php?id=' . $restaurantId);
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}

// Fetch menu food items
$foods = $db->queryAll("SELECT f.*, c.`name` AS `category_name` 
                        FROM `foods` f
                        JOIN `categories` c ON f.`category_id` = c.`id`
                        WHERE f.`restaurant_id` = ? AND f.`is_available` = 1
                        ORDER BY c.`id` ASC, f.`name` ASC", [$restaurantId]);

// Fetch reviews
$reviews = $db->queryAll("SELECT r.*, u.`username`, u.`avatar`
                          FROM `reviews` r
                          JOIN `users` u ON r.`user_id` = u.`id`
                          WHERE r.`restaurant_id` = ?
                          ORDER BY r.`created_at` DESC", [$restaurantId]);

// Calculate Average Rating
$avgRatingRow = $db->queryRow("SELECT AVG(`rating`) as `avg`, COUNT(`id`) as `count` FROM `reviews` WHERE `restaurant_id` = ?", [$restaurantId]);
$avgRating = $avgRatingRow['avg'] ? round($avgRatingRow['avg'], 1) : 0.0;
$ratingCount = $avgRatingRow['count'] ?? 0;
?>

<!-- Restaurant Header Banner -->
<div class="bg-dark text-white py-5 mb-5" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.8)), url('<?php echo image_url($restaurant['image_path'] ?? '', 'assets/images/default_restaurant.jpg'); ?>') center center / cover no-repeat;">
    <div class="container py-3 text-center">
        <h1 class="display-4 fw-bold animate-fade-in-up"><?php echo e($restaurant['name']); ?></h1>
        <p class="lead text-white-50 mb-3"><?php echo e($restaurant['cuisine_type']); ?> Cuisine &bull; <?php echo e($restaurant['address']); ?></p>
        
        <div class="d-flex justify-content-center align-items-center gap-3">
            <span class="badge bg-warning text-dark fs-6 py-2 px-3">
                <i class="bi bi-star-fill me-1"></i><?php echo $avgRating > 0 ? $avgRating . ' / 5.0' : 'No Ratings'; ?> (<?php echo $ratingCount; ?> reviews)
            </span>
            <span class="badge bg-danger fs-6 py-2 px-3">
                <i class="bi bi-telephone-fill me-1"></i><?php echo e($restaurant['phone']); ?>
            </span>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row g-5">
        
        <!-- Left Column: Menu Items List -->
        <div class="col-lg-8">
            <h3 class="fw-bold mb-4"><i class="bi bi-journal-richtext text-danger me-2"></i>Restaurant Menu</h3>
            
            <?php if (empty($foods)): ?>
                <div class="alert alert-warning">This restaurant has not published any dishes yet.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($foods as $food): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden food-card">
                                <div style="height: 160px; overflow: hidden; position: relative;">
                                    <?php 
                                    $foodImg = image_url($food['image_path'] ?? '', 'assets/images/default_food.jpg');
                                    ?>
                                    <img src="<?php echo $foodImg; ?>" alt="<?php echo e($food['name']); ?>" class="w-100 h-100 food-img" style="object-fit: cover;">
                                    <span class="badge bg-light text-dark position-absolute top-2 start-2" style="font-size: 0.75rem; border-radius: 20px;">
                                        <?php echo e($food['category_name']); ?>
                                    </span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark"><?php echo e($food['name']); ?></h5>
                                        <p class="text-muted small mb-3 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo e($food['description']); ?>
                                        </p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="food-price fs-5"><?php echo format_currency($food['price']); ?></span>
                                        <a href="food-detail.php?id=<?php echo $food['id']; ?>" class="btn btn-brand btn-sm">Add to Order</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Column: Ratings & Reviews -->
        <div class="col-lg-4">
            <h4 class="fw-bold mb-4"><i class="bi bi-chat-heart text-danger me-2"></i>Reviews & Feedback</h4>
            
            <!-- Submit Review Form -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <h5 class="fw-bold mb-3">Write a Review</h5>
                
                <?php if (is_logged_in()): ?>
                    <form action="restaurant-menu.php?id=<?php echo $restaurantId; ?>" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="action" value="submit_review">
                        
                        <div class="mb-3">
                            <label for="rating" class="form-label fw-semibold">Rating</label>
                            <select name="rating" id="rating" class="form-select" required>
                                <option value="" disabled selected>Select star rating...</option>
                                <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (5/5 Excellence)</option>
                                <option value="4">&#9733;&#9733;&#9733;&#9733; (4/5 Good)</option>
                                <option value="3">&#9733;&#9733;&#9733; (3/5 Average)</option>
                                <option value="2">&#9733;&#9733; (2/5 Fair)</option>
                                <option value="1">&#9733; (1/5 Poor)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comment" class="form-label fw-semibold">Review Comment</label>
                            <textarea name="comment" id="comment" rows="3" class="form-control" placeholder="Share your experience dining or ordering..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-brand btn-sm w-100 py-2">Publish Review</button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-2">
                        <p class="text-muted small">You must be logged in to leave reviews.</p>
                        <a href="login.php" class="btn btn-sm btn-outline-dark rounded-pill px-3">Login to Review</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- List Reviews -->
            <div class="reviews-list">
                <?php if (empty($reviews)): ?>
                    <p class="text-muted text-center small py-3">No reviews have been published for this outlet yet.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="bg-white p-3 rounded-4 shadow-sm mb-3 border-start border-danger border-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <?php 
                                    $av = image_url(!empty($rev['avatar']) ? 'uploads/avatars/' . $rev['avatar'] : 'assets/images/default_avatar.jpg');
                                    ?>
                                    <img src="<?php echo $av; ?>" alt="Avatar" class="rounded-circle" style="width: 25px; height: 25px; object-fit: cover;">
                                    <span class="fw-semibold small"><?php echo e($rev['username']); ?></span>
                                </div>
                                <span class="text-warning small">
                                    <?php echo str_repeat('&#9733;', $rev['rating']); ?><?php echo str_repeat('&#9734;', 5 - $rev['rating']); ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-1"><?php echo e($rev['comment']); ?></p>
                            <span class="text-muted" style="font-size: 0.7rem;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
