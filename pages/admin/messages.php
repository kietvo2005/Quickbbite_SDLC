<?php
/**
 * Support query Manager - Food Delivery System Admin Panel
 * Manages queries submitted through the customer contact page form.
 */

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';

// Guards
require_admin();

$pageTitle = "Enquiries Inbox";
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();

// 1. Handle Status Toggles & Deletions (GET Action Handles)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'mark_read') {
        $db->execute("UPDATE `contact_messages` SET `status` = 'read' WHERE `id` = ?", [$targetId]);
        set_flash('success', 'Support enquiry marked as read.');
    } elseif ($action === 'mark_replied') {
        $db->execute("UPDATE `contact_messages` SET `status` = 'replied' WHERE `id` = ?", [$targetId]);
        set_flash('success', 'Support enquiry marked as resolved/replied.');
    } elseif ($action === 'delete') {
        $db->execute("DELETE FROM `contact_messages` WHERE `id` = ?", [$targetId]);
        set_flash('info', 'Support query deleted from inbox.');
    }
    
    redirect(BASE_URL . 'pages/admin/messages.php');
}

// Fetch messages list
$messages = $db->queryAll("SELECT * FROM `contact_messages` ORDER BY `status` ASC, `id` DESC");
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <!-- Messages Inbox panel -->
        <div class="col-lg-9">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-chat-left-text text-danger me-2"></i>Customer Support Inbox</h3>
            
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <?php if (empty($messages)): ?>
                    <p class="text-muted small">No support queries are currently in the inbox.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-muted small">
                                <tr>
                                    <th scope="col">Sender</th>
                                    <th scope="col">Subject</th>
                                    <th scope="col">Message Query</th>
                                    <th scope="col">Fulfillment Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                    <tr class="<?php echo $msg['status'] === 'unread' ? 'table-warning fw-semibold' : ''; ?>">
                                        <td>
                                            <span class="text-dark d-block small"><?php echo e($msg['name']); ?></span>
                                            <span class="text-muted text-xs"><?php echo e($msg['email']); ?></span>
                                            <span class="text-muted text-xxs d-block"><?php echo date('M d, h:i A', strtotime($msg['created_at'])); ?></span>
                                        </td>
                                        <td class="small"><?php echo e($msg['subject']); ?></td>
                                        <td class="small text-muted" style="max-width: 250px;">
                                            <?php echo e($msg['message']); ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $st = $msg['status'];
                                            $badge = 'bg-warning text-dark';
                                            if ($st === 'read') $badge = 'bg-info text-dark';
                                            elseif ($st === 'replied') $badge = 'bg-success';
                                            ?>
                                            <span class="badge rounded-pill <?php echo $badge; ?> text-uppercase small" style="font-size: 0.7rem;"><?php echo $st; ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Action
                                                </button>
                                                <ul class="dropdown-menu shadow border-0 p-1">
                                                    <li><a class="dropdown-item rounded small" href="messages.php?action=mark_read&id=<?php echo $msg['id']; ?>">Mark as Read</a></li>
                                                    <li><a class="dropdown-item rounded small" href="messages.php?action=mark_replied&id=<?php echo $msg['id']; ?>">Mark as Replied</a></li>
                                                </ul>
                                            </div>
                                            <a href="messages.php?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Permanently delete support ticket?');">
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
