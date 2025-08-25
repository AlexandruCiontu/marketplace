<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        $hasPurchased = $user->orders()
            ->where('status', 'paid')
            ->whereHas('orderItems', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        if (! $hasPurchased) {
            return back()->withErrors(['review' => 'Poți lăsa recenzie doar pentru produsele cumpărate.']);
        }

        $already = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($already) {
            return back()->withErrors(['review' => 'Ai lăsat deja o recenzie pentru acest produs.']);
        }

        Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => (int) $request->integer('rating'),
            'comment' => $request->input('body'),
        ]);

        return back()->with('success', 'Mulțumim pentru recenzie!');
    }
}
