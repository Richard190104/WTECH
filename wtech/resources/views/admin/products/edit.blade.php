@extends('layouts.admin')

@section('title', 'Edit Product')

@section('admin-topbar-actions')
    <a class="admin-btn admin-btn-secondary" href="{{ route('admin.products.index') }}">
        <i class="fas fa-list"></i> Product list
    </a>
@endsection

@section('content')
<section class="admin-panel" aria-labelledby="admin-form-title">
    @php
        $selectedTitleImageId = old('title_image_id');
        if (empty($selectedTitleImageId)) {
            $selectedTitleImageId = optional($images->firstWhere('is_title', true))->id;
        }
    @endphp

    <h1 id="admin-form-title" class="admin-title">Edit product</h1>
    <p class="admin-subtitle">Update the product details, pricing, images, and visibility settings.</p>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="admin-form-grid" novalidate data-admin-image-uploader>
        @csrf
        @method('PUT')

        <div class="admin-form-field admin-col-2">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="admin-input @error('title') is-invalid @enderror" value="{{ old('title', $product->title) }}" required>
            @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="price">Price ($)</label>
            <input type="number" step="0.01" min="0" id="price" name="price" class="admin-input @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" required>
            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="discount">Discount (%)</label>
            <input type="number" step="1" min="0" max="100" id="discount" name="discount" class="admin-input @error('discount') is-invalid @enderror" value="{{ old('discount', $product->discount) }}">
            @error('discount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="qty">Quantity</label>
            <input type="number" step="1" min="0" id="qty" name="qty" class="admin-input @error('qty') is-invalid @enderror" value="{{ old('qty', $product->qty) }}" required>
            @error('qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field admin-col-2">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="admin-input admin-textarea @error('description') is-invalid @enderror" rows="4" required>{{ old('description', $product->description) }}</textarea>
            @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field admin-col-2">
            <label for="specifications">Specifications</label>
            <textarea id="specifications" name="specifications" class="admin-input admin-textarea @error('specifications') is-invalid @enderror" rows="4">{{ old('specifications', $product->specifications) }}</textarea>
            @error('specifications') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="admin-input @error('category_id') is-invalid @enderror" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="brand_id">Brand</label>
            <select id="brand_id" name="brand_id" class="admin-input @error('brand_id') is-invalid @enderror" required>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </select>
            @error('brand_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field admin-col-2">
            <label class="form-label">Status</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Product is active</label>
            </div>
        </div>

        <div class="admin-form-field admin-col-2">
            <label class="form-label">Current Images</label>
            <div class="row g-3">
                @foreach ($images as $image)
                    <div class="col-md-4">
                        <div class="card h-100">
                            <img src="{{ asset($image->image_path) }}" class="card-img-top" alt="{{ $image->alt_text }}" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2">
                                @if ((string) $selectedTitleImageId === (string) $image->id)
                                    <span class="badge bg-info mb-2">Title Image</span>
                                @endif
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="title_image_id" value="{{ $image->id }}" id="title_image_{{ $image->id }}" {{ (string) $selectedTitleImageId === (string) $image->id ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="title_image_{{ $image->id }}">Set as title image</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="images_to_delete[]" value="{{ $image->id }}" id="delete_image_{{ $image->id }}">
                                    <label class="form-check-label small" for="delete_image_{{ $image->id }}">Delete</label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('title_image_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field admin-col-2">
            <label for="new_images">Add New Images</label>
            <input type="file" id="new_images" name="new_images[]" class="admin-input @error('new_images') is-invalid @enderror" accept="image/*" multiple data-admin-image-input data-admin-max-images="5">
            <p class="admin-field-note">Each new selection is added to the queue below. Existing images are shown above.</p>
            @error('new_images') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            <div class="row g-3 mt-1" data-admin-image-preview></div>
        </div>

        <div class="admin-form-actions admin-col-2">
            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn admin-btn-primary">
                <i class="fas fa-save"></i> Update product
            </button>
        </div>
    </form>
</section>
@endsection
