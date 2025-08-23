<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Department;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\CountryCode;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function home(Request $request)
    {
        $keyword = $request->query('keyword');
        $products = Product::query()
            ->forWebsite()
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%");
                });
            })
            ->paginate(24);

        return Inertia::render('Home', [
            'products' => ProductListResource::collection($products),
            'countryCode' => CountryCode::toIso2(session('country_code')) ?? config('vat.fallback_country', 'RO'),
        ]);
    }

    public function show(Product $product)
    {
        $user = auth()->user();

        // Load media instead of legacy images relation
        $product->load([
            'media',
            'variationTypes.options.media',
            'variations',
            'reviews.user',
        ]);

        $hasPurchased = $user
            ? $user->orders()
                ->where('status', 'paid')
                ->whereHas('items', fn($q) => $q->where('product_id', $product->id))
                ->exists()
            : false;

        $already = $user
            ? $product->reviews()->where('user_id', $user->id)->exists()
            : false;

        $base = Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        $boughtTogetherIds = OrderItem::query()
            ->selectRaw('product_id, COUNT(*) as c')
            ->whereIn('order_id', function ($q) use ($product) {
                $q->select('order_id')->from('order_items')->where('product_id', $product->id);
            })
            ->where('product_id', '!=', $product->id)
            ->groupBy('product_id')
            ->orderByDesc('c')
            ->limit(15)
            ->pluck('product_id');

        $boughtTogether = Product::query()
            ->whereIn('id', $boughtTogetherIds)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get();

        $similar = (clone $base)->orderByDesc('reviews_avg_rating')->limit(20)->get();
        $compare = (clone $base)->orderBy('price')->limit(20)->get();

        $alsoViewed = Schema::hasColumn('products', 'views')
            ? (clone $base)->orderByDesc('views')->limit(20)->get()
            : (clone $base)->orderByDesc('reviews_count')->orderByDesc('reviews_avg_rating')->limit(20)->get();

        return Inertia::render('Product/Show', [
            'product' => new \App\Http\Resources\ProductResource($product),
            'can_review' => (bool) ($user && $user->hasVerifiedEmail() && $hasPurchased),
            'already_reviewed' => (bool) $already,
            'bought_together' => $boughtTogether->map->only(['id','name','slug','image_url','price_gross','reviews_count','reviews_avg_rating']),
            'similar_products' => $similar->map->only(['id','name','slug','image_url','price_gross','reviews_count','reviews_avg_rating']),
            'compare_products' => $compare->map->only(['id','name','slug','image_url','price_gross','reviews_count','reviews_avg_rating']),
            'also_viewed' => $alsoViewed->map->only(['id','name','slug','image_url','price_gross','reviews_count','reviews_avg_rating']),
        ]);
    }

    public function byDepartment(Request $request, Department $department)
    {
        abort_unless($department->active, 404);

        $keyword = $request->query('keyword');
        $products = Product::query()
            ->forWebsite()
            ->where('department_id', $department->id)
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%");
                });
            })
            ->paginate(24);

        return Inertia::render('Department/Index', [
            'department' => new DepartmentResource($department),
            'products' => ProductListResource::collection($products),
        ]);
    }
}
