from PIL import Image, ImageDraw, ImageFont
from pathlib import Path
import os
import re

root = Path(r"c:\laragon\www\Food_delivery_WDD")
food_dir = root / "uploads" / "foods"
restaurant_dir = root / "uploads" / "restaurants"
food_dir.mkdir(parents=True, exist_ok=True)
restaurant_dir.mkdir(parents=True, exist_ok=True)

try:
    font = ImageFont.truetype("C:/Windows/Fonts/arial.ttf", 36)
    font_small = ImageFont.truetype("C:/Windows/Fonts/arial.ttf", 24)
except Exception:
    font = ImageFont.load_default()
    font_small = ImageFont.load_default()


def sanitize_name(name: str) -> str:
    name = re.sub(r"[^a-z0-9]+", "_", name.lower()).strip("_")
    return name or "food"


def add_plate(draw, box, color=(255, 255, 255)):
    x0, y0, x1, y1 = box
    draw.ellipse((x0, y0, x1, y1), fill=color, outline=(220, 220, 220), width=8)
    draw.ellipse((x0 + 20, y0 + 20, x1 - 20, y1 - 20), fill=(250, 250, 250), outline=(230, 230, 230), width=4)


def make_burger(path, title):
    img = Image.new("RGB", (1024, 1024), (248, 242, 232))
    draw = ImageDraw.Draw(img)
    # shadow
    draw.ellipse((120, 130, 900, 820), fill=(225, 210, 190))
    add_plate(draw, (180, 200, 840, 860), (255, 255, 255))
    # bun
    draw.rounded_rectangle((280, 315, 740, 455), radius=40, fill=(240, 180, 100), outline=(210, 150, 80), width=8)
    draw.rounded_rectangle((300, 335, 720, 445), radius=32, fill=(250, 205, 120), outline=(240, 190, 95), width=4)
    # patty
    draw.rounded_rectangle((300, 450, 720, 540), radius=28, fill=(150, 85, 45), outline=(120, 60, 28), width=6)
    # cheese
    draw.rounded_rectangle((300, 500, 720, 530), radius=18, fill=(240, 200, 80), outline=(220, 175, 60), width=3)
    # lettuce
    draw.rounded_rectangle((320, 530, 700, 570), radius=15, fill=(100, 180, 90), outline=(80, 150, 70), width=3)
    # tomato
    draw.ellipse((332, 545, 400, 610), fill=(220, 80, 60), outline=(190, 60, 40), width=3)
    draw.ellipse((620, 545, 690, 610), fill=(220, 80, 60), outline=(190, 60, 40), width=3)
    # bottom bun
    draw.rounded_rectangle((280, 575, 740, 705), radius=40, fill=(240, 180, 100), outline=(210, 150, 80), width=8)
    draw.rounded_rectangle((300, 590, 720, 690), radius=30, fill=(250, 205, 120), outline=(240, 190, 95), width=4)
    draw.text((80, 840), title, fill=(70, 70, 70), font=font)
    img.save(path)


def make_pizza(path, title):
    img = Image.new("RGB", (1024, 1024), (252, 242, 230))
    draw = ImageDraw.Draw(img)
    draw.ellipse((140, 140, 880, 880), fill=(221, 135, 55), outline=(180, 100, 35), width=12)
    draw.ellipse((190, 190, 830, 830), fill=(232, 81, 43), outline=(200, 60, 30), width=10)
    draw.pieslice((190, 190, 830, 830), 0, 90, fill=(240, 200, 80))
    draw.pieslice((190, 190, 830, 830), 90, 180, fill=(188, 144, 74))
    draw.pieslice((190, 190, 830, 830), 180, 270, fill=(240, 200, 80))
    draw.pieslice((190, 190, 830, 830), 270, 360, fill=(188, 144, 74))
    draw.ellipse((245, 250, 775, 780), fill=(255, 240, 200), outline=(240, 220, 170), width=5)
    draw.rectangle((360, 560, 660, 660), fill=(255, 231, 152), outline=(230, 205, 120), width=5)
    draw.text((80, 850), title, fill=(70, 70, 70), font=font)
    img.save(path)


