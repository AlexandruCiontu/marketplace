<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariationTypeOptionResource extends JsonResource
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
            'variation_type_id' => $this->variation_type_id,
            'name' => $this->name,

            // Include relations
            'variationType' => $this->whenLoaded('variationType', function() {
                return [
                    'id' => $this->variationType->id,
                    'name' => $this->variationType->name,
                    'frontend_type' => $this->variationType->frontend_type,
                ];
            }),
        ];
    }
}
