<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\VatRateService;

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
        // 1. Calculate base net price for product
        $net = $this->getPriceForFirstOptions();

        // 2. Calculate VAT and gross price
        $vatResult = app(VatRateService::class)->calculate($net, $this->vat_rate_type);

        // 3. Format for frontend
        $netFormatted   = number_format($net, 2, '.', '');
        $vatFormatted   = number_format($vatResult['vat'], 2, '.', '');
        $grossFormatted = number_format($vatResult['gross'], 2, '.', '');

        return [
            // Identifier and basic data
            'id'                 => $this->id,
            'title'              => $this->title,
            'slug'               => $this->slug,

            // Prices (gross + net + VAT)
            'net_raw'            => $net,
            'vat_raw'            => $vatResult['vat'],
            'gross_raw'          => $vatResult['gross'],
            'net'                => $netFormatted,
            'vat'                => $vatFormatted,
            'gross'              => $grossFormatted,
            'gross_price'        => $vatResult['gross'], // important for React
            'net_price'          => round($net, 2),
            'vat_rate_type'      => $this->vat_rate_type ?? 'standard_rate',
            'country_code'       => session('country_code', 'RO'),

            // Stock and image
            'quantity'           => $this->quantity,
            'image'              => $this->getFirstImageUrl(),

            // Vendor information
            'user_id'            => $this->user->id,
            'user_name'          => $this->user->name,
            'user_store_name'    => optional($this->user->vendor)->store_name,

            // Department information
            'department_id'      => optional($this->department)->id,
            'department_name'    => optional($this->department)->name,
            'department_slug'    => optional($this->department)->slug,
        ];
    }
}
