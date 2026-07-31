<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=food_delivery;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $tables = ['orders', 'order_items', 'users', 'foods'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM `$table`");
        $row = $stmt->fetch();
        echo "$table: {$row['c']}\n";
    }

    echo "\nCustomers:\n";
    $stmt = $pdo->query("SELECT id, username, email, role FROM users ORDER BY role, id ASC LIMIT 10");
    foreach ($stmt->fetchAll() as $user) {
        echo "{$user['id']} - {$user['username']} ({$user['email']}) role={$user['role']}\n";
    }

    echo "\nFoods:\n";
    $stmt = $pdo->query("SELECT id, name, price, restaurant_id FROM foods ORDER BY id ASC LIMIT 20");
    foreach ($stmt->fetchAll() as $food) {
        echo "{$food['id']} - {$food['name']} ({$food['price']}) restaurant_id={$food['restaurant_id']}\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
