@extends('layouts.admin')

@section('title', 'Admin - Products')

@section('admin-topbar-actions')
    <a class="admin-btn admin-btn-secondary" href="{{ route('admin.products.create') }}">
        <i class="fas fa-plus"></i> New product
    </a>
@endsection

@section('content')
<section class="admin-panel" aria-labelledby="admin-products-title">
    <div class="admin-panel-header">
        <div>
            <h1 id="admin-products-title" class="admin-title">Product list</h1>
            <p class="admin-subtitle">Number of products: <span id="admin-products-count">{{ $products->total() }}</span></p>
        </div>
        <a class="admin-btn admin-btn-primary" href="{{ route('admin.products.create') }}">
            <i class="fas fa-plus"></i> Add product
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="admin-table-wrap">
        <table class="admin-table" aria-label="Admin product table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->title }}</td>
                        <td>{{ $product->category_name }}</td>
                        <td>{{ $product->brand_name }}</td>
                        <td>${{ number_format((float) $product->price, 2) }}</td>
                        <td>{{ number_format((float) $product->discount, 0) }}%</td>
                        <td>{{ $product->qty }}</td>
                        <td>
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="admin-table-action">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" style="display: inline;" onsubmit="return confirm('Delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-table-action danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No products found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</section>
@endsection
