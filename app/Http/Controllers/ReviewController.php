<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\ReviewResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $user = $request->user();

        $data = $request->validate([
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $user->hasVerifiedEmail()) {
            return back()->withErrors(['review' => 'You must verify your email.']);
        }

        $hasPurchased = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereIn('status', [OrderStatusEnum::Paid->value, OrderStatusEnum::Delivered->value]);
            })
            ->exists();

        if (! $hasPurchased) {
            return back()->withErrors(['review' => 'Only buyers can leave reviews.']);
        }

        $already = Review::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($already) {
            return back()->withErrors(['review' => 'You have already reviewed this product.']);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()
            ->with('success', 'Thanks for your review!')
            ->with('newReview', ReviewResource::make($review));
    }
}
