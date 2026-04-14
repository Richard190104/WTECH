<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('brands')->insert([
            ['name' => 'Samsung'],
            ['name' => 'Apple'],
            ['name' => 'Sony'],
            ['name' => 'LG'],
            ['name' => 'Xiaomi'],
            ['name' => 'HP'],
            ['name' => 'Lenovo'],
            ['name' => 'Asus'],
        ]);

        $brandIds = DB::table('brands')->pluck('id', 'name');

        DB::table('categories')->insert([
            ['parent_id' => null, 'name' => 'Computers'],
            ['parent_id' => null, 'name' => 'Phones'],
            ['parent_id' => null, 'name' => 'Audio'],
            ['parent_id' => null, 'name' => 'Accessories'],
            ['parent_id' => null, 'name' => 'Gaming'],
            ['parent_id' => null, 'name' => 'TV'],
            ['parent_id' => null, 'name' => 'Wearables'],
            ['parent_id' => 1, 'name' => 'Laptops'],
            ['parent_id' => 2, 'name' => 'Smartphones'],
            ['parent_id' => 3, 'name' => 'Headphones'],
        ]);

        $categoryIds = DB::table('categories')->pluck('id', 'name');

        $products = [
            [
                'title' => 'HP ProBook 450 G9',
                'price' => 899.99,
                'discount' => 18.00,
                'rating_avg' => 4.80,
                'review_count' => 42,
                'description' => 'Professional laptop designed for business users.',
                'specifications' => 'Intel Core i7-1255U, 16GB RAM, 512GB SSD, 14 inch FHD',
                'brand_name' => 'HP',
                'category_name' => 'Laptops',
                'qty' => 14,
                'image_path' => 'images/HP-ProBook-450-G9_0b.jpg',
            ],
            [
                'title' => 'UltraBook Pro 15',
                'price' => 1299.99,
                'discount' => 15.00,
                'rating_avg' => 4.90,
                'review_count' => 256,
                'description' => 'High performance laptop for professionals.',
                'specifications' => 'Intel Core i7, 32GB RAM, 1TB SSD, 15.6 inch display',
                'brand_name' => 'Apple',
                'category_name' => 'Laptops',
                'qty' => 11,
                'image_path' => 'images/ultrabook pro.jpg',
            ],
            [
                'title' => 'SmartPhone X12',
                'price' => 899.99,
                'discount' => 10.00,
                'rating_avg' => 4.70,
                'review_count' => 89,
                'description' => '5G enabled smartphone with triple camera.',
                'specifications' => 'OLED display, 256GB storage, 8GB RAM',
                'brand_name' => 'Samsung',
                'category_name' => 'Smartphones',
                'qty' => 48,
                'image_path' => 'images/smartphone x12.jpg',
            ],
            [
                'title' => 'Wireless Headset Pro',
                'price' => 199.99,
                'discount' => 30.00,
                'rating_avg' => 4.90,
                'review_count' => 342,
                'description' => 'Active noise cancellation, 30h battery.',
                'specifications' => 'Bluetooth 5.3, ANC, USB-C charging',
                'brand_name' => 'Sony',
                'category_name' => 'Headphones',
                'qty' => 56,
                'image_path' => 'images/silly cat.jpg',
            ],
            [
                'title' => 'Gaming Mouse RGB',
                'price' => 79.99,
                'discount' => 12.00,
                'rating_avg' => 4.60,
                'review_count' => 175,
                'description' => 'Precision optical sensor, 8000 DPI.',
                'specifications' => 'RGB lighting, ergonomic design, 8 buttons',
                'brand_name' => 'Asus',
                'category_name' => 'Accessories',
                'qty' => 92,
                'image_path' => 'images/silly cat.jpg',
            ],
            [
                'title' => '4K Monitor 27"',
                'price' => 549.99,
                'discount' => 20.00,
                'rating_avg' => 4.40,
                'review_count' => 12,
                'description' => 'Professional-grade display with HDR.',
                'specifications' => '27 inch 4K UHD, HDR, IPS panel',
                'brand_name' => 'LG',
                'category_name' => 'Accessories',
                'qty' => 17,
                'image_path' => 'images/silly cat.jpg',
            ],
            [
                'title' => 'Mechanical Keyboard',
                'price' => 149.99,
                'discount' => 0.00,
                'rating_avg' => 4.80,
                'review_count' => 37,
                'description' => 'RGB mechanical keyboard for work and gaming.',
                'specifications' => 'Mechanical switches, RGB, USB-C',
                'brand_name' => 'Lenovo',
                'category_name' => 'Accessories',
                'qty' => 41,
                'image_path' => 'images/silly cat.jpg',
            ],
            [
                'title' => 'HD Webcam 4K',
                'price' => 129.99,
                'discount' => 0.00,
                'rating_avg' => 4.20,
                'review_count' => 6,
                'description' => 'Auto focus webcam with stereo microphones.',
                'specifications' => '4K recording, autofocus, built-in mic',
                'brand_name' => 'HP',
                'category_name' => 'Accessories',
                'qty' => 63,
                'image_path' => 'images/silly cat.jpg',
            ],
            [
                'title' => 'Tablet Pro 12.9',
                'price' => 649.99,
                'discount' => 0.00,
                'rating_avg' => 4.80,
                'review_count' => 211,
                'description' => 'Super Retina display tablet for creativity.',
                'specifications' => '12.9 inch display, stylus support, 256GB',
                'brand_name' => 'Apple',
                'category_name' => 'Phones',
                'qty' => 24,
                'image_path' => 'images/silly cat.jpg',
            ],
            [
                'title' => 'Smart Speaker',
                'price' => 89.99,
                'discount' => 10.00,
                'rating_avg' => 4.60,
                'review_count' => 156,
                'description' => 'Voice control enabled smart speaker.',
                'specifications' => 'Wi-Fi, Bluetooth, voice assistant',
                'brand_name' => 'Xiaomi',
                'category_name' => 'Audio',
                'qty' => 35,
                'image_path' => 'images/silly cat.jpg',
            ],
            [
                'title' => 'Samsung Galaxy Watch 6',
                'price' => 329.99,
                'discount' => 0.00,
                'rating_avg' => 4.70,
                'review_count' => 140,
                'description' => 'Smartwatch with health and fitness tracking.',
                'specifications' => 'AMOLED, GPS, heart rate monitor',
                'brand_name' => 'Samsung',
                'category_name' => 'Wearables',
                'qty' => 29,
                'image_path' => 'images/silly cat.jpg',
            ],
            [
                'title' => 'Xiaomi Redmi Note 13',
                'price' => 299.99,
                'discount' => 15.00,
                'rating_avg' => 4.50,
                'review_count' => 180,
                'description' => 'Affordable smartphone with solid features.',
                'specifications' => 'AMOLED display, 128GB storage, 8GB RAM',
                'brand_name' => 'Xiaomi',
                'category_name' => 'Smartphones',
                'qty' => 72,
                'image_path' => 'images/smartphone x12.jpg',
            ],
            [
                'title' => 'LG OLED C3 55',
                'price' => 1499.99,
                'discount' => 100.00,
                'rating_avg' => 4.70,
                'review_count' => 95,
                'description' => 'Premium OLED TV with deep blacks.',
                'specifications' => '55 inch OLED, 4K, HDR',
                'brand_name' => 'LG',
                'category_name' => 'TV',
                'qty' => 9,
                'image_path' => 'images/Playdock 5 console.jpg',
            ],
        ];

        foreach ($products as $product) {
            $productId = DB::table('products')->insertGetId([
                'title' => $product['title'],
                'price' => $product['price'],
                'discount' => $product['discount'],
                'rating_avg' => $product['rating_avg'],
                'review_count' => $product['review_count'],
                'description' => $product['description'],
                'specifications' => $product['specifications'],
                'brand_id' => $brandIds[$product['brand_name']],
                'category_id' => $categoryIds[$product['category_name']],
                'qty' => $product['qty'],
                'is_active' => true,
                'date_added' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_images')->insert([
                'product_id' => $productId,
                'alt_text' => $product['title'],
                'image_path' => $product['image_path'],
                'is_title' => true,
            ]);
        }

        User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'customer',
        ]);
    }
}