def make_sushi(path, title):
    img = Image.new("RGB", (1024, 1024), (248, 244, 238))
    draw = ImageDraw.Draw(img)
    add_plate(draw, (170, 200, 850, 900), (255, 255, 255))
    draw.ellipse((300, 300, 720, 720), fill=(255, 240, 220), outline=(220, 200, 180), width=8)
    for x, y in [(360, 350), (470, 335), (575, 360), (420, 470), (540, 470)]:
        draw.ellipse((x, y, x + 90, y + 90), fill=(255, 255, 255), outline=(200, 200, 200), width=4)
        draw.ellipse((x + 16, y + 16, x + 74, y + 74), fill=(248, 180, 100), outline=(220, 150, 80), width=4)
    draw.rectangle((220, 760, 320, 860), fill=(180, 120, 70), outline=(140, 90, 50), width=4)
    draw.rectangle((650, 760, 780, 860), fill=(180, 120, 70), outline=(140, 90, 50), width=4)
    draw.text((80, 850), title, fill=(70, 70, 70), font=font)
    img.save(path)


def make_ramen(path, title):
    img = Image.new("RGB", (1024, 1024), (252, 246, 240))
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((220, 260, 800, 760), radius=60, fill=(255, 250, 240), outline=(230, 220, 200), width=6)
    draw.rounded_rectangle((260, 310, 760, 710), radius=50, fill=(245, 212, 152), outline=(220, 190, 130), width=6)
    draw.arc((300, 200, 700, 420), 0, 180, fill=(220, 220, 220), width=8)
    draw.rectangle((320, 360, 700, 600), fill=(200, 120, 60), outline=(180, 100, 50), width=4)
    draw.ellipse((360, 430, 440, 500), fill=(255, 240, 200), outline=(220, 195, 170), width=4)
    draw.ellipse((560, 430, 640, 500), fill=(255, 240, 200), outline=(220, 195, 170), width=4)
    draw.rectangle((360, 610, 660, 670), fill=(180, 130, 70), outline=(150, 100, 40), width=3)
    draw.text((80, 840), title, fill=(70, 70, 70), font=font)
    img.save(path)


def make_dessert(path, title):
    img = Image.new("RGB", (1024, 1024), (255, 248, 245))
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((220, 220, 800, 760), radius=50, fill=(255, 250, 250), outline=(230, 220, 215), width=6)
    draw.rounded_rectangle((260, 270, 760, 700), radius=40, fill=(248, 220, 200), outline=(230, 190, 170), width=6)
    draw.ellipse((330, 300, 420, 390), fill=(255, 180, 180), outline=(220, 140, 140), width=4)
    draw.ellipse((600, 320, 690, 410), fill=(255, 180, 180), outline=(220, 140, 140), width=4)
    draw.ellipse((430, 390, 580, 500), fill=(240, 140, 120), outline=(210, 120, 90), width=4)
    draw.text((80, 850), title, fill=(70, 70, 70), font=font)
    img.save(path)


def make_drink(path, title):
    img = Image.new("RGB", (1024, 1024), (245, 248, 252))
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((280, 250, 760, 720), radius=36, fill=(255, 255, 255), outline=(220, 220, 220), width=6)
    draw.rounded_rectangle((330, 300, 710, 650), radius=26, fill=(220, 240, 255), outline=(190, 205, 220), width=5)
    draw.ellipse((360, 180, 420, 255), fill=(255, 255, 255), outline=(220, 220, 220), width=4)
    draw.rectangle((392, 242, 408, 284), fill=(255, 255, 255))
    draw.rectangle((480, 340, 620, 520), fill=(255, 200, 120), outline=(200, 160, 90), width=4)
    draw.line((600, 380, 700, 260), fill=(255, 200, 120), width=12)
    draw.text((80, 850), title, fill=(70, 70, 70), font=font)
    img.save(path)


