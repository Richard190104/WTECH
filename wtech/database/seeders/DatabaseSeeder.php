<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Clean store data but keep users intact.
            DB::table('order_items')->delete();
            DB::table('cart_items')->delete();
            DB::table('reviews')->delete();
            DB::table('product_images')->delete();
            DB::table('products')->delete();
            DB::table('categories')->delete();
            DB::table('brands')->delete();

            $brands = [
                'Samsung',
                'Apple',
                'Sony',
                'LG',
                'Xiaomi',
                'HP',
                'Lenovo',
                'Asus',
                'Logitech',
                'JBL',
            ];

            DB::table('brands')->insert(array_map(fn (string $name) => ['name' => $name], $brands));
            $brandIds = DB::table('brands')->pluck('id', 'name');

            $categoryTree = [
                'Computers' => ['Laptops', 'Desktops'],
                'Phones' => ['Smartphones'],
                'Audio' => ['Headphones', 'Speakers'],
                'Accessories' => ['Keyboards', 'Mice'],
                'Gaming' => ['Consoles'],
                'TV' => ['Smart TVs'],
                'Wearables' => ['Smartwatches'],
            ];

            $categoryIds = [];
            foreach ($categoryTree as $root => $children) {
                $rootId = DB::table('categories')->insertGetId([
                    'parent_id' => null,
                    'name' => $root,
                ]);

                $categoryIds[$root] = $rootId;

                foreach ($children as $child) {
                    $categoryIds[$child] = DB::table('categories')->insertGetId([
                        'parent_id' => $rootId,
                        'name' => $child,
                    ]);
                }
            }

            $imagePool = [
                'images/HP-ProBook-450-G9_0b.jpg',
                'images/Playdock 5 console.jpg',
                'images/silly cat.jpg',
                'images/smartphone x12.jpg',
                'images/ultrabook pro.jpg',
                'images/USB C HUB.jpg',
                'images/Wireless Mouse Pro.jpg',
            ];

            $productBlueprints = [
                [
                    'stem' => 'AeroBook',
                    'category' => 'Laptops',
                    'brands' => ['HP', 'Lenovo', 'Asus'],
                    'base_price' => 749,
                    'price_step' => 59,
                    'description' => 'Slim productivity laptop for work and school.',
                    'specifications' => 'Intel Core i5, 16GB RAM, 512GB SSD, Wi-Fi 6',
                ],
                [
                    'stem' => 'DeskPro',
                    'category' => 'Desktops',
                    'brands' => ['HP', 'Lenovo', 'Asus'],
                    'base_price' => 899,
                    'price_step' => 75,
                    'description' => 'Reliable desktop PC for multitasking and office workloads.',
                    'specifications' => 'Intel Core i7, 16GB RAM, 1TB SSD, RTX 4060',
                ],
                [
                    'stem' => 'NovaPhone',
                    'category' => 'Smartphones',
                    'brands' => ['Samsung', 'Apple', 'Xiaomi'],
                    'base_price' => 429,
                    'price_step' => 95,
                    'description' => '5G smartphone with advanced camera and all-day battery.',
                    'specifications' => '6.5 inch OLED, 256GB storage, 8GB RAM, 5000mAh',
                ],
                [
                    'stem' => 'QuietBeat',
                    'category' => 'Headphones',
                    'brands' => ['Sony', 'JBL', 'Samsung'],
                    'base_price' => 119,
                    'price_step' => 24,
                    'description' => 'Wireless headphones with active noise cancellation.',
                    'specifications' => 'Bluetooth 5.3, 40h battery, ANC, USB-C',
                ],
                [
                    'stem' => 'RoomPulse',
                    'category' => 'Speakers',
                    'brands' => ['JBL', 'Sony', 'LG'],
                    'base_price' => 89,
                    'price_step' => 19,
                    'description' => 'Compact smart speaker with rich stereo sound.',
                    'specifications' => 'Wi-Fi, Bluetooth, dual driver, voice assistant ready',
                ],
                [
                    'stem' => 'TypeFlow',
                    'category' => 'Keyboards',
                    'brands' => ['Logitech', 'Asus', 'Lenovo'],
                    'base_price' => 69,
                    'price_step' => 14,
                    'description' => 'Mechanical keyboard optimized for speed and comfort.',
                    'specifications' => 'Hot-swappable switches, RGB, detachable USB-C cable',
                ],
                [
                    'stem' => 'SwiftMouse',
                    'category' => 'Mice',
                    'brands' => ['Logitech', 'Asus', 'HP'],
                    'base_price' => 39,
                    'price_step' => 12,
                    'description' => 'High precision wireless mouse for productivity and gaming.',
                    'specifications' => 'Up to 26000 DPI, low-latency wireless, 80h battery',
                ],
                [
                    'stem' => 'PlayDock',
                    'category' => 'Consoles',
                    'brands' => ['Sony', 'Samsung', 'LG'],
                    'base_price' => 399,
                    'price_step' => 49,
                    'description' => 'Next-gen console for 4K gaming and streaming.',
                    'specifications' => '4K output, 1TB SSD, 120fps support, ray tracing',
                ],
                [
                    'stem' => 'VisionTV',
                    'category' => 'Smart TVs',
                    'brands' => ['LG', 'Samsung', 'Sony'],
                    'base_price' => 699,
                    'price_step' => 109,
                    'description' => 'Smart TV with cinematic color and low latency mode.',
                    'specifications' => '4K UHD, HDR10+, 120Hz panel, HDMI 2.1',
                ],
                [
                    'stem' => 'PulseWatch',
                    'category' => 'Smartwatches',
                    'brands' => ['Samsung', 'Apple', 'Xiaomi'],
                    'base_price' => 189,
                    'price_step' => 34,
                    'description' => 'Fitness smartwatch with health insights and GPS.',
                    'specifications' => 'AMOLED display, GPS, heart rate, sleep tracking',
                ],
            ];

            $variants = ['S', 'Plus', 'Pro', 'Max', 'Ultra'];
            $reviewTexts = [
                'Excellent value for the price and very reliable.',
                'Build quality is great and performance is smooth.',
                'Battery life is better than expected.',
                'Fast shipping and product matches the description.',
                'Good product overall, would buy again.',
                'Works perfectly for daily use.',
            ];

            foreach ($productBlueprints as $blueprintIndex => $blueprint) {
                foreach ($variants as $variantIndex => $variant) {
                    $brand = $blueprint['brands'][($blueprintIndex + $variantIndex) % count($blueprint['brands'])];
                    $year = 2024 + ($variantIndex % 2);
                    $title = $brand . ' ' . $blueprint['stem'] . ' ' . $variant . ' ' . $year;

                    $price = $blueprint['base_price']
                        + ($variantIndex * $blueprint['price_step'])
                        + (($blueprintIndex % 3) * 17);

                    $discount = [0, 5, 10, 15, 20][($blueprintIndex + $variantIndex) % 5];
                    $qty = (($blueprintIndex + $variantIndex) % 9 === 0)
                        ? 0
                        : 8 + (($blueprintIndex * 5 + $variantIndex * 7) % 85);

                    $ratingAvg = round(3.6 + ((($blueprintIndex * 2) + $variantIndex) % 14) / 10, 2);
                    $reviewCount = 12 + (($blueprintIndex * 19 + $variantIndex * 23) % 270);
                    $dateAdded = now()->subDays($blueprintIndex * 3 + $variantIndex);

                    $productId = DB::table('products')->insertGetId([
                        'title' => $title,
                        'price' => round($price, 2),
                        'discount' => $discount,
                        'rating_avg' => min($ratingAvg, 5),
                        'review_count' => $reviewCount,
                        'description' => $blueprint['description'],
                        'specifications' => $blueprint['specifications'],
                        'brand_id' => $brandIds[$brand],
                        'category_id' => $categoryIds[$blueprint['category']],
                        'qty' => $qty,
                        'is_active' => true,
                        'date_added' => $dateAdded,
                        'updated_at' => now(),
                    ]);

                    $titleImage = $imagePool[($blueprintIndex + $variantIndex) % count($imagePool)];
                    $galleryImage = $imagePool[($blueprintIndex + $variantIndex + 2) % count($imagePool)];

                    DB::table('product_images')->insert([
                        [
                            'product_id' => $productId,
                            'alt_text' => $title,
                            'image_path' => $titleImage,
                            'is_title' => true,
                        ],
                        [
                            'product_id' => $productId,
                            'alt_text' => $title . ' gallery image',
                            'image_path' => $galleryImage,
                            'is_title' => false,
                        ],
                    ]);

                    $reviewRows = [];
                    $reviewsToInsert = 2 + (($blueprintIndex + $variantIndex) % 3);

                    for ($i = 0; $i < $reviewsToInsert; $i++) {
                        $reviewRows[] = [
                            'product_id' => $productId,
                            'rating' => 3 + (($blueprintIndex + $variantIndex + $i) % 3),
                            'text' => $reviewTexts[($blueprintIndex + $variantIndex + $i) % count($reviewTexts)],
                            'created_at' => now()->subDays($i + $variantIndex),
                        ];
                    }

                    DB::table('reviews')->insert($reviewRows);
                }
            }
        });
    }
}
