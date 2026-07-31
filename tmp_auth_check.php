<?php
require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/functions/helpers.php';
require_once __DIR__ . '/includes/functions/auth.php';

if (function_exists('require_customer')) {
    echo "OK: require_customer exists\n";
} else {
    echo "MISSING: require_customer does not exist\n";
}
