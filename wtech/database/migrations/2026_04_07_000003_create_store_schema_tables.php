<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 120);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->decimal('price', 12, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->text('description')->nullable();
            $table->text('specifications')->nullable();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->integer('qty');
            $table->boolean('is_active')->default(true);
            $table->timestamp('date_added')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('rating');
            $table->text('text')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('alt_text', 255)->nullable();
            $table->string('image_path', 500);
            $table->boolean('is_title')->default(false);
        });

        Schema::create('delivery_details', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255);
            $table->string('phone', 30);
            $table->string('street', 200);
            $table->string('city', 120);
            $table->string('country', 120);
            $table->string('zip', 20);
            $table->string('note', 500)->nullable();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_created')->useCurrent();
            $table->foreignId('delivery_details_id')->constrained('delivery_details')->cascadeOnDelete();
            $table->string('shipping_method', 30);
            $table->string('payment_method', 30);
            $table->string('status', 30);
            $table->decimal('price_total', 12, 2);
            $table->decimal('price_subtotal', 12, 2);
            $table->decimal('price_vat', 12, 2);
            $table->decimal('price_shipping', 12, 2);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('delivery_details');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};
