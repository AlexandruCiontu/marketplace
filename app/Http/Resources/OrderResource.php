<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = null;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calculate subtotal
        $subtotal = 0;
        foreach ($this->orderItems as $item) {
            $subtotal += $item->price * $item->quantity;
        }

        return [
            'id' => $this->id,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tracking_code' => $this->tracking_code,
            'tracking_code_added_at' => $this->tracking_code_added_at,
            'online_payment_commission' => $this->online_payment_commission,
            'website_commission' => $this->website_commission,
            'vendor_subtotal' => $this->vendor_subtotal,
            'payment_intent' => $this->payment_intent,
            'subtotal' => $subtotal,
            
            // Include relations
            'orderItems' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'user' => new UserResource($this->whenLoaded('user')),
            'shippingAddress' => new AddressResource($this->whenLoaded('shippingAddress')),
            'vendorUser' => new UserResource($this->whenLoaded('vendorUser')),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
        ];
    }
}
