<?php
/**
 * Database Setup and Installer Script
 * Run this script to automatically create the database schema, seed categories,
 * restaurants, foods, create default user accounts, and generate placeholder images.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Adjust to your MySQL server password if necessary

// Helper function to create solid color placeholder images using GD
function create_gd_placeholder($path, $width, $height, $text, $bgR = 255, $bgG = 71, $bgB = 87) {
    $dir = dirname($path);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    
    if (extension_loaded('gd')) {
        $img = imagecreate($width, $height);
        $bgColor = imagecolorallocate($img, $bgR, $bgG, $bgB);
        $textColor = imagecolorallocate($img, 255, 255, 255);
        
        // Centering calculations (standard GD fonts 1 to 5)
        $font = 5; 
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
        imagestring($img, $font, $x, $y, $text, $textColor);
        imagejpeg($img, $path, 90);
        imagedestroy($img);
        return true;
    } else {
        // Safe touch fallback if GD is missing
        file_put_contents($path, '');
        return false;
    }
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Food Delivery System Installer</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class='container my-5'>
    <div class='row justify-content-center'>
        <div class='col-md-8'>
            <div class='card p-4'>
                <h2 class='text-center text-primary mb-4'>Database Installer & Seeder</h2>
                <div class='alert alert-info'>This script initializes the <code>food_delivery</code> database and populates dummy data.</div>";

try {
    // 1. Connect to MySQL Server without specifying DB to create it if missing
    $pdo = new PDO("mysql:host=$host", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<p class='text-success'>✔ Successfully connected to the MySQL server.</p>";

    // 2. Create the Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `food_delivery` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "<p class='text-success'>✔ Database <code>food_delivery</code> verified or created successfully.</p>";

    // 3. Connect to the created Database
    $pdo->exec("USE `food_delivery`;");
    echo "<p class='text-success'>✔ Switched to database <code>food_delivery</code>.</p>";

    // 4. Read and Execute schema.sql
    $schemaPath = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Schema file not found at " . $schemaPath);
    }
    
    $sql = file_get_contents($schemaPath);
    // Remove comments
    $sql = preg_replace('/--.*\n/', '', $sql);
    // Split queries by semicolon
    $queries = explode(';', $sql);
    
    $queryCount = 0;
    foreach ($queries as $query) {
        $trimmed = trim($query);
        if ($trimmed !== '') {
            $pdo->exec($trimmed);
            $queryCount++;
        }
    }
    echo "<p class='text-success'>✔ Executed schema queries. Tables created and baseline categories, restaurants and foods seeded successfully.</p>";

    // 5. Add Users: Customer and Admin accounts
    // Clear users to seed the new admin account safely
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE `admins`;");
    $pdo->exec("TRUNCATE TABLE `addresses`;");
    $pdo->exec("TRUNCATE TABLE `users`;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // Customer account
    $cust_pass = password_hash('Customer@123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `password`, `phone`, `address`, `avatar`, `role`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Customer John', 'customer_john', 'customer@fooddelivery.com', $cust_pass, '+1 555-9001', '999 Delivery Way', NULL, 'customer', 'active']);
    $cust_id = $pdo->lastInsertId();
    
    // Add a default address for the customer
    $stmtAddr = $pdo->prepare("INSERT INTO `addresses` (`user_id`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `is_default`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtAddr->execute([$cust_id, '999 Delivery Way', 'Suite 10B', 'Metro City', 'MC State', '90001', 1]);
    
    // Default Admin account
    $admin_pass = password_hash('Admin@12345', PASSWORD_DEFAULT);
    $stmt->execute(['Jane Doe', 'admin_staff', 'admin@fooddelivery.com', $admin_pass, '+1 555-9002', 'Admin Office', NULL, 'admin', 'active']);
    $admin_user_id = $pdo->lastInsertId();

    // Create Admin Profile record
    $stmtAdmin = $pdo->prepare("INSERT INTO `admins` (`user_id`, `full_name`, `role`) VALUES (?, ?, ?)");
    $stmtAdmin->execute([$admin_user_id, 'Jane Doe', 'Super Administrator']);

    // New Request: Create additional admin account (admin@admin.com / 123)
    $admin_pass_new = password_hash('123', PASSWORD_DEFAULT);
    $stmt->execute(['Admin Primary', 'admin_primary', 'admin@admin.com', $admin_pass_new, '+1 555-9003', 'Admin Office', NULL, 'admin', 'active']);
    $admin_id_new = $pdo->lastInsertId();
    $stmtAdmin->execute([$admin_id_new, 'Primary Admin', 'Dashboard Admin']);
    
    // Seed some mock order statuses in the past months to generate dynamic charts data
    $mockOrders = [
        [$cust_id, 34.97, 'delivered', '999 Delivery Way', 'card', 'paid', '2026-05-15 14:30:00'],
        [$cust_id, 12.99, 'delivered', '999 Delivery Way', 'cod', 'paid', '2026-06-10 18:45:00'],
        [$cust_id, 45.47, 'delivered', '999 Delivery Way', 'card', 'paid', '2026-06-25 12:20:00'],
        [$cust_id, 28.50, 'delivered', '999 Delivery Way', 'cod', 'paid', '2026-07-02 20:05:00'],
        [$cust_id, 16.99, 'preparing', '999 Delivery Way', 'cod', 'pending', '2026-07-23 11:30:00'],
        [$cust_id, 55.40, 'pending', '999 Delivery Way', 'card', 'paid', '2026-07-23 12:10:00'],
        [$cust_id, 22.98, 'cancelled', '999 Delivery Way', 'cod', 'failed', '2026-07-20 15:40:00']
    ];

    $stmtOrder = $pdo->prepare("INSERT INTO `orders` (`user_id`, `total_amount`, `status`, `delivery_address`, `payment_method`, `payment_status`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtItem = $pdo->prepare("INSERT INTO `order_items` (`order_id`, `food_id`, `quantity`, `price`, `created_at`) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($mockOrders as $mo) {
        $stmtOrder->execute([$mo[0], $mo[1], $mo[2], $mo[3], $mo[4], $mo[5], $mo[6]]);
        $order_id = $pdo->lastInsertId();
        
        // Add random mock items for stats (referencing Cheeseburger id=1 or Pepperoni Pizza id=5)
        $stmtItem->execute([$order_id, 1, 2, 8.99, $mo[6]]);
        $stmtItem->execute([$order_id, 5, 1, 14.99, $mo[6]]);
    }
    
    echo "<p class='text-success'>✔ Seeded user accounts and historical orders data.</p>";
    echo "<ul>
            <li><strong>Customer Role:</strong> Email: <code>customer@fooddelivery.com</code> / Password: <code>Customer@123</code></li>
            <li><strong>Admin Role:</strong> Email: <code>admin@fooddelivery.com</code> / Password: <code>Admin@12345</code></li>
            <li><strong>New Primary Admin:</strong> Email: <code>admin@admin.com</code> / Password: <code>123</code></li>
          </ul>";

    // 6. Generate Placeholder Images
    echo "<p class='text-info'>⏳ Generating local image assets...</p>";
    $basePath = __DIR__ . '/';
    
    // Categories
    create_gd_placeholder($basePath . 'assets/images/cat_burgers.jpg', 300, 200, 'Burgers Cat', 255, 71, 87);
    create_gd_placeholder($basePath . 'assets/images/cat_pizza.jpg', 300, 200, 'Pizza Cat', 46, 204, 113);
    create_gd_placeholder($basePath . 'assets/images/cat_asian.jpg', 300, 200, 'Sushi Cat', 155, 89, 182);
    create_gd_placeholder($basePath . 'assets/images/cat_desserts.jpg', 300, 200, 'Desserts Cat', 241, 196, 15);
    create_gd_placeholder($basePath . 'assets/images/cat_drinks.jpg', 300, 200, 'Drinks Cat', 52, 152, 219);

    // Restaurants
    create_gd_placeholder($basePath . 'assets/images/rest_burgerking.jpg', 400, 250, 'Burger King Deluxe', 47, 53, 66);
    create_gd_placeholder($basePath . 'assets/images/rest_bellaitalia.jpg', 400, 250, 'Bella Italia Pizza', 47, 53, 66);
    create_gd_placeholder($basePath . 'assets/images/rest_sakurazen.jpg', 400, 250, 'Sakura Zen Sushi', 47, 53, 66);
    create_gd_placeholder($basePath . 'assets/images/rest_sweettooth.jpg', 400, 250, 'Sweet Tooth Treats', 47, 53, 66);

    // Foods
    // Skip cheeseburger.jpg if it exists to preserve our generated picture
    if (!file_exists($basePath . 'assets/images/food_cheeseburger.jpg')) {
        create_gd_placeholder($basePath . 'assets/images/food_cheeseburger.jpg', 400, 300, 'Cheeseburger', 235, 77, 75);
    }
    create_gd_placeholder($basePath . 'assets/images/food_zinger.jpg', 400, 300, 'Spicy Zinger', 235, 77, 75);
    create_gd_placeholder($basePath . 'assets/images/food_shake.jpg', 400, 300, 'Chocolate Shake', 235, 77, 75);
    create_gd_placeholder($basePath . 'assets/images/food_margherita.jpg', 400, 300, 'Pizza Margherita', 30, 144, 255);
    create_gd_placeholder($basePath . 'assets/images/food_pepperoni.jpg', 400, 300, 'Pizza Pepperoni', 30, 144, 255);
    create_gd_placeholder($basePath . 'assets/images/food_sushi.jpg', 400, 300, 'Salmon Sushi Box', 142, 68, 173);
    create_gd_placeholder($basePath . 'assets/images/food_ramen.jpg', 400, 300, 'Tonkotsu Ramen Bowl', 142, 68, 173);
    create_gd_placeholder($basePath . 'assets/images/food_waffle.jpg', 400, 300, 'Strawberry Waffle', 243, 156, 18);
    create_gd_placeholder($basePath . 'assets/images/food_lavacake.jpg', 400, 300, 'Molten Lava Cake', 243, 156, 18);

    // Default Fallbacks
    create_gd_placeholder($basePath . 'assets/images/default_food.jpg', 400, 300, 'Delicious Food Item', 149, 165, 166);
    create_gd_placeholder($basePath . 'assets/images/default_restaurant.jpg', 400, 250, 'Restaurant Kitchen', 149, 165, 166);
    create_gd_placeholder($basePath . 'assets/images/default_avatar.jpg', 150, 150, 'Profile Photo', 127, 140, 141);

    echo "<p class='text-success'>✔ Successfully generated local image assets.</p>";
    echo "<div class='alert alert-success text-center mt-4'>Installation Completed Successfully! <br><a href='index.php' class='btn btn-primary mt-2'>Go to Homepage</a></div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger mt-4'>
            <h4>Installation Failed!</h4>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

echo "        </div>
    </div>
</div>
</body>
</html>";
