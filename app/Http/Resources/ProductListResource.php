<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = $this->getPriceForFirstOptions();
        $vat = app(\App\Services\VatService::class)->calculate($price, $this->vat_rate_type);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'price' => $price,
            'gross_price' => $vat['gross'],
            'quantity' => $this->quantity,
            'image' => $this->getFirstImageUrl(),
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_store_name' => $this->user->vendor->store_name,
            'department_id' => $this->department->id,
            'department_name' => $this->department->name,
            'department_slug' => $this->department->slug,
        ];
    }
}
