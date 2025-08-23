<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ReviewResource;

class ProductResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        $country = session('country_code', config('vat.fallback_country','RO'));
        $country = strtoupper(\App\Support\CountryCode::toIso2($country) ?? 'RO');

        /** @var \App\Services\VatRateService $service */
        $service = app(\App\Services\VatRateService::class);
        $rate = $service->rateForProduct($this->resource, $country);
        $vat = $service->calculate((float) $this->price, $rate);

        $images = $this->getMedia('images')->map(function ($m) {
            return [
                'url'   => $m->getUrl(),
                'small' => $m->hasGeneratedConversion('small') ? $m->getUrl('small') : $m->getUrl(),
                'thumb' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
            ];
        })->values();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'price' => (float) $this->price,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'quantity' => $this->quantity,
            'images' => $images,
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
            'variationTypes' => $this->whenLoaded('variationTypes', function () {
                return $this->variationTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'type' => $type->type,
                        'options' => $type->options->map(function ($op) {
                            $media = method_exists($op, 'getMedia') ? $op->getMedia('images') : collect();
                            return [
                                'id' => $op->id,
                                'name' => $op->name,
                                'images' => $media->map(function ($m) {
                                    return [
                                        'url'   => $m->getUrl(),
                                        'small' => $m->hasGeneratedConversion('small') ? $m->getUrl('small') : $m->getUrl(),
                                        'thumb' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
                                    ];
                                })->values(),
                            ];
                        })->values(),
                    ];
                })->values();
            }),
            'variations' => $this->whenLoaded('variations', fn () => $this->variations->map(fn ($v) => [
                'variation_type_option_ids' => $v->variation_type_option_ids,
                'price' => $v->price,
                'quantity' => $v->quantity,
            ])->values()),

            // ✅ VAT fields computed server-side
            'vat_type'    => (string) $this->vat_type,
            'vat_rate'    => (float) $rate,
            'vat_amount'  => (float) $vat['vat_amount'],
            'price_net'   => (float) $vat['price_net'],
            'price_gross' => (float) $vat['price_gross'],
            'country_code'=> $country,
            'reviews_count' => $this->when(
                isset($this->reviews_count),
                (int) $this->reviews_count,
                fn () => $this->reviews->count()
            ),
            'rating_average' => $this->when(
                isset($this->reviews_avg_rating),
                round((float) $this->reviews_avg_rating, 2),
                fn () => round((float) $this->reviews->avg('rating'), 2)
            ),
            'reviews' => ReviewResource::collection($this->reviews->sortByDesc('created_at')->take(5)),
        ];
    }
}