def make_healthy(path, title):
    img = Image.new("RGB", (1024, 1024), (248, 250, 240))
    draw = ImageDraw.Draw(img)
    draw.ellipse((190, 200, 830, 840), fill=(255, 255, 255), outline=(220, 220, 220), width=8)
    draw.ellipse((240, 250, 780, 790), fill=(245, 250, 235), outline=(200, 210, 190), width=6)
    draw.circle((370, 440), 70, fill=(120, 180, 70))
    draw.circle((520, 395), 55, fill=(255, 180, 80))
    draw.circle((650, 470), 65, fill=(80, 140, 90))
    draw.ellipse((410, 540, 710, 660), fill=(200, 145, 80), outline=(160, 110, 60), width=4)
    draw.text((80, 850), title, fill=(70, 70, 70), font=font)
    img.save(path)


def make_seafood(path, title):
    img = Image.new("RGB", (1024, 1024), (250, 246, 240))
    draw = ImageDraw.Draw(img)
    add_plate(draw, (180, 220, 840, 860), (255, 255, 255))
    draw.ellipse((320, 330, 700, 700), fill=(245, 205, 120), outline=(220, 170, 80), width=8)
    draw.arc((340, 280, 680, 610), 20, 160, fill=(220, 130, 70), width=5)
    draw.arc((420, 360, 640, 540), 0, 180, fill=(220, 120, 60), width=6)
    draw.ellipse((380, 460, 450, 530), fill=(255, 255, 255), outline=(220, 220, 220), width=3)
    draw.ellipse((560, 470, 630, 540), fill=(255, 255, 255), outline=(220, 220, 220), width=3)
    draw.text((80, 850), title, fill=(70, 70, 70), font=font)
    img.save(path)


def make_pasta(path, title):
    img = Image.new("RGB", (1024, 1024), (248, 244, 238))
    draw = ImageDraw.Draw(img)
    add_plate(draw, (180, 220, 840, 860), (255, 255, 255))
    draw.rounded_rectangle((280, 300, 740, 720), radius=50, fill=(240, 205, 140), outline=(210, 175, 110), width=8)
    for x in range(320, 740, 80):
        draw.ellipse((x, 330, x + 40, 390), fill=(196, 112, 64))
        draw.ellipse((x + 8, 410, x + 48, 470), fill=(196, 112, 64))
    draw.text((80, 850), title, fill=(70, 70, 70), font=font)
    img.save(path)


food_specs = [
    ("uploads/foods/tonkotsu_ramen.jpg", "Tonkotsu Ramen", "ramen"),
    ("uploads/foods/salmon_sushi_box.jpg", "Salmon Sushi Box", "sushi"),
    ("uploads/foods/chicken_katsu_curry.jpg", "Chicken Katsu Curry", "ramen"),
    ("uploads/foods/strawberry_waffle_fantasy.jpg", "Strawberry Waffle Fantasy", "dessert"),
    ("uploads/foods/molten_lava_cake.jpg", "Molten Lava Cake", "dessert"),
    ("uploads/foods/oreo_cheesecake_slice.jpg", "Oreo Cheesecake Slice", "dessert"),
    ("uploads/foods/smash_classic_burger.jpg", "Smash Classic Burger", "burger"),
    ("uploads/foods/bbq_chicken_wrap.jpg", "BBQ Chicken Wrap", "burger"),
    ("uploads/foods/loaded_fries_basket.jpg", "Loaded Fries Basket", "burger"),
    ("uploads/foods/truffle_mushroom_pizza.jpg", "Truffle Mushroom Pizza", "pizza"),
    ("uploads/foods/pepperoni_fire_pizza.jpg", "Pepperoni Fire Pizza", "pizza"),
    ("uploads/foods/garden_veggie_pizza.jpg", "Garden Veggie Pizza", "pizza"),
    ("uploads/foods/gochujang_beef_bowl.jpg", "Gochujang Beef Bowl", "healthy"),
    ("uploads/foods/spicy_chicken_skewers.jpg", "Spicy Chicken Skewers", "healthy"),
    ("uploads/foods/kimchi_fried_rice.jpg", "Kimchi Fried Rice", "healthy"),
    ("uploads/foods/basil_chicken_rice_plate.jpg", "Basil Chicken Rice Plate", "healthy"),
    ("uploads/foods/tom_yum_noodles.jpg", "Tom Yum Noodles", "pasta"),
    ("uploads/foods/crispy_spring_rolls.jpg", "Crispy Spring Rolls", "healthy"),
    ("uploads/foods/margherita_flatbread.jpg", "Margherita Flatbread", "pizza"),
    ("uploads/foods/creamy_mushroom_pasta.jpg", "Creamy Mushroom Pasta", "pasta"),
    ("uploads/foods/garlic_bread_bites.jpg", "Garlic Bread Bites", "pasta"),
    ("uploads/foods/avocado_quinoa_bowl.jpg", "Avocado Quinoa Bowl", "healthy"),
    ("uploads/foods/grilled_salmon_salad.jpg", "Grilled Salmon Salad", "healthy"),
    ("uploads/foods/green_detox_smoothie.jpg", "Green Detox Smoothie", "drink"),
    ("uploads/foods/iced_matcha_latte.jpg", "Iced Matcha Latte", "drink"),
    ("uploads/foods/caramel_cold_brew.jpg", "Caramel Cold Brew", "drink"),
    ("uploads/foods/vanilla_bean_frappe.jpg", "Vanilla Bean Frappe", "drink"),
    ("uploads/foods/garlic_prawn_rice_plate.jpg", "Garlic Prawn Rice Plate", "seafood"),
    ("uploads/foods/crispy_calamari_basket.jpg", "Crispy Calamari Basket", "seafood"),
    ("uploads/foods/lobster_bisque_bowl.jpg", "Lobster Bisque Bowl", "seafood"),
]

