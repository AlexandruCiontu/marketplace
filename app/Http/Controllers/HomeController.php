<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductListResource;
use App\Models\Product;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::published()
            ->with('vendor')
            ->latest()
            ->paginate(12);

        return Inertia::render('Home', [
            'products' => ProductListResource::collection($products),
        ]);
    }
}
