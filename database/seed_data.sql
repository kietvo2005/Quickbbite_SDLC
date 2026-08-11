USE `food_delivery`;

INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`)
SELECT 'Fast Food', 'Crispy fries, juicy burgers, wraps, and loaded comfort bites.', 'bi-burger', 'uploads/categories/fast_food.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` = 'Fast Food');

INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`)
SELECT 'Japanese', 'Fresh sushi, ramen, curries, and polished Japanese comfort food.', 'bi-cup-hot', 'uploads/categories/japanese.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` = 'Japanese');

INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`)
SELECT 'Korean', 'Bold flavors, sizzling grills, and spicy rice dishes.', 'bi-fire', 'uploads/categories/korean.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` = 'Korean');

INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`)
SELECT 'Chinese', 'Wok-fried noodles, rich soups, and savory dim sum favorites.', 'bi-bowl', 'uploads/categories/chinese.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` = 'Chinese');

INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`)
SELECT 'Desserts', 'Sweet finales, cakes, waffles, and chilled indulgences.', 'bi-cake', 'uploads/categories/desserts.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` = 'Desserts');

INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`)
SELECT 'Drinks', 'Refreshing beverages, lattes, cold brews, and smoothies.', 'bi-cup-straw', 'uploads/categories/drinks.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` = 'Drinks');

INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`)
SELECT 'Pizza', 'Wood-fired pizzas with rich toppings and golden crusts.', 'bi-pizza-slice', 'uploads/categories/pizza.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` = 'Pizza');

INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`)
SELECT 'Healthy Food', 'Bright bowls, wholesome grains, and nourishing salads.', 'bi-flower1', 'uploads/categories/healthy_food.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` = 'Healthy Food');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Sakura Zen', 'Fresh sushi, ramen, and Japanese comfort classics served with care.', 'Japanese', '123 Blossom Street, District 1', '+84 28 1234 5678', 'uploads/restaurants/sakura_zen.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Sakura Zen');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Sweet Tooth Treats', 'Artisan desserts, fluffy waffles, and indulgent cakes made fresh daily.', 'Desserts', '45 Sugar Lane, District 3', '+84 28 2345 6789', 'uploads/restaurants/sweet_tooth_treats.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Sweet Tooth Treats');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Burger House', 'Bold burgers, crispy sides, and satisfying comfort food.', 'Fast Food', '88 Grill Avenue, District 7', '+84 28 3456 7890', 'uploads/restaurants/burger_house.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Burger House');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Pizza Corner', 'Wood-fired pizzas with rich toppings and a perfectly crisp crust.', 'Pizza', '21 Pepper Road, District 5', '+84 28 4567 8901', 'uploads/restaurants/pizza_corner.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Pizza Corner');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Korean BBQ', 'Sizzling grilled meats, kimchi, and signature Korean spice.', 'Korean', '11 Seoul Street, District 2', '+84 28 5678 9012', 'uploads/restaurants/korean_bbq.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Korean BBQ');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Thai Express', 'Fast, fragrant, and flavorful Thai dishes for every craving.', 'Chinese', '76 Spice Boulevard, District 4', '+84 28 6789 0123', 'uploads/restaurants/thai_express.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Thai Express');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Italian Bistro', 'Classic pasta, flatbreads, and comforting Italian favorites.', 'Pizza', '33 Tuscany Lane, District 6', '+84 28 7890 1234', 'uploads/restaurants/italian_bistro.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Italian Bistro');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Healthy Bowl', 'Wholesome grain bowls, fresh salads, and nutrient-rich plates.', 'Healthy Food', '99 Green Avenue, District 8', '+84 28 8901 2345', 'uploads/restaurants/healthy_bowl.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Healthy Bowl');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Coffee & Dessert', 'Specialty coffee, chilled drinks, and handcrafted sweets.', 'Drinks', '60 Morning Street, District 9', '+84 28 9012 3456', 'uploads/restaurants/coffee_and_dessert.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Coffee & Dessert');

INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`)
SELECT 'Seafood Paradise', 'Ocean-fresh seafood dishes prepared with bold coastal flavors.', 'Seafood', '12 Harbor Road, District 10', '+84 28 0123 4567', 'uploads/restaurants/seafood_paradise.jpg', 'active'
WHERE NOT EXISTS (SELECT 1 FROM `restaurants` WHERE `name` = 'Seafood Paradise');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Sakura Zen'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Japanese'),
  'Tonkotsu Ramen Bowl',
  'Rich pork broth with chashu, soft-boiled egg, and spring onions.',
  13.99,
  'uploads/foods/tonkotsu_ramen.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Tonkotsu Ramen Bowl');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Sakura Zen'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Japanese'),
  'Salmon Sushi Box',
  'Fresh Atlantic salmon nigiri with seasoned rice and crisp cucumber.',
  16.50,
  'uploads/foods/salmon_sushi_box.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Salmon Sushi Box');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Sakura Zen'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Japanese'),
  'Chicken Katsu Curry',
  'Crispy chicken cutlet served over steamed rice with a mild curry sauce.',
  14.75,
  'uploads/foods/chicken_katsu_curry.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Chicken Katsu Curry');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Sweet Tooth Treats'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Desserts'),
  'Strawberry Waffle Fantasy',
  'Fluffy Belgian waffle layered with strawberry compote and whipped cream.',
  7.99,
  'uploads/foods/strawberry_waffle_fantasy.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Strawberry Waffle Fantasy');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Sweet Tooth Treats'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Desserts'),
  'Molten Lava Cake',
  'Warm chocolate sponge with a molten center and vanilla bean cream.',
  6.50,
  'uploads/foods/molten_lava_cake.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Molten Lava Cake');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Sweet Tooth Treats'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Desserts'),
  'Oreo Cheesecake Slice',
  'Creamy cheesecake with a chocolate cookie crust and fresh berry garnish.',
  5.95,
  'uploads/foods/oreo_cheesecake_slice.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Oreo Cheesecake Slice');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Burger House'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Fast Food'),
  'Smash Classic Burger',
  'Double-seared beef patty with cheddar, pickles, onion, and house sauce.',
  9.90,
  'uploads/foods/smash_classic_burger.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Smash Classic Burger');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Burger House'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Fast Food'),
  'BBQ Chicken Wrap',
  'Grilled chicken, lettuce, tomato, and smoky barbecue sauce wrapped in warm tortilla.',
  8.75,
  'uploads/foods/bbq_chicken_wrap.jpg',
  1, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'BBQ Chicken Wrap');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Burger House'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Fast Food'),
  'Loaded Fries Basket',
  'Golden fries topped with cheddar sauce, bacon bits, and green onions.',
  5.60,
  'uploads/foods/loaded_fries_basket.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Loaded Fries Basket');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Pizza Corner'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Pizza'),
  'Truffle Mushroom Pizza',
  'Creamy mozzarella, wild mushrooms, and truffle oil on a crisp crust.',
  14.80,
  'uploads/foods/truffle_mushroom_pizza.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Truffle Mushroom Pizza');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Pizza Corner'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Pizza'),
  'Pepperoni Fire Pizza',
  'A bold pizza loaded with spicy pepperoni and bubbling mozzarella.',
  15.40,
  'uploads/foods/pepperoni_fire_pizza.jpg',
  1, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Pepperoni Fire Pizza');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Pizza Corner'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Pizza'),
  'Garden Veggie Pizza',
  'Roasted vegetables, basil pesto, and mozzarella on a golden crust.',
  13.20,
  'uploads/foods/garden_veggie_pizza.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Garden Veggie Pizza');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Korean BBQ'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Korean'),
  'Gochujang Beef Bowl',
  'Tender beef glazed with gochujang sauce and served with rice and greens.',
  15.20,
  'uploads/foods/gochujang_beef_bowl.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Gochujang Beef Bowl');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Korean BBQ'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Korean'),
  'Spicy Chicken Skewers',
  'Well-charred chicken skewers with a sweet and spicy glaze.',
  12.90,
  'uploads/foods/spicy_chicken_skewers.jpg',
  1, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Spicy Chicken Skewers');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Korean BBQ'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Korean'),
  'Kimchi Fried Rice',
  'Fragrant fried rice with tangy kimchi, vegetables, and a sunny egg.',
  10.50,
  'uploads/foods/kimchi_fried_rice.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Kimchi Fried Rice');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Thai Express'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Chinese'),
  'Basil Chicken Rice Plate',
  'Fragrant basil chicken served with steamed rice and a light chili kick.',
  11.80,
  'uploads/foods/basil_chicken_rice_plate.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Basil Chicken Rice Plate');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Thai Express'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Chinese'),
  'Tom Yum Noodles',
  'Hot and sour noodles with shrimp, mushrooms, and a bright citrus finish.',
  12.40,
  'uploads/foods/tom_yum_noodles.jpg',
  1, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Tom Yum Noodles');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Thai Express'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Chinese'),
  'Crispy Spring Rolls',
  'Golden rolls filled with vegetables and served with a sweet chili dip.',
  6.90,
  'uploads/foods/crispy_spring_rolls.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Crispy Spring Rolls');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Italian Bistro'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Pizza'),
  'Margherita Flatbread',
  'Classic tomato, basil, and mozzarella layered on a soft thin crust.',
  12.60,
  'uploads/foods/margherita_flatbread.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Margherita Flatbread');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Italian Bistro'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Pizza'),
  'Creamy Mushroom Pasta',
  'Silky pasta tossed with mushrooms, parmesan, and a rich cream sauce.',
  13.95,
  'uploads/foods/creamy_mushroom_pasta.jpg',
  1, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Creamy Mushroom Pasta');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Italian Bistro'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Pizza'),
  'Garlic Bread Bites',
  'Warm garlic bread served with herbs and a side of tomato dip.',
  5.40,
  'uploads/foods/garlic_bread_bites.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Garlic Bread Bites');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Healthy Bowl'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Healthy Food'),
  'Avocado Quinoa Bowl',
  'A colorful bowl of quinoa, avocado, greens, and roasted vegetables.',
  11.50,
  'uploads/foods/avocado_quinoa_bowl.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Avocado Quinoa Bowl');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Healthy Bowl'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Healthy Food'),
  'Grilled Salmon Salad',
  'Fresh greens topped with flaky salmon, cucumber, and citrus dressing.',
  13.80,
  'uploads/foods/grilled_salmon_salad.jpg',
  1, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Grilled Salmon Salad');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Healthy Bowl'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Healthy Food'),
  'Green Detox Smoothie',
  'A refreshing blend of spinach, pineapple, mango, and lime.',
  6.20,
  'uploads/foods/green_detox_smoothie.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Green Detox Smoothie');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Coffee & Dessert'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Drinks'),
  'Iced Matcha Latte',
  'Smooth matcha served over milk with a light sweetness and ice.',
  5.50,
  'uploads/foods/iced_matcha_latte.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Iced Matcha Latte');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Coffee & Dessert'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Drinks'),
  'Caramel Cold Brew',
  'Bold cold brew finished with a silky caramel swirl.',
  4.90,
  'uploads/foods/caramel_cold_brew.jpg',
  1, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Caramel Cold Brew');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Coffee & Dessert'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Drinks'),
  'Vanilla Bean Frappe',
  'Creamy frappé blended with vanilla bean and a touch of sweetness.',
  6.20,
  'uploads/foods/vanilla_bean_frappe.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Vanilla Bean Frappe');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Seafood Paradise'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Japanese'),
  'Garlic Prawn Rice Plate',
  'Sautéed prawns with garlic butter and fluffy jasmine rice.',
  17.50,
  'uploads/foods/garlic_prawn_rice_plate.jpg',
  1, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Garlic Prawn Rice Plate');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Seafood Paradise'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Japanese'),
  'Crispy Calamari Basket',
  'Tender squid rings fried crisp and served with zesty dipping sauce.',
  13.20,
  'uploads/foods/crispy_calamari_basket.jpg',
  1, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Crispy Calamari Basket');

INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`)
SELECT
  (SELECT `id` FROM `restaurants` WHERE `name` = 'Seafood Paradise'),
  (SELECT `id` FROM `categories` WHERE `name` = 'Japanese'),
  'Lobster Bisque Bowl',
  'Creamy lobster bisque with a bright seafood finish and toasted bread.',
  16.90,
  'uploads/foods/lobster_bisque_bowl.jpg',
  0, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `foods` WHERE `name` = 'Lobster Bisque Bowl');