restaurant_specs = [
    ("uploads/restaurants/sakura_zen.jpg", "Sakura Zen", "japanese"),
    ("uploads/restaurants/sweet_tooth_treats.jpg", "Sweet Tooth Treats", "dessert"),
    ("uploads/restaurants/burger_house.jpg", "Burger House", "burger"),
    ("uploads/restaurants/pizza_corner.jpg", "Pizza Corner", "pizza"),
    ("uploads/restaurants/korean_bbq.jpg", "Korean BBQ", "healthy"),
    ("uploads/restaurants/thai_express.jpg", "Thai Express", "pasta"),
    ("uploads/restaurants/italian_bistro.jpg", "Italian Bistro", "pizza"),
    ("uploads/restaurants/healthy_bowl.jpg", "Healthy Bowl", "healthy"),
    ("uploads/restaurants/coffee_and_dessert.jpg", "Coffee & Dessert", "dessert"),
    ("uploads/restaurants/seafood_paradise.jpg", "Seafood Paradise", "seafood"),
]

for rel_path, title, style in food_specs:
    target = root / rel_path
    if target.exists():
        continue
    if style == "burger":
        make_burger(target, title)
    elif style == "pizza":
        make_pizza(target, title)
    elif style == "sushi":
        make_sushi(target, title)
    elif style == "ramen":
        make_ramen(target, title)
    elif style == "dessert":
        make_dessert(target, title)
    elif style == "drink":
        make_drink(target, title)
    elif style == "healthy":
        make_healthy(target, title)
    elif style == "seafood":
        make_seafood(target, title)
    elif style == "pasta":
        make_pasta(target, title)
    else:
        make_burger(target, title)

for rel_path, title, style in restaurant_specs:
    target = root / rel_path
    if target.exists():
        continue
    img = Image.new("RGB", (1024, 1024), (248, 245, 240))
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((130, 140, 890, 900), radius=50, fill=(255, 255, 255), outline=(220, 220, 220), width=10)
    draw.rounded_rectangle((200, 220, 820, 820), radius=38, fill=(248, 229, 206), outline=(220, 196, 165), width=6)
    draw.text((220, 340), title, fill=(70, 70, 70), font=font)
    draw.text((220, 430), "Freshly prepared", fill=(120, 120, 120), font=font_small)
    draw.text((220, 480), "Online ordering available", fill=(120, 120, 120), font=font_small)
    img.save(target)

print("Generated sample images")
