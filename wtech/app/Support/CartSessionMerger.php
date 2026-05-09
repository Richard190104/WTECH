<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartSessionMerger
{
    public function mergeGuestSessionCartIntoUserCart(Request $request, int $userId, bool $replaceUserCart): void
    {
        $sessionCart = $request->session()->get('cart', []);

        if (! is_array($sessionCart) || empty($sessionCart)) {
            return;
        }

        if (! $replaceUserCart) {
            // Keep the existing DB cart outside checkout-resume login flow.
            $request->session()->forget('cart');
            return;
        }

        DB::transaction(function () use ($sessionCart, $userId) {
            $cart = DB::table('carts')->where('user_id', $userId)->first();

            if (! $cart) {
                $cartId = DB::table('carts')->insertGetId([
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $cartId = $cart->id;
            }

            // When a guest logs in with an active guest cart, that guest cart becomes the source of truth.
            DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->delete();

            foreach ($sessionCart as $productId => $requestedQuantity) {
                $productId = (int) $productId;
                $requestedQuantity = max(0, (int) $requestedQuantity);

                if ($productId <= 0 || $requestedQuantity <= 0) {
                    continue;
                }

                $product = DB::table('products')
                    ->where('id', $productId)
                    ->where('is_active', true)
                    ->select(['id', 'price', 'qty'])
                    ->first();

                if (! $product) {
                    continue;
                }

                $maxAvailable = max(0, (int) $product->qty);
                if ($maxAvailable <= 0) {
                    continue;
                }

                $insertQuantity = min($maxAvailable, $requestedQuantity);

                DB::table('cart_items')->insert([
                    'cart_id' => $cartId,
                    'product_id' => $productId,
                    'quantity' => $insertQuantity,
                    'unit_price' => $product->price,
                ]);
            }
        });

        $request->session()->forget('cart');
    }
}
