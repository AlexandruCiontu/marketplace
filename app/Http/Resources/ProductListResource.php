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
        $vatAm = (float) round($net * $rate / 100, 2);
        $gross = (float) round($net + $vatAm, 2);

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'vat_type'    => (string) $this->vat_type,
            'vat_rate'    => (float) $rate,
            'vat_amount'  => (float) $vatAm,
            'price_net'   => (float) $net,
            'price_gross' => (float) $gross,
            'price'       => (float) $gross,
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
