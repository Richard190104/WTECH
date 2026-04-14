<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $brandNames = [
            'Samsung',
            'Apple',
            'Sony',
            'LG',
            'Xiaomi',
            'HP',
            'Lenovo',
            'Asus',
            'Dell',
            'Bose',
            'JBL',
            'Logitech',
            'Razer',
            'Microsoft',
            'Acer',
            'Philips',
            'Garmin',
            'Anker',
        ];

        foreach ($brandNames as $brandName) {
            if (! DB::table('brands')->where('name', $brandName)->exists()) {
                DB::table('brands')->insert(['name' => $brandName]);
            }
        }

        $brandIds = DB::table('brands')->pluck('id', 'name')->all();

        $categoryTree = [
            ['name' => 'Computers', 'parent' => null],
            ['name' => 'Phones', 'parent' => null],
            ['name' => 'Audio', 'parent' => null],
            ['name' => 'Accessories', 'parent' => null],
            ['name' => 'Gaming', 'parent' => null],
            ['name' => 'TV', 'parent' => null],
            ['name' => 'Wearables', 'parent' => null],
            ['name' => 'Ultrabooks', 'parent' => 'Computers'],
            ['name' => 'Gaming Laptops', 'parent' => 'Computers'],
            ['name' => 'Smartphones', 'parent' => 'Phones'],
            ['name' => 'Headphones', 'parent' => 'Audio'],
            ['name' => 'Portable Speakers', 'parent' => 'Audio'],
            ['name' => 'Monitors', 'parent' => 'Accessories'],
            ['name' => 'Keyboards', 'parent' => 'Accessories'],
            ['name' => 'Mice', 'parent' => 'Accessories'],
            ['name' => 'Consoles', 'parent' => 'Gaming'],
            ['name' => 'OLED TVs', 'parent' => 'TV'],
            ['name' => 'Smartwatches', 'parent' => 'Wearables'],
        ];

        foreach ($categoryTree as $category) {
            $parentId = null;
            if ($category['parent']) {
                $parentId = DB::table('categories')
                    ->where('name', $category['parent'])
                    ->whereNull('parent_id')
                    ->value('id');
            }

            $exists = DB::table('categories')
                ->where('name', $category['name'])
                ->where(function ($query) use ($parentId) {
                    if ($parentId === null) {
                        $query->whereNull('parent_id');
                    } else {
                        $query->where('parent_id', $parentId);
                    }
                })
                ->exists();

            if (! $exists) {
                DB::table('categories')->insert([
                    'name' => $category['name'],
                    'parent_id' => $parentId,
                ]);
            }
        }

        $categoryRows = DB::table('categories')->get(['id', 'name', 'parent_id']);
        $categoryIdsByName = [];
        foreach ($categoryRows as $row) {
            if (! isset($categoryIdsByName[$row->name])) {
                $categoryIdsByName[$row->name] = $row->id;
            }
        }

        $products = [
            ['title' => 'Dell Latitude 7440', 'brand' => 'Dell', 'category' => 'Ultrabooks', 'price' => 1249.00, 'discount' => 10.00, 'rating_avg' => 4.60, 'review_count' => 128, 'qty' => 22, 'description' => 'Business ultrabook with durable aluminum body and all-day battery life.', 'specifications' => 'Intel Core i7, 16GB RAM, 512GB SSD, 14 inch IPS', 'date_offset' => 110, 'image_title' => 'images/catalog/laptop-ultrabook.svg', 'gallery' => ['images/catalog/laptop-ultrabook.svg', 'images/catalog/monitor-4k.svg']],
            ['title' => 'HP EliteBook 840 G10', 'brand' => 'HP', 'category' => 'Ultrabooks', 'price' => 1189.00, 'discount' => 8.00, 'rating_avg' => 4.50, 'review_count' => 94, 'qty' => 18, 'description' => 'Slim enterprise laptop designed for hybrid work.', 'specifications' => 'Intel Core i5, 16GB RAM, 512GB SSD, 14 inch FHD', 'date_offset' => 108, 'image_title' => 'images/catalog/laptop-ultrabook.svg', 'gallery' => ['images/catalog/laptop-ultrabook.svg']],
            ['title' => 'Lenovo ThinkPad X1 Carbon Gen 12', 'brand' => 'Lenovo', 'category' => 'Ultrabooks', 'price' => 1599.00, 'discount' => 12.00, 'rating_avg' => 4.80, 'review_count' => 212, 'qty' => 14, 'description' => 'Lightweight premium ultrabook with exceptional keyboard.', 'specifications' => 'Intel Core Ultra 7, 32GB RAM, 1TB SSD, 14 inch OLED', 'date_offset' => 106, 'image_title' => 'images/catalog/laptop-ultrabook.svg', 'gallery' => ['images/catalog/laptop-ultrabook.svg', 'images/catalog/keyboard-mechanical.svg']],
            ['title' => 'Asus ROG Zephyrus G16', 'brand' => 'Asus', 'category' => 'Gaming Laptops', 'price' => 1899.00, 'discount' => 15.00, 'rating_avg' => 4.70, 'review_count' => 143, 'qty' => 9, 'description' => 'Gaming laptop with high-refresh display and RTX graphics.', 'specifications' => 'Intel Core i9, 32GB RAM, RTX 4070, 1TB SSD', 'date_offset' => 104, 'image_title' => 'images/catalog/laptop-gaming.svg', 'gallery' => ['images/catalog/laptop-gaming.svg']],
            ['title' => 'Acer Nitro 16', 'brand' => 'Acer', 'category' => 'Gaming Laptops', 'price' => 1299.00, 'discount' => 11.00, 'rating_avg' => 4.40, 'review_count' => 76, 'qty' => 16, 'description' => 'Balanced gaming performance for mainstream players.', 'specifications' => 'AMD Ryzen 7, 16GB RAM, RTX 4060, 512GB SSD', 'date_offset' => 102, 'image_title' => 'images/catalog/laptop-gaming.svg', 'gallery' => ['images/catalog/laptop-gaming.svg', 'images/catalog/mouse-gaming.svg']],
            ['title' => 'Razer Blade 15 Advance', 'brand' => 'Razer', 'category' => 'Gaming Laptops', 'price' => 2099.00, 'discount' => 18.00, 'rating_avg' => 4.60, 'review_count' => 61, 'qty' => 7, 'description' => 'Compact premium gaming notebook with CNC aluminum chassis.', 'specifications' => 'Intel Core i7, 32GB RAM, RTX 4080, 1TB SSD', 'date_offset' => 100, 'image_title' => 'images/catalog/laptop-gaming.svg', 'gallery' => ['images/catalog/laptop-gaming.svg']],
            ['title' => 'Samsung Galaxy S25', 'brand' => 'Samsung', 'category' => 'Smartphones', 'price' => 999.00, 'discount' => 7.00, 'rating_avg' => 4.70, 'review_count' => 305, 'qty' => 42, 'description' => 'Flagship smartphone with bright AMOLED and AI camera stack.', 'specifications' => '6.7 inch AMOLED, 256GB storage, 12GB RAM, 5G', 'date_offset' => 98, 'image_title' => 'images/catalog/phone-flagship.svg', 'gallery' => ['images/catalog/phone-flagship.svg']],
            ['title' => 'Apple iPhone 17', 'brand' => 'Apple', 'category' => 'Smartphones', 'price' => 1199.00, 'discount' => 5.00, 'rating_avg' => 4.80, 'review_count' => 422, 'qty' => 35, 'description' => 'Top-tier iPhone with pro-grade camera and performance.', 'specifications' => '6.3 inch OLED, 256GB storage, A-series chip', 'date_offset' => 96, 'image_title' => 'images/catalog/phone-flagship.svg', 'gallery' => ['images/catalog/phone-flagship.svg', 'images/catalog/watch-smart.svg']],
            ['title' => 'Xiaomi 15 Pro', 'brand' => 'Xiaomi', 'category' => 'Smartphones', 'price' => 849.00, 'discount' => 9.00, 'rating_avg' => 4.50, 'review_count' => 198, 'qty' => 54, 'description' => 'High-value premium smartphone with ultra-fast charging.', 'specifications' => '6.73 inch AMOLED, 512GB storage, 12GB RAM', 'date_offset' => 94, 'image_title' => 'images/catalog/phone-flagship.svg', 'gallery' => ['images/catalog/phone-flagship.svg']],
            ['title' => 'Sony WH-1000XM6', 'brand' => 'Sony', 'category' => 'Headphones', 'price' => 429.00, 'discount' => 13.00, 'rating_avg' => 4.90, 'review_count' => 512, 'qty' => 48, 'description' => 'Industry-leading noise cancelling over-ear headphones.', 'specifications' => 'Bluetooth 5.4, ANC, 35 hour battery', 'date_offset' => 92, 'image_title' => 'images/catalog/headphones-premium.svg', 'gallery' => ['images/catalog/headphones-premium.svg']],
            ['title' => 'Bose QuietComfort Ultra', 'brand' => 'Bose', 'category' => 'Headphones', 'price' => 399.00, 'discount' => 12.00, 'rating_avg' => 4.70, 'review_count' => 276, 'qty' => 31, 'description' => 'Comfort-oriented wireless ANC headphones for long sessions.', 'specifications' => 'Spatial audio, ANC, multipoint connectivity', 'date_offset' => 90, 'image_title' => 'images/catalog/headphones-premium.svg', 'gallery' => ['images/catalog/headphones-premium.svg']],
            ['title' => 'Apple AirPods Pro 3', 'brand' => 'Apple', 'category' => 'Headphones', 'price' => 299.00, 'discount' => 4.00, 'rating_avg' => 4.60, 'review_count' => 381, 'qty' => 67, 'description' => 'In-ear ANC earbuds with adaptive transparency and H-series chip.', 'specifications' => 'In-ear ANC, MagSafe charging case, IPX4', 'date_offset' => 88, 'image_title' => 'images/catalog/headphones-premium.svg', 'gallery' => ['images/catalog/headphones-premium.svg']],
            ['title' => 'JBL Charge 7', 'brand' => 'JBL', 'category' => 'Portable Speakers', 'price' => 229.00, 'discount' => 10.00, 'rating_avg' => 4.50, 'review_count' => 189, 'qty' => 58, 'description' => 'Portable Bluetooth speaker with bold bass and power bank mode.', 'specifications' => 'IP67, 24 hour battery, USB-C', 'date_offset' => 86, 'image_title' => 'images/catalog/speaker-portable.svg', 'gallery' => ['images/catalog/speaker-portable.svg']],
            ['title' => 'Sony SRS-XG500', 'brand' => 'Sony', 'category' => 'Portable Speakers', 'price' => 349.00, 'discount' => 14.00, 'rating_avg' => 4.40, 'review_count' => 88, 'qty' => 20, 'description' => 'Party speaker with wide sound and ambient illumination.', 'specifications' => 'X-Balanced speakers, 30 hour battery, mic input', 'date_offset' => 84, 'image_title' => 'images/catalog/speaker-portable.svg', 'gallery' => ['images/catalog/speaker-portable.svg']],
            ['title' => 'Anker Soundcore Motion X600', 'brand' => 'Anker', 'category' => 'Portable Speakers', 'price' => 199.00, 'discount' => 9.00, 'rating_avg' => 4.30, 'review_count' => 73, 'qty' => 27, 'description' => 'Compact high-fidelity portable speaker tuned for detail.', 'specifications' => '50W output, LDAC, 12 hour battery', 'date_offset' => 82, 'image_title' => 'images/catalog/speaker-portable.svg', 'gallery' => ['images/catalog/speaker-portable.svg', 'images/catalog/headphones-premium.svg']],
            ['title' => 'LG UltraFine 32UN880', 'brand' => 'LG', 'category' => 'Monitors', 'price' => 699.00, 'discount' => 16.00, 'rating_avg' => 4.60, 'review_count' => 104, 'qty' => 24, 'description' => 'Ergo-mounted 4K monitor for productivity setups.', 'specifications' => '32 inch 4K IPS, USB-C, HDR10', 'date_offset' => 80, 'image_title' => 'images/catalog/monitor-4k.svg', 'gallery' => ['images/catalog/monitor-4k.svg']],
            ['title' => 'Dell UltraSharp U2725Q', 'brand' => 'Dell', 'category' => 'Monitors', 'price' => 749.00, 'discount' => 10.00, 'rating_avg' => 4.70, 'review_count' => 132, 'qty' => 19, 'description' => 'Color-accurate 4K monitor for creators and developers.', 'specifications' => '27 inch 4K IPS Black, USB-C hub, 120Hz', 'date_offset' => 78, 'image_title' => 'images/catalog/monitor-4k.svg', 'gallery' => ['images/catalog/monitor-4k.svg']],
            ['title' => 'Philips Evnia 34M2C7600', 'brand' => 'Philips', 'category' => 'Monitors', 'price' => 899.00, 'discount' => 20.00, 'rating_avg' => 4.40, 'review_count' => 51, 'qty' => 12, 'description' => 'Ultrawide gaming monitor with Mini LED backlight.', 'specifications' => '34 inch ultrawide, 165Hz, Mini LED', 'date_offset' => 76, 'image_title' => 'images/catalog/monitor-4k.svg', 'gallery' => ['images/catalog/monitor-4k.svg', 'images/catalog/laptop-gaming.svg']],
            ['title' => 'Logitech MX Mechanical', 'brand' => 'Logitech', 'category' => 'Keyboards', 'price' => 179.00, 'discount' => 6.00, 'rating_avg' => 4.70, 'review_count' => 166, 'qty' => 61, 'description' => 'Low-profile mechanical keyboard tuned for office productivity.', 'specifications' => 'Tactile switches, backlight, multi-device', 'date_offset' => 74, 'image_title' => 'images/catalog/keyboard-mechanical.svg', 'gallery' => ['images/catalog/keyboard-mechanical.svg']],
            ['title' => 'Razer BlackWidow V4', 'brand' => 'Razer', 'category' => 'Keyboards', 'price' => 199.00, 'discount' => 13.00, 'rating_avg' => 4.50, 'review_count' => 129, 'qty' => 37, 'description' => 'RGB mechanical keyboard with dedicated macro controls.', 'specifications' => 'Green switches, per-key RGB, wrist rest', 'date_offset' => 72, 'image_title' => 'images/catalog/keyboard-mechanical.svg', 'gallery' => ['images/catalog/keyboard-mechanical.svg', 'images/catalog/mouse-gaming.svg']],
            ['title' => 'Microsoft Surface Keyboard', 'brand' => 'Microsoft', 'category' => 'Keyboards', 'price' => 129.00, 'discount' => 5.00, 'rating_avg' => 4.20, 'review_count' => 61, 'qty' => 28, 'description' => 'Minimal aluminum keyboard for productivity-focused setups.', 'specifications' => 'Bluetooth, scissor keys, slim profile', 'date_offset' => 70, 'image_title' => 'images/catalog/keyboard-mechanical.svg', 'gallery' => ['images/catalog/keyboard-mechanical.svg']],
            ['title' => 'Logitech G Pro X Superlight 3', 'brand' => 'Logitech', 'category' => 'Mice', 'price' => 169.00, 'discount' => 10.00, 'rating_avg' => 4.80, 'review_count' => 244, 'qty' => 53, 'description' => 'Ultra-light wireless esports mouse with HERO sensor.', 'specifications' => '63g, 32000 DPI, wireless charging support', 'date_offset' => 68, 'image_title' => 'images/catalog/mouse-gaming.svg', 'gallery' => ['images/catalog/mouse-gaming.svg']],
            ['title' => 'Razer DeathAdder V4 Pro', 'brand' => 'Razer', 'category' => 'Mice', 'price' => 149.00, 'discount' => 7.00, 'rating_avg' => 4.70, 'review_count' => 171, 'qty' => 46, 'description' => 'Ergonomic right-handed gaming mouse with optical clicks.', 'specifications' => '30000 DPI, 8000Hz polling, wireless', 'date_offset' => 66, 'image_title' => 'images/catalog/mouse-gaming.svg', 'gallery' => ['images/catalog/mouse-gaming.svg']],
            ['title' => 'Asus ROG Harpe Ace Mini', 'brand' => 'Asus', 'category' => 'Mice', 'price' => 119.00, 'discount' => 8.00, 'rating_avg' => 4.30, 'review_count' => 74, 'qty' => 39, 'description' => 'Compact performance mouse for claw and fingertip grip.', 'specifications' => '54g shell, tri-mode, 26000 DPI', 'date_offset' => 64, 'image_title' => 'images/catalog/mouse-gaming.svg', 'gallery' => ['images/catalog/mouse-gaming.svg', 'images/catalog/keyboard-mechanical.svg']],
            ['title' => 'Sony PlayStation 5 Slim', 'brand' => 'Sony', 'category' => 'Consoles', 'price' => 549.00, 'discount' => 3.00, 'rating_avg' => 4.90, 'review_count' => 509, 'qty' => 33, 'description' => 'Next-gen gaming console with fast SSD loading.', 'specifications' => '825GB SSD, ray tracing, dualsense controller', 'date_offset' => 62, 'image_title' => 'images/catalog/console-nextgen.svg', 'gallery' => ['images/catalog/console-nextgen.svg']],
            ['title' => 'Xbox Series X 2TB', 'brand' => 'Microsoft', 'category' => 'Consoles', 'price' => 649.00, 'discount' => 5.00, 'rating_avg' => 4.80, 'review_count' => 348, 'qty' => 21, 'description' => 'High-performance 4K console with expanded storage.', 'specifications' => '2TB SSD, 4K gaming, quick resume', 'date_offset' => 60, 'image_title' => 'images/catalog/console-nextgen.svg', 'gallery' => ['images/catalog/console-nextgen.svg']],
            ['title' => 'Nintendo Switch OLED', 'brand' => 'Samsung', 'category' => 'Consoles', 'price' => 379.00, 'discount' => 0.00, 'rating_avg' => 4.70, 'review_count' => 622, 'qty' => 44, 'description' => 'Hybrid handheld console with vivid OLED panel.', 'specifications' => '7 inch OLED, 64GB storage, dock mode', 'date_offset' => 58, 'image_title' => 'images/catalog/console-nextgen.svg', 'gallery' => ['images/catalog/console-nextgen.svg']],
            ['title' => 'LG OLED evo C4 65', 'brand' => 'LG', 'category' => 'OLED TVs', 'price' => 2099.00, 'discount' => 14.00, 'rating_avg' => 4.80, 'review_count' => 233, 'qty' => 11, 'description' => '65 inch OLED TV optimized for cinema and gaming.', 'specifications' => '4K OLED, 144Hz, Dolby Vision', 'date_offset' => 56, 'image_title' => 'images/catalog/tv-oled.svg', 'gallery' => ['images/catalog/tv-oled.svg']],
            ['title' => 'Samsung S95D 55', 'brand' => 'Samsung', 'category' => 'OLED TVs', 'price' => 1899.00, 'discount' => 15.00, 'rating_avg' => 4.60, 'review_count' => 149, 'qty' => 10, 'description' => 'QD-OLED panel with anti-reflective coating for bright rooms.', 'specifications' => '55 inch QD-OLED, 4K, HDR10+', 'date_offset' => 54, 'image_title' => 'images/catalog/tv-oled.svg', 'gallery' => ['images/catalog/tv-oled.svg']],
            ['title' => 'Philips OLED809 65', 'brand' => 'Philips', 'category' => 'OLED TVs', 'price' => 1999.00, 'discount' => 11.00, 'rating_avg' => 4.50, 'review_count' => 97, 'qty' => 8, 'description' => 'OLED television with immersive Ambilight system.', 'specifications' => '65 inch OLED, 120Hz, Dolby Atmos', 'date_offset' => 52, 'image_title' => 'images/catalog/tv-oled.svg', 'gallery' => ['images/catalog/tv-oled.svg', 'images/catalog/speaker-portable.svg']],
            ['title' => 'Apple Watch Series 11', 'brand' => 'Apple', 'category' => 'Smartwatches', 'price' => 499.00, 'discount' => 4.00, 'rating_avg' => 4.70, 'review_count' => 283, 'qty' => 49, 'description' => 'Premium smartwatch with advanced wellness tracking.', 'specifications' => 'Always-on OLED, GPS, ECG, blood oxygen', 'date_offset' => 50, 'image_title' => 'images/catalog/watch-smart.svg', 'gallery' => ['images/catalog/watch-smart.svg']],
            ['title' => 'Samsung Galaxy Watch 8', 'brand' => 'Samsung', 'category' => 'Smartwatches', 'price' => 379.00, 'discount' => 7.00, 'rating_avg' => 4.60, 'review_count' => 205, 'qty' => 57, 'description' => 'Wear OS smartwatch with health and sport insights.', 'specifications' => 'AMOLED, dual-frequency GPS, body composition', 'date_offset' => 48, 'image_title' => 'images/catalog/watch-smart.svg', 'gallery' => ['images/catalog/watch-smart.svg']],
            ['title' => 'Garmin Venu 4', 'brand' => 'Garmin', 'category' => 'Smartwatches', 'price' => 429.00, 'discount' => 9.00, 'rating_avg' => 4.80, 'review_count' => 164, 'qty' => 32, 'description' => 'Fitness-first smartwatch with long battery life.', 'specifications' => 'AMOLED, advanced training metrics, NFC pay', 'date_offset' => 46, 'image_title' => 'images/catalog/watch-smart.svg', 'gallery' => ['images/catalog/watch-smart.svg', 'images/catalog/phone-flagship.svg']],
            ['title' => 'Anker Prime GaN Charger 240W', 'brand' => 'Anker', 'category' => 'Accessories', 'price' => 149.00, 'discount' => 12.00, 'rating_avg' => 4.70, 'review_count' => 145, 'qty' => 85, 'description' => 'High-power desktop charger for laptops and phones.', 'specifications' => '3x USB-C, 1x USB-A, 240W total output', 'date_offset' => 44, 'image_title' => 'images/catalog/accessory-power.svg', 'gallery' => ['images/catalog/accessory-power.svg']],
            ['title' => 'Dell Thunderbolt Dock WD22TB4', 'brand' => 'Dell', 'category' => 'Accessories', 'price' => 319.00, 'discount' => 6.00, 'rating_avg' => 4.40, 'review_count' => 84, 'qty' => 26, 'description' => 'Universal dock that expands laptop connectivity.', 'specifications' => 'Thunderbolt 4, dual 4K output, 130W charging', 'date_offset' => 42, 'image_title' => 'images/catalog/accessory-power.svg', 'gallery' => ['images/catalog/accessory-power.svg', 'images/catalog/monitor-4k.svg']],
            ['title' => 'Logitech Brio 505 Webcam', 'brand' => 'Logitech', 'category' => 'Accessories', 'price' => 149.00, 'discount' => 8.00, 'rating_avg' => 4.30, 'review_count' => 109, 'qty' => 47, 'description' => 'Business-ready webcam with noise reduction microphones.', 'specifications' => '1080p, auto framing, USB-C', 'date_offset' => 40, 'image_title' => 'images/catalog/accessory-power.svg', 'gallery' => ['images/catalog/accessory-power.svg']],
        ];

        foreach ($products as $index => $product) {
            $productId = DB::table('products')->where('title', $product['title'])->value('id');

            if (! $productId) {
                $categoryId = $categoryIdsByName[$product['category']] ?? null;
                $brandId = $brandIds[$product['brand']] ?? null;

                if (! $categoryId || ! $brandId) {
                    continue;
                }

                $productId = DB::table('products')->insertGetId([
                    'title' => $product['title'],
                    'price' => $product['price'],
                    'discount' => $product['discount'],
                    'rating_avg' => $product['rating_avg'],
                    'review_count' => $product['review_count'],
                    'description' => $product['description'],
                    'specifications' => $product['specifications'],
                    'brand_id' => $brandId,
                    'category_id' => $categoryId,
                    'qty' => $product['qty'],
                    'is_active' => true,
                    'date_added' => $now->copy()->subDays($product['date_offset']),
                    'updated_at' => $now,
                ]);
            }

            $imageExists = DB::table('product_images')
                ->where('product_id', $productId)
                ->where('image_path', $product['image_title'])
                ->exists();

            if (! $imageExists) {
                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'alt_text' => $product['title'] . ' hero image',
                    'image_path' => $product['image_title'],
                    'is_title' => true,
                ]);
            }

            foreach ($product['gallery'] as $galleryImagePath) {
                $galleryExists = DB::table('product_images')
                    ->where('product_id', $productId)
                    ->where('image_path', $galleryImagePath)
                    ->where('is_title', false)
                    ->exists();

                if (! $galleryExists) {
                    DB::table('product_images')->insert([
                        'product_id' => $productId,
                        'alt_text' => $product['title'] . ' gallery',
                        'image_path' => $galleryImagePath,
                        'is_title' => false,
                    ]);
                }
            }

            if ($index < 24) {
                $reviewExists = DB::table('reviews')
                    ->where('product_id', $productId)
                    ->where('text', 'Verified buyer review for ' . $product['title'])
                    ->exists();

                if (! $reviewExists) {
                    DB::table('reviews')->insert([
                        'product_id' => $productId,
                        'rating' => (int) round($product['rating_avg']),
                        'text' => 'Verified buyer review for ' . $product['title'],
                        'created_at' => $now->copy()->subDays(max(2, $product['date_offset'] - 5)),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $titles = [
            'Dell Latitude 7440',
            'HP EliteBook 840 G10',
            'Lenovo ThinkPad X1 Carbon Gen 12',
            'Asus ROG Zephyrus G16',
            'Acer Nitro 16',
            'Razer Blade 15 Advance',
            'Samsung Galaxy S25',
            'Apple iPhone 17',
            'Xiaomi 15 Pro',
            'Sony WH-1000XM6',
            'Bose QuietComfort Ultra',
            'Apple AirPods Pro 3',
            'JBL Charge 7',
            'Sony SRS-XG500',
            'Anker Soundcore Motion X600',
            'LG UltraFine 32UN880',
            'Dell UltraSharp U2725Q',
            'Philips Evnia 34M2C7600',
            'Logitech MX Mechanical',
            'Razer BlackWidow V4',
            'Microsoft Surface Keyboard',
            'Logitech G Pro X Superlight 3',
            'Razer DeathAdder V4 Pro',
            'Asus ROG Harpe Ace Mini',
            'Sony PlayStation 5 Slim',
            'Xbox Series X 2TB',
            'Nintendo Switch OLED',
            'LG OLED evo C4 65',
            'Samsung S95D 55',
            'Philips OLED809 65',
            'Apple Watch Series 11',
            'Samsung Galaxy Watch 8',
            'Garmin Venu 4',
            'Anker Prime GaN Charger 240W',
            'Dell Thunderbolt Dock WD22TB4',
            'Logitech Brio 505 Webcam',
        ];

        $productIds = DB::table('products')->whereIn('title', $titles)->pluck('id')->all();

        if (! empty($productIds)) {
            DB::table('reviews')->whereIn('product_id', $productIds)->delete();
            DB::table('product_images')->whereIn('product_id', $productIds)->delete();
            DB::table('products')->whereIn('id', $productIds)->delete();
        }

        $cleanupCategories = [
            'Ultrabooks',
            'Gaming Laptops',
            'Portable Speakers',
            'Monitors',
            'Keyboards',
            'Mice',
            'Consoles',
            'OLED TVs',
            'Smartwatches',
        ];

        DB::table('categories')
            ->whereIn('name', $cleanupCategories)
            ->whereNotIn('id', DB::table('products')->select('category_id'))
            ->delete();

        $cleanupBrands = ['Dell', 'Bose', 'JBL', 'Logitech', 'Razer', 'Microsoft', 'Acer', 'Philips', 'Garmin', 'Anker'];

        DB::table('brands')
            ->whereIn('name', $cleanupBrands)
            ->whereNotIn('id', DB::table('products')->select('brand_id'))
            ->delete();
    }
};
