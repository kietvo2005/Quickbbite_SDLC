<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('localhost', 'root', '', 'food_delivery');

$counts = [
  'categories' => $db->query('SELECT COUNT(*) AS c FROM categories')->fetch_assoc()['c'],
  'restaurants' => $db->query('SELECT COUNT(*) AS c FROM restaurants')->fetch_assoc()['c'],
  'foods' => $db->query('SELECT COUNT(*) AS c FROM foods')->fetch_assoc()['c'],
];

$dupes = $db->query("SELECT name, COUNT(*) AS c FROM foods GROUP BY name HAVING COUNT(*) > 1");
$duplicateFoods = [];
while ($row = $dupes->fetch_assoc()) { $duplicateFoods[] = $row['name']; }

$missingImages = [];
$foods = $db->query("SELECT id, name, image_path FROM foods WHERE image_path IS NOT NULL AND image_path != ''");
while ($row = $foods->fetch_assoc()) {
  $fullPath = __DIR__ . '/../' . ltrim($row['image_path'], '/');
  if (!file_exists($fullPath)) {
    $missingImages[] = $row['name'] . ' => ' . $row['image_path'];
  }
}

$restaurants = $db->query("SELECT name, image_path FROM restaurants WHERE image_path IS NOT NULL AND image_path != ''");
while ($row = $restaurants->fetch_assoc()) {
  $fullPath = __DIR__ . '/../' . ltrim($row['image_path'], '/');
  if (!file_exists($fullPath)) {
    $missingImages[] = $row['name'] . ' => ' . $row['image_path'];
  }
}

$popular = $db->query("SELECT COUNT(*) AS c FROM foods WHERE is_popular = 1")->fetch_assoc()['c'];
$latest = $db->query("SELECT COUNT(*) AS c FROM foods WHERE is_latest = 1")->fetch_assoc()['c'];
$available = $db->query("SELECT COUNT(*) AS c FROM foods WHERE is_available = 1")->fetch_assoc()['c'];

$sample = $db->query("SELECT f.name, r.name AS restaurant_name, c.name AS category_name FROM foods f JOIN restaurants r ON r.id = f.restaurant_id JOIN categories c ON c.id = f.category_id ORDER BY f.id LIMIT 5");

echo "COUNTS\n";
foreach ($counts as $k => $v) { echo $k . '=' . $v . PHP_EOL; }

echo "POPULAR=$popular\nLATEST=$latest\nAVAILABLE=$available\n";
echo "DUPLICATE_FOODS=" . (empty($duplicateFoods) ? 'none' : implode(', ', $duplicateFoods)) . "\n";
echo "MISSING_IMAGES=" . (empty($missingImages) ? 'none' : implode(' | ', $missingImages)) . "\n";
echo "SAMPLE_ROWS\n";
while ($row = $sample->fetch_assoc()) {
  echo $row['name'] . ' | ' . $row['restaurant_name'] . ' | ' . $row['category_name'] . PHP_EOL;
}
