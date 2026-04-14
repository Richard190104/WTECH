@extends('layouts.app')

@section('title', $product->title)

@section('content')
@php
    $mainImage = $images->firstWhere('is_title', true) ?? $images->first();
    $mainImageSrc = !empty($mainImage?->image_path)
        ? (str_starts_with($mainImage->image_path, 'http')
            ? $mainImage->image_path
            : asset('storage/' . $mainImage->image_path))
        : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=1200&q=80';

    $rating = (float) ($product->rating_avg ?? 0);
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;

    $descriptionText = $product->description ?? 'No description available.';
    $specificationsText = $product->specifications ?? 'No specifications available.';
@endphp

<main class="container-xl mt-3 pb-5">
    <nav class="pdp-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
        <span>&gt;</span>
        <a href="{{ route('products.index') }}">Products</a>
        <span>&gt;</span>
        <span>{{ $product->title }}</span>
    </nav>

    <section class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="pdp-main-image" aria-live="polite">
                <img
                    src="{{ $mainImageSrc }}"
                    alt="{{ $mainImage?->alt_text ?? $product->title }}"
                    style="width: 100%; height: 100%; object-fit: cover;"
                    id="main-product-image"
                >
            </div>

            <div class="pdp-thumbs" aria-label="Product images">
                @foreach ($images as $image)
                    @php
                        $thumbSrc = !empty($image->image_path)
                            ? (str_starts_with($image->image_path, 'http')
                                ? $image->image_path
                                : asset('storage/' . $image->image_path))
                            : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=600&q=80';
                    @endphp

                    <button
                        type="button"
                        class="pdp-thumb-btn"
                        onclick="document.getElementById('main-product-image').src='{{ $thumbSrc }}'"
                        aria-label="Show image {{ $loop->iteration }}"
                    >
                        <img
                            src="{{ $thumbSrc }}"
                            alt="{{ $image->alt_text ?? $product->title }}"
                            style="width: 72px; height: 72px; object-fit: cover; border-radius: 8px;"
                        >
                    </button>
                @endforeach
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div style="background: var(--bg); padding: var(--spacing-lg); border-radius: var(--radius-lg); border: 1px solid var(--border);">
                <div style="display: flex; gap: var(--spacing-sm); align-items: center; margin-bottom: var(--spacing-md); flex-wrap: wrap;">
                    <span
                        id="pdp-stock-badge"
                        style="background: {{ $stock['in_stock'] ? 'var(--success)' : 'var(--danger)' }}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"
                    >
                        {{ $stock['in_stock'] ? 'In Stock' : 'Out of Stock' }}
                    </span>

                    <span style="color: var(--text-secondary); font-size: 0.85rem;">
                        <i class="fas fa-shipping-fast"></i> Free Shipping on Orders Over $50
                    </span>
                </div>

                <h1 class="pdp-title">{{ $product->title }}</h1>

                <p class="pdp-rating">
                    <span class="stars">
                        @for ($i = 0; $i < 5; $i++)
                            @if ($i < $fullStars)
                                <i class="fas fa-star"></i>
                            @elseif ($i === $fullStars && $hasHalfStar)
                                <i class="fas fa-star-half-alt"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </span>
                    <span>{{ number_format($rating, 1) }}</span>
                    <span>({{ $product->review_count ?? 0 }} reviews)</span>
                </p>

                <div style="margin: var(--spacing-lg) 0; padding: var(--spacing-lg) 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
                    <div style="display: flex; align-items: baseline; gap: var(--spacing-md); margin-bottom: var(--spacing-sm); flex-wrap: wrap;">
                        <p class="pdp-price" style="margin: 0;">
                            €{{ number_format((float) $pricing['final_price'], 2) }}
                        </p>

                        @if (($pricing['discount_percent'] ?? 0) > 0)
                            <span id="pdp-base-price" style="text-decoration: line-through; color: var(--text-secondary); font-size: 1rem;">
                                €{{ number_format((float) $pricing['base_price'], 2) }}
                            </span>

                            <span id="pdp-discount-badge" style="background: var(--danger); color: white; padding: 4px 12px; border-radius: 4px; font-weight: 700; font-size: 0.85rem;">
                                -{{ rtrim(rtrim(number_format((float) $pricing['discount_percent'], 2, '.', ''), '0'), '.') }}%
                            </span>
                        @endif
                    </div>
                </div>

                <p class="pdp-description">{{ $descriptionText }}</p>

                <section class="pdp-spec-box" aria-label="Key specifications">
                    <h2>Key Specifications</h2>
                    <div id="pdp-spec-rows">
                        @php
                            $specLines = preg_split('/\r\n|\r|\n/', trim((string) $specificationsText));
                        @endphp

                        @forelse ($specLines as $line)
                            @if (trim($line) !== '')
                                <div class="pdp-spec-row">{{ $line }}</div>
                            @endif
                        @empty
                            <div class="pdp-spec-row">No specifications available.</div>
                        @endforelse
                    </div>
                </section>

                <div class="pdp-actions">
                    <label class="pdp-qty" for="qty-value">
                        <span>Quantity:</span>
                        <span class="qty-box">
                            <button type="button" id="qty-decrease" aria-label="Decrease quantity">-</button>
                            <span id="qty-value">1</span>
                            <button type="button" id="qty-increase" aria-label="Increase quantity">+</button>
                        </span>
                    </label>

                    <form method="POST" action="{{ route('cart.add', $product->id) }}" id="add-to-cart-form">
                        @csrf
                        <input type="hidden" name="quantity" id="cart-quantity-input" value="1">

                        <button
                            type="submit"
                            id="add-to-cart"
                            class="pdp-btn pdp-btn-primary"
                            {{ $stock['in_stock'] ? '' : 'disabled' }}
                        >
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>

                    @if (session('success'))
                        <p id="pdp-feedback" class="pdp-feedback" aria-live="polite">{{ session('success') }}</p>
                    @else
                        <p id="pdp-feedback" class="pdp-feedback" aria-live="polite"></p>
                    @endif
                </div>

                <div style="margin-top: var(--spacing-lg); padding-top: var(--spacing-lg); border-top: 1px solid var(--border); display: flex; gap: var(--spacing-lg); flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 150px;">
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0 0 var(--spacing-xs) 0;"><i class="fas fa-truck"></i> FREE DELIVERY</p>
                        <p style="font-weight: 600; margin: 0;">On orders over $50</p>
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0 0 var(--spacing-xs) 0;"><i class="fas fa-undo"></i> RETURNS</p>
                        <p style="font-weight: 600; margin: 0;">30-day returns policy</p>
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0 0 var(--spacing-xs) 0;"><i class="fas fa-shield-alt"></i> SECURE</p>
                        <p style="font-weight: 600; margin: 0;">100% secure payment</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pdp-info mt-5">
        <div class="pdp-tabs" role="tablist" aria-label="Product info tabs">
            <button class="pdp-tab active" data-tab="description" type="button">Description</button>
            <button class="pdp-tab" data-tab="specifications" type="button">Specifications</button>
            <button class="pdp-tab" data-tab="reviews" type="button">Reviews ({{ $reviews->count() }})</button>
        </div>

        <div class="pdp-info-panel" id="pdp-info-panel">
            <div class="tab-panel" data-tab-panel="description">
                <p>{{ $descriptionText }}</p>
            </div>

            <div class="tab-panel d-none" data-tab-panel="specifications">
                @forelse ($specLines as $line)
                    @if (trim($line) !== '')
                        <p>{{ $line }}</p>
                    @endif
                @empty
                    <p>No specifications available.</p>
                @endforelse
            </div>

            <div class="tab-panel d-none" data-tab-panel="reviews">
                @forelse ($reviews as $review)
                    <div class="review-item" style="padding: 1rem 0; border-bottom: 1px solid var(--border);">
                        <p style="margin-bottom: .25rem;">
                            @for ($i = 0; $i < 5; $i++)
                                @if ($i < (int) $review->rating)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </p>
                        <p style="margin-bottom: .25rem;">{{ $review->text }}</p>
                        <small style="color: var(--text-secondary);">
                            {{ \Carbon\Carbon::parse($review->created_at)->format('d.m.Y') }}
                        </small>
                    </div>
                @empty
                    <p>No reviews yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mt-5">
        <h2 class="section-title">Related Products</h2>
        <div class="row g-3">
            @forelse ($relatedProducts as $relatedProduct)
                @php
                    $relatedRating = (float) ($relatedProduct->rating_avg ?? 0);
                    $relatedFullStars = floor($relatedRating);
                    $relatedHasHalfStar = ($relatedRating - $relatedFullStars) >= 0.5;
                    $relatedDiscount = (float) ($relatedProduct->discount ?? 0);

                    $relatedImageSrc = !empty($relatedProduct->image_path)
                        ? (str_starts_with($relatedProduct->image_path, 'http')
                            ? $relatedProduct->image_path
                            : asset('storage/' . $relatedProduct->image_path))
                        : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80';
                @endphp

                <div class="col-12 col-md-6 col-xl-3">
                    <article
                        class="product-card product-card-clickable"
                        tabindex="0"
                        role="button"
                        onclick="window.location='{{ route('products.show', $relatedProduct->id) }}'"
                        onkeydown="if(event.key==='Enter' || event.key===' '){ event.preventDefault(); window.location='{{ route('products.show', $relatedProduct->id) }}'; }"
                        aria-label="Open {{ $relatedProduct->title }} details"
                    >
                        <div style="position: relative;">
                            <img
                                src="{{ $relatedImageSrc }}"
                                alt="{{ $relatedProduct->image_alt ?? $relatedProduct->title }}"
                                loading="lazy"
                            >

                            @if ($relatedDiscount > 0)
                                <span class="product-discount-badge">
                                    -{{ rtrim(rtrim(number_format($relatedDiscount, 2, '.', ''), '0'), '.') }}%
                                </span>
                            @endif
                        </div>

                        <div class="product-info">
                            <div class="product-rating">
                                <span class="stars">
                                    @for ($i = 0; $i < 5; $i++)
                                        @if ($i < $relatedFullStars)
                                            <i class="fas fa-star"></i>
                                        @elseif ($i === $relatedFullStars && $relatedHasHalfStar)
                                            <i class="fas fa-star-half-alt"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </span>

                                <span>({{ $relatedProduct->review_count ?? 0 }})</span>
                            </div>

                            <h3>{{ $relatedProduct->title }}</h3>
                            <p>{{ $relatedProduct->brand_name ?? '' }}</p>
                            <strong>€{{ number_format((float) ($relatedProduct->price ?? 0), 2) }}</strong>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-4">No related products found.</div>
                </div>
            @endforelse
        </div>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let quantity = 1;

    const qtyValue = document.getElementById('qty-value');
    const quantityInput = document.getElementById('cart-quantity-input');
    const decreaseBtn = document.getElementById('qty-decrease');
    const increaseBtn = document.getElementById('qty-increase');
    const addToCartBtn = document.getElementById('add-to-cart');
    const feedback = document.getElementById('pdp-feedback');

    decreaseBtn?.addEventListener('click', function () {
        if (quantity > 1) {
            quantity--;
            qtyValue.textContent = quantity;
            if (quantityInput) quantityInput.value = quantity;
        }
    });

    increaseBtn?.addEventListener('click', function () {
        quantity++;
        qtyValue.textContent = quantity;
        if (quantityInput) quantityInput.value = quantity;
    });

    addToCartBtn?.addEventListener('click', function () {
        if (feedback) {
            feedback.textContent = `Adding ${quantity} item(s) to cart...`;
        }
    });

    const tabs = document.querySelectorAll('.pdp-tab');
    const panels = document.querySelectorAll('.tab-panel');

    tabs.forEach((tab) => {
        tab.addEventListener('click', function () {
            const tabName = this.dataset.tab;

            tabs.forEach((item) => item.classList.remove('active'));
            panels.forEach((panel) => panel.classList.add('d-none'));

            this.classList.add('active');
            document.querySelector(`[data-tab-panel="${tabName}"]`)?.classList.remove('d-none');
        });
    });
});
</script>
@endsection