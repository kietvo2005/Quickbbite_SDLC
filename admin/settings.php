<?php
/**
 * Admin Settings Page
 */
$pageTitle = 'Admin Settings';
require_once __DIR__ . '/../includes/header.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_preferences') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error: CSRF verification failed.');
    }

    $_SESSION['theme'] = isset($_POST['dark_mode']) ? 'dark' : 'light';
    $_SESSION['language'] = sanitize_input($_POST['language'] ?? 'en');
    $_SESSION['notifications'] = isset($_POST['notifications']) ? 'enabled' : 'disabled';

    set_flash('success', 'Admin preferences updated successfully.');
    redirect(BASE_URL . 'admin/settings.php');
}
?>
<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="col-lg-9">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-gear text-danger me-2"></i>System Settings</h3>
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <form action="settings.php" method="POST" class="row g-3">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="update_preferences">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Dark Mode</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="dark_mode" id="dark_mode" value="1" <?php echo (($_SESSION['theme'] ?? 'light') === 'dark') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="dark_mode">Enable dark mode</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="language" class="form-label fw-semibold">Language</label>
                        <select name="language" id="language" class="form-select">
                            <option value="en" <?php echo (($_SESSION['language'] ?? 'en') === 'en') ? 'selected' : ''; ?>>English</option>
                            <option value="fr" <?php echo (($_SESSION['language'] ?? 'en') === 'fr') ? 'selected' : ''; ?>>French</option>
                            <option value="es" <?php echo (($_SESSION['language'] ?? 'en') === 'es') ? 'selected' : ''; ?>>Spanish</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Notification Preferences</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notifications" id="notifications" value="1" <?php echo (($_SESSION['notifications'] ?? 'enabled') === 'enabled') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="notifications">Receive order and system updates</label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-brand px-4 py-2">Save Preferences</button>
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
