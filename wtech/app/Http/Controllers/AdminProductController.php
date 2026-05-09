<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = DB::table('products')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->select([
                'products.id',
                'products.title',
                'products.price',
                'products.discount',
                'products.qty',
                'products.is_active',
                'products.updated_at',
                'brands.name as brand_name',
                'categories.name as category_name',
            ])
            ->orderBy('products.updated_at', 'desc');

        if ($request->filled('q')) {
            $query->where('products.title', 'like', '%' . $request->input('q') . '%');
        }

        $products = $query->paginate(15)->withQueryString();

        return view('admin.products.index', ['products' => $products]);
    }

    public function create(): View
    {
        $brands = DB::table('brands')->orderBy('name')->get();
        $categories = DB::table('categories')->where('parent_id', '!=', null)->orderBy('name')->get();

        return view('admin.products.create', [
            'brands' => $brands,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'specifications' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'qty' => ['required', 'integer', 'min:0'],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'images' => ['required', 'array', 'min:2', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $productId = DB::table('products')->insertGetId([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'specifications' => $validated['specifications'] ?? null,
                'price' => (float) $validated['price'],
                'discount' => (float) ($validated['discount'] ?? 0),
                'qty' => (int) $validated['qty'],
                'brand_id' => (int) $validated['brand_id'],
                'category_id' => (int) $validated['category_id'],
                'is_active' => true,
                'date_added' => now(),
                'updated_at' => now(),
            ]);

            $images = $request->file('images', []);
            foreach ($images as $index => $image) {
                $filename = Str::slug($validated['title']) . '_' . $productId . '_' . ($index + 1) . '.' . $image->extension();
                $path = $image->storeAs('products', $filename, 'public');

                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'image_path' => 'storage/' . $path,
                    'alt_text' => $validated['title'],
                    'is_title' => $index === 0,
                ]);
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully');
    }

    public function edit(int $id): View
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            abort(404);
        }

        $images = DB::table('product_images')->where('product_id', $id)->get();
        $brands = DB::table('brands')->orderBy('name')->get();
        $categories = DB::table('categories')->where('parent_id', '!=', null)->orderBy('name')->get();

        return view('admin.products.edit', [
            'product' => $product,
            'images' => $images,
            'brands' => $brands,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'specifications' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'qty' => ['required', 'integer', 'min:0'],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'title_image_id' => ['nullable', 'integer', 'exists:product_images,id'],
            'new_images' => ['nullable', 'array', 'max:5'],
            'new_images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'images_to_delete' => ['nullable', 'array'],
            'images_to_delete.*' => ['integer', 'exists:product_images,id'],
        ]);

        $currentImageIds = DB::table('product_images')
            ->where('product_id', $id)
            ->pluck('id')
            ->map(fn ($imageId) => (int) $imageId)
            ->all();

        $imagesToDelete = array_values(array_intersect(
            $currentImageIds,
            array_map('intval', $validated['images_to_delete'] ?? [])
        ));

        $remainingImageCount = count($currentImageIds) - count($imagesToDelete);
        $newImages = $request->file('new_images', []);
        $finalImageCount = $remainingImageCount + count($newImages);

        if ($finalImageCount < 2) {
            return back()
                ->withErrors(['new_images' => 'A product must contain at least 2 images.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $request, $id) {
            DB::table('products')->where('id', $id)->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'specifications' => $validated['specifications'] ?? null,
                'price' => (float) $validated['price'],
                'discount' => (float) ($validated['discount'] ?? 0),
                'qty' => (int) $validated['qty'],
                'brand_id' => (int) $validated['brand_id'],
                'category_id' => (int) $validated['category_id'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'updated_at' => now(),
            ]);

                $imagesToDelete = array_map('intval', $validated['images_to_delete'] ?? []);

                // Delete old images
                if (!empty($imagesToDelete)) {
                DB::table('product_images')
                    ->where('product_id', $id)
                    ->whereIn('id', $imagesToDelete)
                    ->delete();
            }

            // Add new images
            $newImages = $request->file('new_images', []);
            if (!empty($newImages)) {
                $existingImagesCount = DB::table('product_images')->where('product_id', $id)->count();

                foreach ($newImages as $index => $image) {
                    $filename = Str::slug($validated['title']) . '_' . $id . '_' . ($existingImagesCount + $index + 1) . '.' . $image->extension();
                    $path = $image->storeAs('products', $filename, 'public');

                    DB::table('product_images')->insert([
                        'product_id' => $id,
                        'image_path' => 'storage/' . $path,
                        'alt_text' => $validated['title'],
                        'is_title' => false,
                    ]);
                }
            }

            $selectedTitleImageId = isset($validated['title_image_id'])
                ? (int) $validated['title_image_id']
                : null;

            if ($selectedTitleImageId !== null && in_array($selectedTitleImageId, $imagesToDelete, true)) {
                $selectedTitleImageId = null;
            }

            if ($selectedTitleImageId !== null) {
                $selectedTitleImageId = DB::table('product_images')
                    ->where('product_id', $id)
                    ->where('id', $selectedTitleImageId)
                    ->value('id');
            }

            if ($selectedTitleImageId === null) {
                $selectedTitleImageId = DB::table('product_images')
                    ->where('product_id', $id)
                    ->orderByDesc('is_title')
                    ->orderBy('id')
                    ->value('id');
            }

            if ($selectedTitleImageId !== null) {
                DB::table('product_images')
                    ->where('product_id', $id)
                    ->update(['is_title' => false]);

                DB::table('product_images')
                    ->where('id', $selectedTitleImageId)
                    ->update(['is_title' => true]);
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            abort(404);
        }

        DB::transaction(function () use ($id) {
            DB::table('product_images')->where('product_id', $id)->delete();
            DB::table('cart_items')->where('product_id', $id)->delete();
            DB::table('order_items')->where('product_id', $id)->delete();
            DB::table('reviews')->where('product_id', $id)->delete();
            DB::table('products')->where('id', $id)->delete();
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully');
    }
}
