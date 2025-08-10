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
            return back()->withErrors(['review' => 'Trebuie să ai emailul verificat.']);
        }

        $hasPurchased = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereIn('status', [OrderStatusEnum::Paid->value, OrderStatusEnum::Delivered->value]);
            })
            ->exists();

        if (! $hasPurchased) {
            return back()->withErrors(['review' => 'Doar cumpărătorii pot lăsa recenzii.']);
        }

        $already = Review::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($already) {
            return back()->withErrors(['review' => 'Ai trimis deja o recenzie pentru acest produs.']);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()
            ->with('success', 'Mulțumim pentru recenzie!')
            ->with('newReview', ReviewResource::make($review));
    }
}
