<?php
$db = new mysqli('localhost', 'root', '', 'food_delivery');
if ($db->connect_error) {
    die('DB_ERROR: ' . $db->connect_error);
}

$tables = ['categories', 'restaurants', 'foods'];
foreach ($tables as $table) {
    $res = $db->query("SELECT COUNT(*) AS c FROM `$table`");
    echo $table . '=' . $res->fetch_assoc()['c'] . PHP_EOL;
}

$res = $db->query("SELECT COUNT(*) AS c FROM foods WHERE is_popular = 1");
echo 'popular=' . $res->fetch_assoc()['c'] . PHP_EOL;
$res = $db->query("SELECT COUNT(*) AS c FROM foods WHERE is_latest = 1");
echo 'latest=' . $res->fetch_assoc()['c'] . PHP_EOL;
$res = $db->query("SELECT COUNT(*) AS c FROM foods WHERE is_available = 1");
echo 'available=' . $res->fetch_assoc()['c'] . PHP_EOL;
