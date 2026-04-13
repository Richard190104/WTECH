@extends('layouts.app')

@section('title', 'Home')

@section('content')
<main class="container-xl mt-4 pb-5">
    <section class="row g-3 hero-blocks">
        <div class="col-12 col-lg-8">
            <article class="promo-card h-100">
                <div class="promo-discount">-25%</div>
                <h2>ULTRA PERFORMANCE</h2>
                <h3>Gaming Laptop</h3>
                <p>Experience gaming like never before. RTX 4080 • 32GB RAM • 1TB SSD</p>
                <button class="btn btn-light mt-3" style="font-weight: 600;">
                    Shop Now <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </article>
        </div>

        <div class="col-12 col-lg-4">
            <aside class="deal-card h-100">
                <h2>Deal of the Month</h2>
                <div class="deal-badge">-50%</div>
                <p style="margin-top: 16px; font-size: 0.9rem;">Wireless Headphones Pro</p>
            </aside>
        </div>
    </section>

    <section class="mt-5">
        <h2 class="section-title">Featured Products</h2>

        <div class="row g-3">
            <div class="col-12 col-lg-9">
                <div class="row g-3">
                    @forelse ($featuredProducts as $product)
                        @php
                            $rating = (float) ($product->rating_avg ?? 0);
                            $fullStars = floor($rating);
                            $hasHalfStar = ($rating - $fullStars) >= 0.5;
                            $discount = (float) ($product->discount ?? 0);
                            $description = $product->description ?: ($product->specifications ?: 'Available now in our catalog.');
                        @endphp

                        <div class="col-12 col-md-6 col-xl-4">
                            <article
                                class="product-card product-card-clickable"
                                tabindex="0"
                                role="button"
                                onclick="window.location='{{ route('products.show', $product->id) }}'"
                                onkeydown="if(event.key==='Enter' || event.key===' '){ event.preventDefault(); window.location='{{ route('products.show', $product->id) }}'; }"
                                aria-label="Open {{ $product->title }} details"
                            >
                                <div style="position: relative;">
                                    <img
                                        src="{{ $product->image_path ? asset($product->image_path) : asset('images/placeholder.png') }}"
                                        alt="{{ $product->image_alt ?? $product->title }}"
                                        loading="lazy"
                                    >

                                    @if ($discount > 0)
                                        <span class="product-discount-badge">-{{ rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') }}%</span>
                                    @endif
                                </div>

                                <div class="product-info">
                                    <div class="product-rating">
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

                                        <span>({{ $product->review_count ?? 0 }})</span>
                                    </div>

                                    <h3>{{ $product->title }}</h3>
                                    <p>{{ $description }}</p>
                                    <strong>€{{ number_format((float) ($product->price ?? 0), 2) }}</strong>

                                    <div class="product-card-actions">
                                        <button
                                            type="button"
                                            class="product-view-btn"
                                            onclick="window.location='{{ route('products.show', $product->id) }}'"
                                        >
                                             View
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card p-4">No products found.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-12 col-lg-3 d-flex flex-column gap-3">
                <aside class="side-promo" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                    <h3><i class="fas fa-laptop-code"></i> PC Accessories</h3>
                    <p>Up to 40% Off</p>
                    <button class="side-promo-btn">Explore</button>
                </aside>

                <aside class="side-promo" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                    <h3><i class="fas fa-fire"></i> Flash Sale</h3>
                    <p>Limited Time Only</p>
                    <button class="side-promo-btn" style="color: #D97706;">Shop Now</button>
                </aside>
            </div>
        </div>
    </section>

    <section class="mt-5">
        <h2 class="section-title">Trending Now</h2>

        <div class="row g-3">
            @forelse ($trendingProducts as $product)
                @php
                    $rating = (float) ($product->rating_avg ?? 0);
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                    $discount = (float) ($product->discount ?? 0);
                    $description = $product->description ?: ($product->specifications ?: 'Available now in our catalog.');
                @endphp

                <div class="col-12 col-md-6 col-xl-3">
                    <article
                        class="product-card product-card-clickable"
                        tabindex="0"
                        role="button"
                        onclick="window.location='{{ route('products.show', $product->id) }}'"
                        onkeydown="if(event.key==='Enter' || event.key===' '){ event.preventDefault(); window.location='{{ route('products.show', $product->id) }}'; }"
                        aria-label="Open {{ $product->title }} details"
                    >
                        <div style="position: relative;">
                            <img
                                src="{{ $product->image_path ? asset($product->image_path) : asset('images/placeholder.png') }}"
                                alt="{{ $product->image_alt ?? $product->title }}"
                                loading="lazy"
                            >

                            @if ($discount > 0)
                                <span class="product-discount-badge">-{{ rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') }}%</span>
                            @endif
                        </div>

                        <div class="product-info">
                            <div class="product-rating">
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

                                <span>({{ $product->review_count ?? 0 }})</span>
                            </div>

                            <h3>{{ $product->title }}</h3>
                            <p>{{ $description }}</p>
                            <strong>€{{ number_format((float) ($product->price ?? 0), 2) }}</strong>

                            <div class="product-card-actions">
                                <button
                                    type="button"
                                    class="product-view-btn"
                                    onclick="window.location='{{ route('products.show', $product->id) }}'"
                                >
                                    View
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-4">No products found.</div>
                </div>
            @endforelse
        </div>
    </section>
</main>
@endsection