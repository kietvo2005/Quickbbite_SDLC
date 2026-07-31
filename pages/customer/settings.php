<?php
/**
 * Customer Settings Page
 * Provides lightweight account preference controls for the current customer account.
 */

$pageTitle = 'Settings';
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';

require_customer();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_preferences') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error: CSRF verification failed.');
    }

    $_SESSION['theme'] = isset($_POST['dark_mode']) ? 'dark' : 'light';
    $_SESSION['language'] = in_array($_POST['language'] ?? '', ['en', 'vi'], true) ? $_POST['language'] : 'en';
    $_SESSION['notifications'] = isset($_POST['notifications']) ? 'enabled' : 'disabled';

    set_flash('success', 'Your preferences were updated successfully.');
    redirect(BASE_URL . 'pages/customer/settings.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h3 class="fw-bold mb-4"><i class="bi bi-gear text-danger me-2"></i><?php echo __('settings_title'); ?></h3>

                <form action="settings.php" method="POST" class="row g-3">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="update_preferences">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><?php echo __('settings_dark_mode'); ?></label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="dark_mode" id="dark_mode" value="1" <?php echo (($_SESSION['theme'] ?? 'light') === 'dark') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="dark_mode"><?php echo __('settings_enable_dark_mode'); ?></label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="language" class="form-label fw-semibold"><?php echo __('settings_language'); ?></label>
                        <select name="language" id="language" class="form-select">
                            <option value="en" <?php echo (($_SESSION['language'] ?? 'en') === 'en') ? 'selected' : ''; ?>>English</option>
                            <option value="vi" <?php echo (($_SESSION['language'] ?? 'en') === 'vi') ? 'selected' : ''; ?>>Tiếng Việt</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold"><?php echo __('settings_notifications'); ?></label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notifications" id="notifications" value="1" <?php echo (($_SESSION['notifications'] ?? 'enabled') === 'enabled') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="notifications"><?php echo __('settings_receive_updates'); ?></label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-brand px-4 py-2"><?php echo __('settings_save'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const darkModeToggle = document.getElementById('dark_mode');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', () => {
            document.documentElement.classList.toggle('dark-mode', darkModeToggle.checked);
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>