@extends('layouts.app')

@section('title', 'Products')

@section('content')
<main class="container-xl mt-3 pb-5">
    <nav class="pdp-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
        <span>&gt;</span>
        <span>Products</span>
    </nav>

    <h1 class="plp-category-title">Products</h1>

    <div class="plp-layout">
        <aside class="plp-sidebar" aria-label="Product filters">
            <form method="GET" action="{{ route('products.index') }}">

                <div class="plp-filter-card">
                    <h2 class="plp-filter-heading">Category</h2>
                    <div class="plp-check-list">
                        @foreach ($categories as $category)
                            <label class="plp-check-item">
                                <input
                                    type="radio"
                                    name="category_id"
                                    value="{{ $category->id }}"
                                    {{ (string) ($filters['category_id'] ?? '') === (string) $category->id ? 'checked' : '' }}
                                >
                                {{ $category->name }}
                            </label>
                        @endforeach

                        <label class="plp-check-item">
                            <input
                                type="radio"
                                name="category_id"
                                value=""
                                {{ empty($filters['category_id']) ? 'checked' : '' }}
                            >
                            All categories
                        </label>
                    </div>
                </div>

                <div class="plp-filter-card">
                    <h2 class="plp-filter-heading">Price Range</h2>

                    <div class="plp-price-inputs">
                        <div class="plp-price-field">
                            <label for="price-min" class="visually-hidden">Min price</label>
                            <span class="plp-price-prefix">€</span>
                            <input
                                type="number"
                                id="price-min"
                                name="price_min"
                                class="plp-price-input"
                                value="{{ $filters['price_min'] ?? '' }}"
                                min="0"
                                step="0.01"
                                placeholder="0"
                            >
                        </div>

                        <span class="plp-price-sep">—</span>

                        <div class="plp-price-field">
                            <label for="price-max" class="visually-hidden">Max price</label>
                            <span class="plp-price-prefix">€</span>
                            <input
                                type="number"
                                id="price-max"
                                name="price_max"
                                class="plp-price-input"
                                value="{{ $filters['price_max'] ?? '' }}"
                                min="0"
                                step="0.01"
                                placeholder="2000"
                            >
                        </div>
                    </div>
                </div>

                <div class="plp-filter-card">
                    <h2 class="plp-filter-heading">Brand</h2>
                    <div class="plp-check-list">
                        @foreach ($brands as $brand)
                            <label class="plp-check-item">
                                <input
                                    type="radio"
                                    name="brand_id"
                                    value="{{ $brand->id }}"
                                    {{ (string) ($filters['brand_id'] ?? '') === (string) $brand->id ? 'checked' : '' }}
                                >
                                {{ $brand->name }}
                            </label>
                        @endforeach

                        <label class="plp-check-item">
                            <input
                                type="radio"
                                name="brand_id"
                                value=""
                                {{ empty($filters['brand_id']) ? 'checked' : '' }}
                            >
                            All brands
                        </label>
                    </div>
                </div>

                <div class="plp-filter-card">
                    <h2 class="plp-filter-heading">Availability</h2>
                    <div class="plp-check-list">
                        <label class="plp-check-item">
                            <input
                                type="checkbox"
                                name="in_stock"
                                value="1"
                                {{ !empty($filters['in_stock']) ? 'checked' : '' }}
                            >
                            In stock only
                        </label>
                    </div>
                </div>

                <div class="plp-filter-card">
                    <h2 class="plp-filter-heading">Rating</h2>
                    <div class="plp-check-list">
                        <label class="plp-check-item" for="rating-filter-select" style="width: 100%;">
                            Minimum rating
                            <select
                                id="rating-filter-select"
                                name="rating_min"
                                class="plp-sort-select"
                                style="width: 100%; margin-top: 8px;"
                            >
                                <option value="">Any rating</option>
                                <option value="4" {{ ($filters['rating_min'] ?? '') == '4' ? 'selected' : '' }}>4.0 &amp; up</option>
                                <option value="4.5" {{ ($filters['rating_min'] ?? '') == '4.5' ? 'selected' : '' }}>4.5 &amp; up</option>
                                <option value="5" {{ ($filters['rating_min'] ?? '') == '5' ? 'selected' : '' }}>5.0 only</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="plp-reset-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>

                    <a href="{{ route('products.index') }}" class="plp-reset-btn text-decoration-none text-center">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </aside>

        <div class="plp-content">
            <div class="plp-toolbar">
                <p class="plp-result-count">
                    Showing {{ $products->count() }} of {{ $products->total() }} products
                </p>

                <div class="plp-toolbar-right">
                    <form method="GET" action="{{ route('products.index') }}" id="sort-form">
                        <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">
                        <input type="hidden" name="category_id" value="{{ $filters['category_id'] ?? '' }}">
                        <input type="hidden" name="brand_id" value="{{ $filters['brand_id'] ?? '' }}">
                        <input type="hidden" name="price_min" value="{{ $filters['price_min'] ?? '' }}">
                        <input type="hidden" name="price_max" value="{{ $filters['price_max'] ?? '' }}">
                        <input type="hidden" name="rating_min" value="{{ $filters['rating_min'] ?? '' }}">

                        @if (!empty($filters['in_stock']))
                            <input type="hidden" name="in_stock" value="1">
                        @endif

                        <label for="sort-select" class="plp-sort-label">Sort by:</label>
                        <select
                            id="sort-select"
                            name="sort"
                            class="plp-sort-select"
                            onchange="document.getElementById('sort-form').submit()"
                        >
                            <option value="price_asc" {{ ($filters['sort'] ?? 'newest') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ ($filters['sort'] ?? 'newest') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="rating_desc" {{ ($filters['sort'] ?? 'newest') === 'rating_desc' ? 'selected' : '' }}>Top Rated</option>
                            <option value="newest" {{ ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="row g-3">
                @forelse ($products as $product)
                    @php
                        $rating = (float) ($product->rating_avg ?? 0);
                        $fullStars = floor($rating);
                        $hasHalfStar = ($rating - $fullStars) >= 0.5;
                        $discount = (float) ($product->discount ?? 0);
                        $description = $product->description ?? $product->specifications ?? 'Available now in our catalog.';
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
                                    src="{{ $product->image_path ? asset(ltrim($product->image_path, '/')) : 'https://www.vecteezy.com/free-photos/cute-baby-cat' }}"
                                    alt="{{ $product->title }}"
                                >

                                @if ($discount > 0)
                                    <span class="product-discount-badge">
                                        -{{ rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') }}%
                                    </span>
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

            <nav class="plp-pagination mt-4" aria-label="Page navigation">
                {{ $products->links() }}
            </nav>
        </div>
    </div>
</main>
@endsection