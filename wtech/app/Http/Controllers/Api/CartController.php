<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_id' => ['nullable', 'integer', 'min:1'],
            'user_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $cart = $this->resolveCart($validated['cart_id'] ?? null, $validated['user_id'] ?? null);

        if (! $cart) {
            return response()->json([
                'message' => 'Cart not found.',
                'cart' => null,
                'items' => [],
                'summary' => $this->emptySummary(),
            ]);
        }

        return response()->json($this->buildCartResponse($cart));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'cart_id' => ['nullable', 'integer', 'min:1'],
            'user_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $product = DB::table('products')->where('id', $validated['product_id'])->first();

        if (! $product || ! $product->is_active) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $unitPrice = $this->calculateUnitPrice($product);
        $availableQuantity = (int) $product->qty;

        $cart = $this->resolveCart(
            $validated['cart_id'] ?? null,
            $validated['user_id'] ?? null,
            true
        );

        if (! $cart) {
            return response()->json([
                'message' => 'Unable to resolve cart.',
            ], 422);
        }

        $existingItem = DB::table('cart_items')
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $nextQuantity = $quantity;
        if ($existingItem) {
            $nextQuantity = (int) $existingItem->quantity + $quantity;
        }

        if ($nextQuantity > $availableQuantity) {
            return response()->json([
                'message' => 'Requested quantity exceeds available stock.',
                'available_quantity' => $availableQuantity,
            ], 422);
        }

        DB::transaction(function () use ($cart, $product, $quantity, $unitPrice, $existingItem, $nextQuantity) {
            if ($existingItem) {
                DB::table('cart_items')
                    ->where('id', $existingItem->id)
                    ->update([
                        'quantity' => $nextQuantity,
                        'unit_price' => $unitPrice,
                    ]);
            } else {
                DB::table('cart_items')->insert([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);
            }

            DB::table('carts')
                ->where('id', $cart->id)
                ->update([
                    'updated_at' => now(),
                ]);
        });

        $freshCart = $this->resolveCart($cart->id, null);

        return response()->json([
            'message' => $existingItem ? 'Cart item updated.' : 'Cart item added.',
            ...$this->buildCartResponse($freshCart),
        ], $existingItem ? 200 : 201);
    }

    private function resolveCart(?int $cartId = null, ?int $userId = null, bool $createIfMissing = false)
    {
        $cart = null;

        if ($cartId) {
            $cart = DB::table('carts')->where('id', $cartId)->first();
        } elseif ($userId) {
            $cart = DB::table('carts')
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->first();
        }

        if (! $cart && $createIfMissing) {
            $cartId = DB::table('carts')->insertGetId([
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cart = DB::table('carts')->where('id', $cartId)->first();
        }

        return $cart;
    }

    private function buildCartResponse(object $cart): array
    {
        $items = DB::table('cart_items')
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('product_images as title_images', function ($join) {
                $join->on('title_images.product_id', '=', 'products.id')
                    ->where('title_images.is_title', true);
            })
            ->where('cart_items.cart_id', $cart->id)
            ->orderBy('cart_items.id')
            ->select([
                'cart_items.id as cart_item_id',
                'cart_items.product_id',
                'cart_items.quantity',
                'cart_items.unit_price',
                'products.title',
                'products.price as product_price',
                'products.discount',
                'products.qty as available_quantity',
                'brands.name as brand_name',
                'categories.name as category_name',
                'title_images.image_path as image_path',
                'title_images.alt_text as image_alt',
            ])
            ->get()
            ->map(function ($item) {
                $lineTotal = round(((float) $item->unit_price) * ((int) $item->quantity), 2);

                return [
                    'cart_item_id' => $item->cart_item_id,
                    'product_id' => $item->product_id,
                    'title' => $item->title,
                    'brand_name' => $item->brand_name,
                    'category_name' => $item->category_name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => $lineTotal,
                    'available_quantity' => (int) $item->available_quantity,
                    'discount' => (float) $item->discount,
                    'image_path' => $item->image_path,
                    'image_alt' => $item->image_alt,
                ];
            })
            ->all();

        $summary = $this->calculateSummary($items);

        return [
            'cart' => [
                'id' => $cart->id,
                'user_id' => $cart->user_id,
            ],
            'items' => $items,
            'summary' => $summary,
        ];
    }

    private function calculateSummary(array $items): array
    {
        $subtotal = round(array_reduce($items, function (float $carry, array $item) {
            return $carry + (float) $item['line_total'];
        }, 0.0), 2);

        $vat = round($subtotal * 0.20, 2);
        $shipping = $subtotal > 0 ? 0.00 : 0.00;
        $total = round($subtotal + $vat + $shipping, 2);

        return [
            'items_count' => array_reduce($items, function (int $carry, array $item) {
                return $carry + (int) $item['quantity'];
            }, 0),
            'subtotal' => $subtotal,
            'vat' => $vat,
            'shipping' => $shipping,
            'total' => $total,
        ];
    }

    private function emptySummary(): array
    {
        return [
            'items_count' => 0,
            'subtotal' => 0.00,
            'vat' => 0.00,
            'shipping' => 0.00,
            'total' => 0.00,
        ];
    }

    private function calculateUnitPrice(object $product): float
    {
        $basePrice = (float) $product->price;
        $discountPercent = (float) $product->discount;

        if ($discountPercent <= 0) {
            return round($basePrice, 2);
        }

        return round($basePrice - ($basePrice * ($discountPercent / 100)), 2);
    }
}
