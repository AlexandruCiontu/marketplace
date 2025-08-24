<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        /** @var \App\Models\Product $product */
        $product = $this->resource;

        // traducem query-ul în IDs de opțiuni
        $optionIds = $product->resolveOptionIdsFromQuery($request->query());

        $country = session('country_code', config('vat.fallback_country','RO'));
        $country = strtoupper(\App\Support\CountryCode::toIso2($country) ?? 'RO');

        /** @var \App\Services\VatRateService $service */
        $service = app(\App\Services\VatRateService::class);
        $rate = $service->rateForProduct($this->resource, $country);
        $vat = $service->calculate((float) $this->price, $rate);

        return [
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
            'description' => $product->description,
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
            'price' => (float) $product->price,
            'weight' => $product->weight,
            'length' => $product->length,
            'width' => $product->width,
            'height' => $product->height,
            'quantity' => $product->quantity,
            'image' => $product->getFirstImageUrl(),
            'images' => $product->getImagesForOptions($optionIds),
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
            'vat_amount'  => (float) $vat['vat_amount'],
            'price_net'   => (float) $vat['price_net'],
            'price_gross' => (float) $vat['price_gross'],
            'country_code'=> $country,
        ];
    }
}
