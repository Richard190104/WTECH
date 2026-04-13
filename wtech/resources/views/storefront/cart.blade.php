@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<main class="container-xl mt-4 pb-5">
    <h1 style="font-size: 2rem; margin-bottom: var(--spacing-lg); color: var(--text-primary);">
        <i class="fas fa-shopping-cart"></i> Shopping Cart
    </h1>

    <section class="checkout-steps">
        <div class="step-item active">
            <span class="step-num">1</span>
            <span class="step-label">Cart Summary</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <span class="step-num">2</span>
            <span class="step-label">Shipping &amp; Payment</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <span class="step-num">3</span>
            <span class="step-label">Delivery Details</span>
        </div>
    </section>

    <section class="row g-3 mt-2">
        <div class="col-12 col-lg-8">
            @if (count($cartItems) > 0)
                <div class="cart-table">
                    <div class="cart-head">
                        <div>Image</div>
                        <div>Product</div>
                        <div>Quantity</div>
                        <div>Price</div>
                        <div style="text-align: center;"><i class="fas fa-trash-alt"></i></div>
                    </div>

                    @foreach ($cartItems as $item)
                        @php
                            $imageSrc = !empty($item['image_path'] ?? null)
                                ? (str_starts_with($item['image_path'], 'http')
                                    ? $item['image_path']
                                    : asset('storage/' . $item['image_path']))
                                : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80';

                            $lineTotal = ((float) $item['price']) * ((int) $item['quantity']);
                        @endphp

                        <div class="cart-row">
                            <div class="cart-thumb">
                                <img
                                    src="{{ $imageSrc }}"
                                    alt="{{ $item['title'] }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-md);"
                                >
                            </div>

                            <div class="cart-product">
                                <a href="{{ route('products.show', $item['product_id']) }}" class="cart-product-link">
                                    {{ $item['title'] }}
                                </a>
                            </div>

                            <div>
                            <form method="POST" action="{{ route('cart.setQuantity', $item['product_id']) }}" class="d-flex align-items-center gap-2">
                                @csrf

                                <button type="submit" name="action" value="decrease" class="btn btn-sm btn-outline-secondary">-</button>

                                <span
                                    
                                >
                                    {{ $item['quantity'] }}
                                </span>

                                <button type="submit" name="action" value="increase" class="btn btn-sm btn-outline-secondary">+</button>

                               
                            </form>
                        </div>

                            <div class="cart-price">
                                €{{ number_format($lineTotal, 2) }}
                            </div>

                            <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}">
                                @csrf
                                <button class="cart-remove" type="submit" title="Remove item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card p-4">
                    <h2 class="h5 mb-3">Your cart is empty</h2>
                    <p class="mb-0">Looks like you have not added any products yet.</p>
                </div>
            @endif

            <div style="margin-top: var(--spacing-lg);">
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <aside class="order-summary">
                <h2><i class="fas fa-receipt"></i> Order Summary</h2>

                <div class="summary-line">
                    <span>Subtotal:</span>
                    <strong>€{{ number_format($subtotal, 2) }}</strong>
                </div>

                <div class="summary-line">
                    <span>VAT (20%):</span>
                    <strong>€{{ number_format($vat, 2) }}</strong>
                </div>

                <div class="summary-line">
                    <span><i class="fas fa-shipping-fast"></i> Shipping:</span>
                    <strong>€{{ number_format($shipping, 2) }}</strong>
                </div>

                <hr>

                <div class="summary-total">
                    <span>Total:</span>
                    <strong>€{{ number_format($total, 2) }}</strong>
                </div>

                <a class="continue-btn {{ count($cartItems) === 0 ? 'disabled' : '' }}">
                    Continue to Shipping
                </a>

                <div style="margin-top: var(--spacing-lg); text-align: center; color: var(--text-secondary); font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: var(--spacing-sm);">
                    <i class="fas fa-lock"></i> Secure Checkout
                </div>
            </aside>
        </div>
    </section>
</main>
@endsection