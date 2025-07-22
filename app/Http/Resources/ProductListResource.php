<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\VatService;

class ProductListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 1) Get the net price you already compute
        $net = $this->getPriceForFirstOptions();

        // 2) Run VAT calculation (rate, vat amount, gross)
        $vatResult = app(VatService::class)
            ->calculate($net, $this->vat_rate_type);

        // 3) Format numbers as strings with two decimals
        $netFormatted   = number_format($net,          2, '.', '');
        $vatFormatted   = number_format($vatResult['vat'],   2, '.', '');
        $grossFormatted = number_format($vatResult['gross'], 2, '.', '');

        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'slug'               => $this->slug,
            // raw values if you need them in JS
            'net_raw'            => $net,
            'vat_raw'            => $vatResult['vat'],
            'gross_raw'          => $vatResult['gross'],

            // nicely formatted strings for display
            'net'                => $netFormatted,
            'vat'                => $vatFormatted,
            'gross'              => $grossFormatted,

            'quantity'           => $this->quantity,
            'image'              => $this->getFirstImageUrl(),

            // seller info
            'user_id'            => $this->user->id,
            'user_name'          => $this->user->name,
            'user_store_name'    => $this->user->vendor->store_name,

            // department info
            'department_id'      => $this->department->id,
            'department_name'    => $this->department->name,
            'department_slug'    => $this->department->slug,
        ];
    }
}
