<?php
/**
 * Quick theme toggle handler — flips light/dark mode and redirects back.
 */
require_once __DIR__ . '/includes/config/config.php';

$_SESSION['theme'] = (($_SESSION['theme'] ?? 'light') === 'dark') ? 'light' : 'dark';

$referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php';
header('Location: ' . $referer);
exit;