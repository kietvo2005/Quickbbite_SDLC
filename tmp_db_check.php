<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=food_delivery;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    foreach (['orders', 'order_items'] as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM `$table`");
        $row = $stmt->fetch();
        echo "$table: {$row['c']}\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
