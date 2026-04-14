<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = DB::table('products')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('product_images as title_images', function ($join) {
                $join->on('title_images.product_id', '=', 'products.id')
                    ->where('title_images.is_title', true);
            })
            ->where('products.is_active', true)
            ->select([
                'products.id',
                'products.title',
                'products.price',
                'products.discount',
                'products.rating_avg',
                'products.review_count',
                'products.description',
                'products.specifications',
                'products.qty',
                'products.date_added',
                'brands.id as brand_id',
                'brands.name as brand_name',
                'categories.id as category_id',
                'categories.name as category_name',
                'title_images.image_path as image_path',
                'title_images.alt_text as image_alt',
            ]);

        if ($request->filled('category_id')) {
            $query->where('products.category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('products.brand_id', (int) $request->input('brand_id'));
        }

        $hasPriceMin = $request->filled('price_min');
        $hasPriceMax = $request->filled('price_max');
        $priceMin = $hasPriceMin ? (float) $request->input('price_min') : null;
        $priceMax = $hasPriceMax ? (float) $request->input('price_max') : null;

        if ($hasPriceMin && $hasPriceMax && $priceMin > $priceMax) {
            [$priceMin, $priceMax] = [$priceMax, $priceMin];
        }

        if ($priceMin !== null) {
            $query->where('products.price', '>=', $priceMin);
        }

        if ($priceMax !== null) {
            $query->where('products.price', '<=', $priceMax);
        }

        if ($request->boolean('in_stock')) {
            $query->where('products.qty', '>', 0);
        }

        if ($request->filled('rating_min')) {
            $query->where('products.rating_avg', '>=', (float) $request->input('rating_min'));
        }

        if ($request->filled('q')) {
            $needle = trim((string) $request->input('q'));

            $query->where(function ($subQuery) use ($needle) {
                $subQuery->whereRaw(
                    "to_tsvector('simple', " .
                    "coalesce(products.title, '') || ' ' || " .
                    "coalesce(products.description, '') || ' ' || " .
                    "coalesce(products.specifications, '') || ' ' || " .
                    "coalesce(brands.name, '') || ' ' || " .
                    "coalesce(categories.name, '')" .
                    ") @@ plainto_tsquery('simple', ?)",
                    [$needle]
                )
                ->orWhere('products.title', 'like', '%' . $needle . '%')
                ->orWhere('products.description', 'like', '%' . $needle . '%')
                ->orWhere('products.specifications', 'like', '%' . $needle . '%')
                ->orWhere('brands.name', 'like', '%' . $needle . '%')
                ->orWhere('categories.name', 'like', '%' . $needle . '%');
            });
        }

        $sort = (string) $request->input('sort', 'newest');

        switch ($sort) {
            case 'price_asc':
                $query->orderBy('products.price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('products.price', 'desc');
                break;
            case 'rating_desc':
                $query->orderBy('products.rating_avg', 'desc');
                break;
            default:
                $query->orderBy('products.date_added', 'desc');
                break;
        }

        $perPage = max(1, min((int) $request->input('per_page', 12), 50));
        $products = $query->paginate($perPage)->withQueryString();

        $categories = DB::table('categories')->orderBy('name')->get(['id', 'name']);
        $brands = DB::table('brands')->orderBy('name')->get(['id', 'name']);

        return view('storefront.products', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => [
                'category_id' => $request->input('category_id'),
                'brand_id' => $request->input('brand_id'),
                'price_min' => $request->input('price_min'),
                'price_max' => $request->input('price_max'),
                'in_stock' => $request->boolean('in_stock'),
                'rating_min' => $request->input('rating_min'),
                'q' => $request->input('q'),
                'sort' => $sort,
            ],
        ]);
    }

    public function show(int $id): View
    {
        $product = DB::table('products')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('product_images as title_images', function ($join) {
                $join->on('title_images.product_id', '=', 'products.id')
                    ->where('title_images.is_title', true);
            })
            ->where('products.id', $id)
            ->where('products.is_active', true)
            ->select([
                'products.id',
                'products.title',
                'products.price',
                'products.discount',
                'products.rating_avg',
                'products.review_count',
                'products.description',
                'products.specifications',
                'products.qty',
                'products.date_added',
                'brands.id as brand_id',
                'brands.name as brand_name',
                'categories.id as category_id',
                'categories.name as category_name',
                'title_images.image_path as image_path',
                'title_images.alt_text as image_alt',
            ])
            ->first();

        abort_if(!$product, 404);

        $basePrice = (float) $product->price;
        $discountPercent = (float) $product->discount;
        $discountAmount = round($basePrice * ($discountPercent / 100), 2);
        $finalPrice = max(0, round($basePrice - $discountAmount, 2));

        $images = DB::table('product_images')
            ->where('product_id', $id)
            ->orderByDesc('is_title')
            ->orderBy('id')
            ->get(['id', 'image_path', 'alt_text', 'is_title']);

        $reviews = DB::table('reviews')
            ->where('product_id', $id)
            ->orderByDesc('created_at')
            ->get(['id', 'rating', 'text', 'created_at']);

        $relatedProducts = DB::table('products')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('product_images as title_images', function ($join) {
                $join->on('title_images.product_id', '=', 'products.id')
                    ->where('title_images.is_title', true);
            })
            ->where('products.is_active', true)
            ->where('products.id', '<>', $id)
            ->where(function ($query) use ($product) {
                $query->where('products.category_id', $product->category_id)
                    ->orWhere('products.brand_id', $product->brand_id);
            })
            ->orderByRaw('CASE WHEN products.category_id = ? THEN 0 ELSE 1 END', [$product->category_id])
            ->orderByDesc('products.rating_avg')
            ->limit(4)
            ->get([
                'products.id',
                'products.title',
                'products.price',
                'products.discount',
                'products.rating_avg',
                'products.review_count',
                'brands.name as brand_name',
                'categories.name as category_name',
                'title_images.image_path as image_path',
                'title_images.alt_text as image_alt',
            ]);

            return view('storefront.product-detail', [
                'product' => $product,
                'pricing' => [
                    'base_price' => $basePrice,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'final_price' => $finalPrice,
                ],
                'stock' => [
                    'quantity' => (int) $product->qty,
                    'in_stock' => (int) $product->qty > 0,
                ],
                'images' => $images,
                'reviews' => $reviews,
                'relatedProducts' => $relatedProducts,
            ]);
    }
    public function home()
{
   $featuredProducts = DB::table('products')
    ->join('brands', 'brands.id', '=', 'products.brand_id')
    ->join('categories', 'categories.id', '=', 'products.category_id')
    ->leftJoin('product_images as title_images', function ($join) {
        $join->on('title_images.product_id', '=', 'products.id')
            ->where('title_images.is_title', true);
    })
    ->where('products.is_active', true)
    ->orderBy('products.date_added', 'desc')
    ->limit(6)
    ->get([
        'products.id',
        'products.title',
        'products.price',
        'products.discount',
        'products.rating_avg',
        'products.review_count',
        'products.description',
        'products.specifications',
        'brands.name as brand_name',
        'categories.name as category_name',
        'title_images.image_path as image_path',
        'title_images.alt_text as image_alt',
    ]);

    $trendingProducts = DB::table('products')
        ->join('brands', 'brands.id', '=', 'products.brand_id')
        ->join('categories', 'categories.id', '=', 'products.category_id')
        ->leftJoin('product_images as title_images', function ($join) {
            $join->on('title_images.product_id', '=', 'products.id')
                ->where('title_images.is_title', true);
        })
        ->where('products.is_active', true)
        ->orderByDesc('products.rating_avg')
        ->orderByDesc('products.review_count')
        ->limit(4)
        ->get([
            'products.id',
            'products.title',
            'products.description',
            'products.specifications',
            'products.price',
            'products.discount',
            'products.rating_avg',
            'products.review_count',
            'brands.name as brand_name',
            'categories.name as category_name',
            'title_images.image_path as image_path',
            'title_images.alt_text as image_alt',
        ]);

    return view('welcome', [
        'featuredProducts' => $featuredProducts,
        'trendingProducts' => $trendingProducts,
    ]);
}
}