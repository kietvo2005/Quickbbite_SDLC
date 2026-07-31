<?php
require_once __DIR__ . '/includes/config/config.php';
echo '<pre>';
var_dump($_SESSION['language'] ?? 'NOT SET');
var_dump(load_language_dictionary());
echo '</pre>';