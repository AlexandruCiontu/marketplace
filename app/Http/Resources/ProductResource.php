<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\VatRateService;

class ProductResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        $options = $request->input('options') ?: [];
        $images = $options ? $this->getImagesForOptions($options) : $this->getImages();

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
                'store_name' => optional($this->user->vendor)->store_name ?? '',
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
                $calc = app(VatRateService::class)->calculate($variation->price, $this->vat_rate_type);
                return [
                    'id' => $variation->id,
                    'variation_type_option_ids' => $variation->variation_type_option_ids,
                    'quantity' => $variation->quantity,
                    'price' => $variation->price,
                    'gross_price' => $calc['gross'],
                    'vat_amount' => $calc['vat'],
                ];
            }),

            // ✅ TVA fields
            // ✅ TVA fields
            'net_price' => round((float) $this->price, 2),
            'vat_rate_type' => $this->vat_rate_type ?? 'standard_rate',
            'country_code' => session('country_code') ?? 'RO',
            'vat_amount' => round((float) $this->vat_amount, 2),
            'gross_price' => round((float) $this->gross_price, 2),
        ];
    }
}
