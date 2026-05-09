@extends('layouts.app')

@section('title', 'Shipping & Payment')

@section('content')
@php
    $selectedShipping = old('shipping_method', session('checkout.shipping_method', 'Courier Delivery'));
    $selectedPayment = old('payment_method', session('checkout.payment_method', 'Credit Card'));

    $shippingPrices = [
        'Courier Delivery' => 15.0,
        'Pickup Point' => 0.0,
        'Personal Pickup' => 8.0,
    ];

    $selectedShippingPrice = (float) old(
        'shipping_price',
        $shippingPrices[$selectedShipping] ?? session('checkout.shipping_price', 15)
    );
@endphp
<main class="container-xl mt-4 pb-5">
    <h1 style="font-size: 2rem; margin-bottom: var(--spacing-lg); color: var(--text-primary);">
        <i class="fas fa-truck"></i> Shipping & Payment
    </h1>

    <section class="checkout-steps">
        <div class="step-item">
            <span class="step-num">1</span>
            <span class="step-label">Cart Summary</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item active">
            <span class="step-num">2</span>
            <span class="step-label">Shipping &amp; Payment</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <span class="step-num">3</span>
            <span class="step-label">Delivery Details</span>
        </div>
    </section>

    <form method="POST" action="{{ route('shipping.store') }}">
        @csrf

        <section class="row g-3 mt-2">
            <div class="col-12 col-lg-8">

                <!-- SHIPPING -->
                <div class="checkout-box mb-3">
                    <h2 class="checkout-box-title"><i class="fas fa-box"></i> Shipping Method</h2>

                    <label class="option-card {{ $selectedShipping === 'Courier Delivery' ? 'is-selected' : '' }}">
                        <input type="radio" name="shipping_method" value="Courier Delivery" data-price="15" {{ $selectedShipping === 'Courier Delivery' ? 'checked' : '' }}>
                        <span class="option-title"><i class="fas fa-truck"></i> Courier Delivery</span>
                        <span class="option-note">Delivery in 1-2 business days • €15.00</span>
                    </label>

                    <label class="option-card {{ $selectedShipping === 'Pickup Point' ? 'is-selected' : '' }}">
                        <input type="radio" name="shipping_method" value="Pickup Point" data-price="0" {{ $selectedShipping === 'Pickup Point' ? 'checked' : '' }}>
                        <span class="option-title"><i class="fas fa-location-dot"></i> Pickup Point</span>
                        <span class="option-note">Pickup points • FREE</span>
                    </label>

                    <label class="option-card {{ $selectedShipping === 'Personal Pickup' ? 'is-selected' : '' }}">
                        <input type="radio" name="shipping_method" value="Personal Pickup" data-price="8" {{ $selectedShipping === 'Personal Pickup' ? 'checked' : '' }}>
                        <span class="option-title"><i class="fas fa-store"></i> Personal Pickup</span>
                        <span class="option-note">Showroom pickup • €8.00</span>
                    </label>

                    <input type="hidden" name="shipping_price" id="shipping-price" value="{{ number_format($selectedShippingPrice, 2, '.', '') }}">
                </div>

                <!-- PAYMENT -->
                <div class="checkout-box">
                    <h2 class="checkout-box-title"><i class="fas fa-credit-card"></i> Payment Method</h2>

                    <label class="option-card {{ $selectedPayment === 'Credit Card' ? 'is-selected' : '' }}">
                        <input type="radio" name="payment_method" value="Credit Card" {{ $selectedPayment === 'Credit Card' ? 'checked' : '' }}>
                        <span class="option-title"><i class="fas fa-credit-card"></i> Credit Card</span>
                        <span class="option-note">Visa, Mastercard</span>
                    </label>

                    <label class="option-card {{ $selectedPayment === 'Bank Transfer' ? 'is-selected' : '' }}">
                        <input type="radio" name="payment_method" value="Bank Transfer" {{ $selectedPayment === 'Bank Transfer' ? 'checked' : '' }}>
                        <span class="option-title"><i class="fas fa-university"></i> Bank Transfer</span>
                        <span class="option-note">3-5 days</span>
                    </label>

                    <label class="option-card {{ $selectedPayment === 'PayPal' ? 'is-selected' : '' }}">
                        <input type="radio" name="payment_method" value="PayPal" {{ $selectedPayment === 'PayPal' ? 'checked' : '' }}>
                        <span class="option-title"><i class="fab fa-paypal"></i> PayPal</span>
                        <span class="option-note">Fast & secure</span>
                    </label>
                </div>

            </div>

            <!-- SUMMARY -->
            <div class="col-12 col-lg-4">
                <aside class="order-summary">
                    <h2><i class="fas fa-receipt"></i> Order Summary</h2>

                    <div class="summary-line">
                        <span>Subtotal:</span>
                        <strong id="summary-subtotal">€{{ number_format($subtotal, 2) }}</strong>
                    </div>

                    <div class="summary-line">
                        <span>VAT (20%):</span>
                        <strong id="summary-vat">€{{ number_format($vat, 2) }}</strong>
                    </div>

                    <div class="summary-line">
                        <span><i class="fas fa-shipping-fast"></i> Shipping:</span>
                        <strong id="summary-shipping">€{{ number_format($shipping, 2) }}</strong>
                    </div>

                    <hr>

                    <div class="summary-total">
                        <span>Total:</span>
                        <strong id="summary-total">€{{ number_format($total, 2) }}</strong>
                    </div>

                    <button type="submit" class="continue-btn">
                        Continue to Delivery
                    </button>

                    <a href="{{ route('cart.index') }}" class="continue-btn continue-btn-light">
                        Back
                    </a>

                    <p class="checkout-feedback" id="checkout-feedback"></p>
                </aside>
            </div>
        </section>
    </form>
</main>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    const shippingInputs = document.querySelectorAll('input[name="shipping_method"]');
    const shippingPriceInput = document.getElementById('shipping-price');

    const subtotal = parseFloat("{{ $subtotal }}");
    const vatRate = 0.2;

    const checkedShipping = document.querySelector('input[name="shipping_method"]:checked');
    if (checkedShipping) {
        const price = parseFloat(checkedShipping.dataset.price || '0');
        const vat = subtotal * vatRate;
        const total = subtotal + vat + price;

        shippingPriceInput.value = price;
        document.getElementById('summary-shipping').textContent = `€${price.toFixed(2)}`;
        document.getElementById('summary-vat').textContent = `€${vat.toFixed(2)}`;
        document.getElementById('summary-total').textContent = `€${total.toFixed(2)}`;
    }

    shippingInputs.forEach(input => {
        input.addEventListener('change', () => {
            const price = parseFloat(input.dataset.price);

            shippingPriceInput.value = price;

            const vat = subtotal * vatRate;
            const total = subtotal + vat + price;

            document.getElementById('summary-shipping').textContent = `€${price.toFixed(2)}`;
            document.getElementById('summary-vat').textContent = `€${vat.toFixed(2)}`;
            document.getElementById('summary-total').textContent = `€${total.toFixed(2)}`;
        });
    });

    // highlight selected
    document.querySelectorAll('.option-card').forEach(card => {
        card.addEventListener('click', () => {
            card.parentElement.querySelectorAll('.option-card').forEach(c => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
        });
    });

});
</script>
@endsection