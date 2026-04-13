@extends('layouts.app')

@section('title', 'Delivery Details')

@section('content')
<main class="container-xl mt-4 pb-5">
    <h1 style="font-size: 2rem; margin-bottom: var(--spacing-lg); color: var(--text-primary);">
        <i class="fas fa-map-location-dot"></i> Delivery Details
    </h1>

    <section class="checkout-steps">
        <div class="step-item">
            <span class="step-num">1</span>
            <span class="step-label">Cart Summary</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <span class="step-num">2</span>
            <span class="step-label">Shipping &amp; Payment</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item active">
            <span class="step-num">3</span>
            <span class="step-label">Delivery Details</span>
        </div>
    </section>

    <section>
        @if ($errors->any())
            <div class="login-feedback error" style="display:block; margin-bottom: 16px;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="delivery-form" id="delivery-form" method="POST" action="{{ route('delivery.store') }}" novalidate>
            @csrf

            <h2><i class="fas fa-user"></i> Recipient Information</h2>

            <div class="delivery-grid-2">
                <div>
                    <label for="first-name"><i class="fas fa-user"></i> First Name</label>
                    <input
                        id="first-name"
                        name="first_name"
                        type="text"
                        required
                        placeholder="John"
                        value="{{ old('first_name', $user?->first_name ?? '') }}"
                    >
                </div>

                <div>
                    <label for="last-name"><i class="fas fa-user"></i> Last Name</label>
                    <input
                        id="last-name"
                        name="last_name"
                        type="text"
                        required
                        placeholder="Doe"
                        value="{{ old('last_name', $user?->last_name ?? '') }}"
                    >
                </div>
            </div>

            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                required
                placeholder="john@example.com"
                value="{{ old('email', $user?->email ?? '') }}"
            >

            <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
            <input
                id="phone"
                name="phone"
                type="tel"
                required
                placeholder="+421 900 000 000"
                value="{{ old('phone') }}"
            >

            <label for="street-address"><i class="fas fa-road"></i> Street Address</label>
            <input
                id="street-address"
                name="street_address"
                type="text"
                required
                placeholder="123 Main Street"
                value="{{ old('street_address') }}"
            >

            <div class="delivery-grid-2">
                <div>
                    <label for="city"><i class="fas fa-city"></i> City</label>
                    <input
                        id="city"
                        name="city"
                        type="text"
                        required
                        placeholder="Bratislava"
                        value="{{ old('city') }}"
                    >
                </div>

                <div>
                    <label for="zip-code"><i class="fas fa-mailbox"></i> ZIP Code</label>
                    <input
                        id="zip-code"
                        name="zip_code"
                        type="text"
                        required
                        placeholder="81101"
                        value="{{ old('zip_code') }}"
                    >
                </div>
            </div>

            <label for="country"><i class="fas fa-globe"></i> Country</label>
            <input
                id="country"
                name="country"
                type="text"
                required
                placeholder="Slovakia"
                value="{{ old('country', 'Slovakia') }}"
            >

            <label for="notes"><i class="fas fa-sticky-note"></i> Additional Notes (optional)</label>
            <textarea
                id="notes"
                name="notes"
                rows="3"
                placeholder="Any special delivery instructions or preferences..."
            >{{ old('notes') }}</textarea>

            <div class="delivery-recap" id="delivery-recap">
                <p>
                    <strong><i class="fas fa-box"></i> Shipping Method:</strong>
                    <span id="recap-shipping">{{ $shippingMethod ?? session('shipping_method', 'Courier Delivery') }}</span>
                </p>

                <p>
                    <strong><i class="fas fa-credit-card"></i> Payment Method:</strong>
                    <span id="recap-payment">{{ $paymentMethod ?? session('payment_method', 'Credit Card') }}</span>
                </p>

                <p style="border-top: 1px solid var(--border); padding-top: var(--spacing-md); margin-top: var(--spacing-md); font-size: 1.1rem;">
                    <strong><i class="fas fa-money-bill"></i> Total:</strong>
                    <span id="recap-total" style="color: var(--primary);">
                        €{{ number_format($total ?? 0, 2) }}
                    </span>
                </p>
            </div>

            <div class="delivery-actions">
                <a href="{{ route('shipping') }}" class="continue-btn continue-btn-light">
                    Back
                </a>

                <button type="submit" class="continue-btn" id="place-order">
                    Place Order
                </button>
            </div>

            @if (session('success'))
                <p class="checkout-feedback" id="delivery-feedback" aria-live="polite">{{ session('success') }}</p>
            @else
                <p class="checkout-feedback" id="delivery-feedback" aria-live="polite"></p>
            @endif
        </form>
    </section>
</main>
@endsection