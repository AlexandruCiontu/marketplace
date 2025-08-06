<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderViewResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'gross_price' => $this->total_price,
            'net_total' => $this->net_total,
            'vat_total' => $this->vat_total,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'vendorUser' => new VendorUserResource($this->vendorUser),
            'orderItems' => $this->orderItems->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'net_price' => $item->net_price,
                'vat_rate' => $item->vat_rate,
                'vat_amount' => $item->vat_amount,
                'gross_price' => $item->gross_price,
                'variation_type_option_ids' => $item->variation_type_option_ids,
                'product' => [
                    'id' => $item->product->id,
                    'title' => $item->product->title,
                    'slug' => $item->product->slug,
                    'description' => $item->product->description,
                    'image' => $item->product->getImageForOptions($item->variation_type_option_ids ?: []),
                ],
            ]),
        ];
    }
}
