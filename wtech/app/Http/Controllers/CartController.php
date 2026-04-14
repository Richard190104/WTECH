<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cartItems = [];
        $subtotal = 0.0;

        if (auth()->check()) {
            $cartId = $this->getOrCreateCartIdForUser(auth()->id());

            $items = DB::table('cart_items')
                ->join('products', 'products.id', '=', 'cart_items.product_id')
                ->leftJoin('product_images as title_images', function ($join) {
                    $join->on('title_images.product_id', '=', 'products.id')
                        ->where('title_images.is_title', true);
                })
                ->where('cart_items.cart_id', $cartId)
                ->select([
                    'cart_items.product_id',
                    'cart_items.quantity',
                    'cart_items.unit_price',
                    'products.title',
                    'title_images.image_path as image_path',
                ])
                ->get();

            foreach ($items as $item) {
                $lineTotal = ((float) $item->unit_price) * ((int) $item->quantity);
                $subtotal += $lineTotal;

                $cartItems[] = [
                    'product_id' => $item->product_id,
                    'title' => $item->title,
                    'price' => (float) $item->unit_price,
                    'quantity' => (int) $item->quantity,
                    'image_path' => $item->image_path,
                ];
            }
        } else {
            $cart = session('cart', []);

            foreach ($cart as $productId => $quantity) {
                $product = DB::table('products')
                    ->leftJoin('product_images as title_images', function ($join) {
                        $join->on('title_images.product_id', '=', 'products.id')
                            ->where('title_images.is_title', true);
                    })
                    ->where('products.id', $productId)
                    ->select([
                        'products.id',
                        'products.title',
                        'products.price',
                        'title_images.image_path as image_path',
                    ])
                    ->first();

                if (! $product) {
                    continue;
                }

                $lineTotal = ((float) $product->price) * ((int) $quantity);
                $subtotal += $lineTotal;

                $cartItems[] = [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'price' => (float) $product->price,
                    'quantity' => (int) $quantity,
                    'image_path' => $product->image_path,
                ];
            }
        }

        $vat = round($subtotal * 0.20, 2);
        $shipping = $subtotal > 50 || $subtotal == 0 ? 0.00 : 15.00;
        $total = round($subtotal + $vat + $shipping, 2);

        return view('storefront.cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'vat' => $vat,
            'shipping' => $shipping,
            'total' => $total,
        ]);
    }

   public function add(Request $request, int $productId): RedirectResponse
{
    $quantity = max(1, (int) $request->input('quantity', 1));

    $product = DB::table('products')
        ->where('id', $productId)
        ->where('is_active', true)
        ->first();

    abort_if(! $product, 404);

    if (auth()->check()) {
        $cartId = $this->getOrCreateCartIdForUser(auth()->id());

        $existingItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            DB::table('cart_items')
                ->where('id', $existingItem->id)
                ->update([
                    'quantity' => $existingItem->quantity + $quantity,
                ]);
        } else {
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $product->price,
            ]);
        }

        return back()->with('success', 'Product added to cart.');
    }

    $cart = session('cart', []);
    $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
    session(['cart' => $cart]);

    return back()->with('success', 'Product added to cart.');
}

    public function update(Request $request, int $productId): RedirectResponse
    {
        $action = $request->input('action');

        if (auth()->check()) {
            $cartId = $this->getOrCreateCartIdForUser(auth()->id());

            $item = DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->where('product_id', $productId)
                ->first();

            if (! $item) {
                return back();
            }

            if ($action === 'increase') {
                DB::table('cart_items')
                    ->where('id', $item->id)
                    ->update([
                        'quantity' => $item->quantity + 1,
                    ]);
            } elseif ($action === 'decrease') {
                $newQty = $item->quantity - 1;

                if ($newQty <= 0) {
                    DB::table('cart_items')->where('id', $item->id)->delete();
                } else {
                    DB::table('cart_items')
                        ->where('id', $item->id)
                        ->update([
                            'quantity' => $newQty,
                        ]);
                }
            }

            return back();
        }

        $cart = session('cart', []);

        if (! isset($cart[$productId])) {
            return back();
        }

        if ($action === 'increase') {
            $cart[$productId]++;
        } elseif ($action === 'decrease') {
            $cart[$productId]--;

            if ($cart[$productId] <= 0) {
                unset($cart[$productId]);
            }
        }

        session(['cart' => $cart]);

        return back();
    }

    public function remove(int $productId): RedirectResponse
    {
        if (auth()->check()) {
            $cartId = $this->getOrCreateCartIdForUser(auth()->id());

            DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->where('product_id', $productId)
                ->delete();

            return back();
        }

        $cart = session('cart', []);
        unset($cart[$productId]);
        session(['cart' => $cart]);

        return back();
    }

    protected function getOrCreateCartIdForUser(int $userId): int
    {
        $cart = DB::table('carts')->where('user_id', $userId)->first();

        if ($cart) {
            return $cart->id;
        }

        return DB::table('carts')->insertGetId([
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    public function setQuantity(Request $request, int $productId): RedirectResponse
{
    $action = $request->input('action');
    $requestedQty = max(1, (int) $request->input('quantity', 1));

    if (auth()->check()) {
        $cartId = $this->getOrCreateCartIdForUser(auth()->id());

        $item = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if (! $item) {
            return back();
        }

        $newQty = $item->quantity;

        if ($action === 'increase') {
            $newQty = $item->quantity + 1;
        } elseif ($action === 'decrease') {
            $newQty = $item->quantity - 1;
        } elseif ($action === 'set') {
            $newQty = $requestedQty;
        }

        if ($newQty <= 0) {
            DB::table('cart_items')
                ->where('id', $item->id)
                ->delete();
        } else {
            DB::table('cart_items')
                ->where('id', $item->id)
                ->update([
                    'quantity' => $newQty,
                ]);
        }

        return back();
    }

    $cart = session('cart', []);

    if (! isset($cart[$productId])) {
        return back();
    }

    if ($action === 'increase') {
        $cart[$productId]++;
    } elseif ($action === 'decrease') {
        $cart[$productId]--;
    } elseif ($action === 'set') {
        $cart[$productId] = $requestedQty;
    }

    if ($cart[$productId] <= 0) {
        unset($cart[$productId]);
    }

    session(['cart' => $cart]);

    return back();
}
}