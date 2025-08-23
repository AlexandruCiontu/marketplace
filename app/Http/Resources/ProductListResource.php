<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\CountryCode;
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
        $country = session('country_code', config('vat.fallback_country','RO'));
        $country = strtoupper(CountryCode::toIso2($country) ?? 'RO');

        /** @var VatRateService $vat */
        $vat = app(VatRateService::class);

        $net   = (float) $this->getPriceForFirstOptions();
        $rate  = $vat->rateForProduct($this->resource, $country);
        $vatAm = round($net * $rate / 100, 2);
        $gross = round($net + $vatAm, 2);

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'vat_type'    => $this->vat_type,
            'vat_rate'    => $rate,
            'vat_amount'  => $vatAm,
            'price_net'   => $net,
            'price_gross' => $gross,
            'country_code'=> $country,
            'quantity'    => $this->quantity,
            'image'       => $this->getFirstImageUrl(),
            'user_id'     => $this->user->id,
            'user_name'   => $this->user->name,
            'user_store_name' => optional($this->user->vendor)->store_name,
            'department_id'   => optional($this->department)->id,
            'department_name' => optional($this->department)->name,
            'department_slug' => optional($this->department)->slug,
        ];
    }
}
