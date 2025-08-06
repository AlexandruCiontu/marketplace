<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'net_price' => $this->net_price,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'gross_price' => $this->gross_price,
            'variation_type_option_ids' => $this->variation_type_option_ids,
            
            // Include relations
            'product' => new ProductResource($this->whenLoaded('product')),
            'variationOptions' => VariationTypeOptionResource::collection($this->whenLoaded('variationOptions')),
        ];
    }
}
