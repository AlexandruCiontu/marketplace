<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        $options = $request->input('options') ?: [];
        $images = $options ? $this->getImagesForOptions($options) : $this->getImages();

        $country = session('country_code', config('vat.fallback_country','RO'));
        $country = strtoupper(\App\Support\CountryCode::toIso2($country) ?? 'RO');

        /** @var \App\Services\VatRateService $vat */
        $vat = app(\App\Services\VatRateService::class);
        $rate = $vat->rateForProduct($this->resource, $country);

        $net   = (float) $this->price;
        $vatAm = (float) round($net * $rate / 100, 2);
        $gross = (float) round($net + $vatAm, 2);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'price' => $this->price,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'quantity' => $this->quantity,
            'image' => $this->getFirstImageUrl(),
            'images' => $images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'thumb' => $image->getUrl('thumb'),
                    'small' => $image->getUrl('small'),
                    'large' => $image->getUrl('large'),
                ];
            }),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'store_name' => $this->user->vendor->store_name,
            ],
            'department' => [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'slug' => $this->department->slug,
            ],
            'variationTypes' => $this->variationTypes->map(function ($variationType) {
                return [
                    'id' => $variationType->id,
                    'name' => $variationType->name,
                    'type' => $variationType->type,
                    'options' => $variationType->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'images' => $option->getMedia('images')->map(function ($image) {
                                return [
                                    'id' => $image->id,
                                    'thumb' => $image->getUrl('thumb'),
                                    'small' => $image->getUrl('small'),
                                    'large' => $image->getUrl('large'),
                                ];
                            })
                        ];
                    })
                ];
            }),
            'variations' => $this->variations->map(function ($variation) {
                return [
                    'id' => $variation->id,
                    'variation_type_option_ids' => $variation->variation_type_option_ids,
                    'quantity' => $variation->quantity,
                    'price' => $variation->price,
                ];
            }),

            // ✅ VAT fields computed server-side
            'vat_type'    => (string) $this->vat_type,
            'vat_rate'    => (float) $rate,
            'vat_amount'  => (float) $vatAm,
            'price_net'   => (float) $net,
            'price_gross' => (float) $gross,
            'country_code'=> $country,
        ];
    }
}
