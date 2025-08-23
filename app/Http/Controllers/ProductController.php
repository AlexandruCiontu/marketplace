<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Department;
use App\Models\Order;
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

        $hasPurchased = false;
        if (auth()->check()) {
            $hasPurchased = Order::where('user_id', auth()->id())
                ->whereHas('orderItems', fn ($q) => $q->where('product_id', $product->id))
                ->exists();
        }

        $relatedProducts = Product::query()
            ->forWebsite()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(10)
            ->get();

        $country = $countryResolver->resolve();
        $rate = $vat->rateForProduct($product, $country);
        $net = round((float) $product->price, 2);
        $vatA = round($net * $rate / 100, 2);
        $gross = round($net + $vatA, 2);

        return Inertia::render('Product/Show', [
            'product' => array_merge(
                (new ProductResource($product))->toArray(request()),
                ['vat' => ['rate' => $rate, 'amount' => $vatA, 'gross' => $gross]]
            ),
            'variationOptions' => request('options', []),
            'hasPurchased' => $hasPurchased,
            'relatedProducts' => ProductListResource::collection($relatedProducts),
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
