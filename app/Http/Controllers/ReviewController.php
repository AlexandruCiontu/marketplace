<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $hasPurchased = Order::where('user_id', auth()->id())
            ->whereHas('orderItems', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        if (! $hasPurchased || ! auth()->user()->hasVerifiedEmail()) {
            abort(403, 'Only verified buyers can leave a review.');
        }

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->back();
    }
}
