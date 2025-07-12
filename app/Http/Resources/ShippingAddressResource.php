<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingAddressResource extends JsonResource
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
            'full_name' => $this->full_name,
            'user_id' => $this->user_id,
            'country_code' => $this->country_code,
            'country' => $this->country,
            'city' => $this->city,
            'type' => $this->type,
            'zipcode' => $this->zipcode,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'state' => $this->state,
            'default' => (bool)$this->default,
            'phone' => $this->phone,
            'delivery_instructions' => $this->delivery_instructions,
        ];
    }
}
