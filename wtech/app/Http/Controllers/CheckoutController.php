<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function shipping(Request $request)
    {
        $subtotal = 0.0;

        if (auth()->check()) {
            $cart = DB::table('carts')
                ->where('user_id', auth()->id())
                ->first();

            if ($cart) {
                $subtotal = (float) DB::table('cart_items')
                    ->where('cart_id', $cart->id)
                    ->selectRaw('COALESCE(SUM(unit_price * quantity), 0) as subtotal')
                    ->value('subtotal');
            }
        } else {
            $cart = session('cart', []);

            foreach ($cart as $productId => $quantity) {
                $product = DB::table('products')
                    ->where('id', $productId)
                    ->where('is_active', true)
                    ->select('price')
                    ->first();

                if (! $product) {
                    continue;
                }

                $subtotal += ((float) $product->price) * ((int) $quantity);
            }
        }

        $vat = round($subtotal * 0.20, 2);
        $shipping = (float) session('checkout.shipping_price', 15);
        $total = round($subtotal + $vat + $shipping, 2);

        return view('storefront.shipping', compact('subtotal', 'vat', 'shipping', 'total'));
    }

    public function storeShipping(Request $request)
    {
        $request->validate([
            'shipping_method' => ['required', 'string'],
            'shipping_price' => ['required', 'numeric'],
            'payment_method' => ['required', 'string'],
        ]);

        session([
            'checkout.shipping_method' => $request->shipping_method,
            'checkout.shipping_price' => (float) $request->shipping_price,
            'checkout.payment_method' => $request->payment_method,
        ]);

        return redirect()->route('delivery');
    }
    public function delivery(Request $request)
{
    $subtotal = 0.0;

    if (auth()->check()) {
        $cart = DB::table('carts')->where('user_id', auth()->id())->first();

        if ($cart) {
            $subtotal = (float) DB::table('cart_items')
                ->where('cart_id', $cart->id)
                ->selectRaw('COALESCE(SUM(unit_price * quantity), 0) as subtotal')
                ->value('subtotal');
        }
    } else {
        $cart = session('cart', []);

        foreach ($cart as $productId => $quantity) {
            $product = DB::table('products')
                ->where('id', $productId)
                ->where('is_active', true)
                ->select('price')
                ->first();

            if (! $product) {
                continue;
            }

            $subtotal += ((float) $product->price) * ((int) $quantity);
        }
    }

    $vat = round($subtotal * 0.20, 2);
    $shipping = (float) session('checkout.shipping_price', 15);
    $total = round($subtotal + $vat + $shipping, 2);

    return view('storefront.delivery', [
        'user' => auth()->user(),
        'shippingMethod' => session('checkout.shipping_method'),
        'paymentMethod' => session('checkout.payment_method'),
        'total' => $total,
    ]);
}

public function storeDelivery(Request $request)
{
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:100'],
        'last_name' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['required', 'string', 'max:30'],
        'street_address' => ['required', 'string', 'max:200'],
        'city' => ['required', 'string', 'max:120'],
        'country' => ['required', 'string', 'max:120'],
        'zip_code' => ['required', 'string', 'max:20'],
        'notes' => ['nullable', 'string', 'max:500'],
    ]);

    $shippingMethod = session('checkout.shipping_method');
    $paymentMethod = session('checkout.payment_method');
    $shippingPrice = (float) session('checkout.shipping_price', 15);

    if (! $shippingMethod || ! $paymentMethod) {
        return redirect()
            ->route('shipping')
            ->withErrors(['checkout' => 'Please complete shipping and payment first.']);
    }

    $cartItems = [];
    $subtotal = 0.0;
    $cartId = null;

    if (auth()->check()) {
        $cart = DB::table('carts')
            ->where('user_id', auth()->id())
            ->first();

        if ($cart) {
            $cartId = $cart->id;

            $items = DB::table('cart_items')
                ->join('products', 'products.id', '=', 'cart_items.product_id')
                ->where('cart_items.cart_id', $cartId)
                ->select([
                    'cart_items.product_id',
                    'cart_items.quantity',
                    'cart_items.unit_price',
                    'products.qty as stock_qty',
                    'products.title',
                    'products.is_active',
                ])
                ->get();

            foreach ($items as $item) {
                if (! $item->is_active) {
                    return back()->withErrors([
                        'checkout' => "Product '{$item->title}' is no longer available.",
                    ]);
                }

                if ((int) $item->quantity > (int) $item->stock_qty) {
                    return back()->withErrors([
                        'checkout' => "Not enough stock for '{$item->title}'.",
                    ]);
                }

                $lineTotal = ((float) $item->unit_price) * ((int) $item->quantity);
                $subtotal += $lineTotal;

                $cartItems[] = [
                    'product_id' => (int) $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => $lineTotal,
                ];
            }
        }
    } else {
        $sessionCart = session('cart', []);

        foreach ($sessionCart as $productId => $quantity) {
            $product = DB::table('products')
                ->where('id', $productId)
                ->where('is_active', true)
                ->select(['id', 'title', 'price', 'qty'])
                ->first();

            if (! $product) {
                return back()->withErrors([
                    'checkout' => 'One of the products in your cart is no longer available.',
                ]);
            }

            if ((int) $quantity > (int) $product->qty) {
                return back()->withErrors([
                    'checkout' => "Not enough stock for '{$product->title}'.",
                ]);
            }

            $unitPrice = (float) $product->price;
            $lineTotal = $unitPrice * (int) $quantity;
            $subtotal += $lineTotal;

            $cartItems[] = [
                'product_id' => (int) $product->id,
                'quantity' => (int) $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }
    }

    if (count($cartItems) === 0) {
        return redirect()
            ->route('cart.index')
            ->withErrors(['checkout' => 'Your cart is empty.']);
    }

    $vat = round($subtotal * 0.20, 2);
    $total = round($subtotal + $vat + $shippingPrice, 2);

    $orderId = DB::transaction(function () use (
        $validated,
        $shippingMethod,
        $paymentMethod,
        $shippingPrice,
        $subtotal,
        $vat,
        $total,
        $cartItems,
        $cartId
    ) {
        $deliveryDetailsId = DB::table('delivery_details')->insertGetId([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'street' => $validated['street_address'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'zip' => $validated['zip_code'],
            'note' => $validated['notes'] ?? null,
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => auth()->id(),
            'date_created' => now(),
            'delivery_details_id' => $deliveryDetailsId,
            'shipping_method' => $shippingMethod,
            'payment_method' => $paymentMethod,
            'status' => 'CREATED',
            'price_total' => $total,
            'price_subtotal' => $subtotal,
            'price_vat' => $vat,
            'price_shipping' => $shippingPrice,
        ]);

        foreach ($cartItems as $item) {
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
            ]);

            DB::table('products')
                ->where('id', $item['product_id'])
                ->decrement('qty', $item['quantity']);
        }

        if (auth()->check() && $cartId) {
            DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->delete();
        }

        return $orderId;
    });

    if (! auth()->check()) {
        session()->forget('cart');
    }

    session()->forget([
        'checkout.shipping_method',
        'checkout.shipping_price',
        'checkout.payment_method',
    ]);

    return redirect('/')
        ->with('success', "Order #{$orderId} was created successfully.");
}
}