<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\CountryCode;
use App\Services\VatCountryResolver;
use App\Services\VatRateService;
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

    public function show(Product $product, VatCountryResolver $countryResolver, VatRateService $vat)
    {
        $product->load(['reviews.user']);

        $user = auth()->user();
        $hasPurchased = false;
        $already = false;

        if ($user) {
            $hasPurchased = $user->orders()
                ->where('status', 'paid')
                ->whereHas('orderItems', fn ($q) => $q->where('product_id', $product->id))
                ->exists();
            $already = $product->reviews->firstWhere('user_id', $user->id) !== null;
        }

        $country = $countryResolver->resolve();
        $rate = $vat->rateForProduct($product, $country);
        $net = round((float) $product->price, 2);
        $vatA = round($net * $rate / 100, 2);
        $gross = round($net + $vatA, 2);

        $boughtTogetherIds = OrderItem::query()
            ->selectRaw('product_id, COUNT(*) as c')
            ->whereIn('order_id', function ($q) use ($product) {
                $q->select('order_id')
                    ->from('order_items')
                    ->where('product_id', $product->id);
            })
            ->where('product_id', '!=', $product->id)
            ->groupBy('product_id')
            ->orderByDesc('c')
            ->limit(15)
            ->pluck('product_id');

        $boughtTogether = Product::whereIn('id', $boughtTogetherIds)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get()
            ->map(function ($p) use ($vat, $country) {
                $calc = $vat->priceForProduct($p, $country);
                return [
                    'id' => $p->id,
                    'name' => $p->title,
                    'slug' => $p->slug,
                    'image_url' => $p->getFirstImageUrl(),
                    'price_gross' => $calc['gross'],
                    'currency' => $calc['currency'],
                    'rating_average' => round((float) $p->reviews_avg_rating, 2),
                    'reviews_count' => $p->reviews_count,
                ];
            });

        $similarProducts = Product::query()
            ->where('category_id', $product->category_id)
            ->when($product->brand_id ?? null, fn ($q) => $q->orWhere('brand_id', $product->brand_id))
            ->where('id', '!=', $product->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->limit(20)
            ->get()
            ->map(function ($p) use ($vat, $country) {
                $calc = $vat->priceForProduct($p, $country);
                return [
                    'id' => $p->id,
                    'name' => $p->title,
                    'slug' => $p->slug,
                    'image_url' => $p->getFirstImageUrl(),
                    'price_gross' => $calc['gross'],
                    'currency' => $calc['currency'],
                    'rating_average' => round((float) $p->reviews_avg_rating, 2),
                    'reviews_count' => $p->reviews_count,
                ];
            });

        $compareProducts = Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('price')
            ->limit(20)
            ->get()
            ->map(function ($p) use ($vat, $country) {
                $calc = $vat->priceForProduct($p, $country);
                return [
                    'id' => $p->id,
                    'name' => $p->title,
                    'slug' => $p->slug,
                    'image_url' => $p->getFirstImageUrl(),
                    'price_gross' => $calc['gross'],
                    'currency' => $calc['currency'],
                    'rating_average' => round((float) $p->reviews_avg_rating, 2),
                    'reviews_count' => $p->reviews_count,
                ];
            });

        $alsoViewed = Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('views')
            ->limit(20)
            ->get()
            ->map(function ($p) use ($vat, $country) {
                $calc = $vat->priceForProduct($p, $country);
                return [
                    'id' => $p->id,
                    'name' => $p->title,
                    'slug' => $p->slug,
                    'image_url' => $p->getFirstImageUrl(),
                    'price_gross' => $calc['gross'],
                    'currency' => $calc['currency'],
                    'rating_average' => round((float) $p->reviews_avg_rating, 2),
                    'reviews_count' => $p->reviews_count,
                ];
            });

        return Inertia::render('Product/Show', [
            'product' => array_merge(
                (new ProductResource($product))->toArray(request()),
                ['vat' => ['rate' => $rate, 'amount' => $vatA, 'gross' => $gross]]
            ),
            'variationOptions' => request('options', []),
            'can_review' => (bool) ($user && $user->hasVerifiedEmail() && $hasPurchased),
            'already_reviewed' => $already,
            'all_reviews' => $product->reviews()->with('user:id,name')->latest()->get(),
            'bought_together' => $boughtTogether,
            'similar_products' => $similarProducts,
            'compare_products' => $compareProducts,
            'also_viewed' => $alsoViewed,
            'vatCountry' => $country,
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
