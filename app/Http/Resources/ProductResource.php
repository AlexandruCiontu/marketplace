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
        $videos = $this->getVideos();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'price' => $this->price,
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
            'videos' => $videos->map(function ($video) {
                return [
                    'id' => $video->id,
                    'url' => $video->getUrl(),
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

            // ✅ TVA fields
            // ✅ TVA fields
            'net_price' => round((float) $this->price, 2),
            'vat_rate_type' => $this->vat_rate_type ?? 'standard_rate',
            'country_code' => session('country_code') ?? 'RO',
            'price_with_vat' => round((float) $this->price_with_vat, 2),
            'vat_amount' => round((float) $this->vat_amount, 2),
            'gross_price' => round((float) $this->gross_price, 2),
        ];
    }
}
