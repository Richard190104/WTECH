@extends('layouts.admin')

@section('title', 'Create Product')

@section('admin-topbar-actions')
    <a class="admin-btn admin-btn-secondary" href="{{ route('admin.products.index') }}">
        <i class="fas fa-list"></i> Product list
    </a>
@endsection

@section('content')
<section class="admin-panel" aria-labelledby="admin-form-title">
    <h1 id="admin-form-title" class="admin-title">Add product</h1>
    <p class="admin-subtitle">Fill in title, price, discount, quantity, description, specifications, brand, category, and upload images.</p>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="admin-form-grid" novalidate data-admin-image-uploader>
        @csrf

        <div class="admin-form-field admin-col-2">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="admin-input @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
            @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="price">Price ($)</label>
            <input type="number" step="0.01" min="0" id="price" name="price" class="admin-input @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="discount">Discount (%)</label>
            <input type="number" step="1" min="0" max="100" id="discount" name="discount" class="admin-input @error('discount') is-invalid @enderror" value="{{ old('discount', 0) }}">
            @error('discount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="qty">Quantity</label>
            <input type="number" step="1" min="0" id="qty" name="qty" class="admin-input @error('qty') is-invalid @enderror" value="{{ old('qty', 0) }}" required>
            @error('qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field admin-col-2">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="admin-input admin-textarea @error('description') is-invalid @enderror" rows="4" required>{{ old('description') }}</textarea>
            @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field admin-col-2">
            <label for="specifications">Specifications</label>
            <textarea id="specifications" name="specifications" class="admin-input admin-textarea @error('specifications') is-invalid @enderror" rows="4">{{ old('specifications') }}</textarea>
            @error('specifications') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="admin-input @error('category_id') is-invalid @enderror" required>
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field">
            <label for="brand_id">Brand</label>
            <select id="brand_id" name="brand_id" class="admin-input @error('brand_id') is-invalid @enderror" required>
                <option value="">Select brand</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </select>
            @error('brand_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="admin-form-field admin-col-2">
            <label for="images">Images</label>
            <input type="file" id="images" name="images[]" class="admin-input @error('images') is-invalid @enderror" accept="image/*" multiple required data-admin-image-input data-admin-max-images="5">
            <p class="admin-field-note">Select one or more files. Each new selection is added to the queue below.</p>
            @error('images') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            <div class="row g-3 mt-1" data-admin-image-preview></div>
        </div>

        <div class="admin-form-actions admin-col-2">
            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn admin-btn-primary">
                <i class="fas fa-save"></i> Save product
            </button>
        </div>
    </form>
</section>
@endsection
