<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $options = $request->input('options') ?: [];

        // Imagini fie pentru opțiuni selectate, fie fallback
        $images = $options ? $this->getImagesForOptions($options) : $this->getImages();

        // Calcule TVA + brut
        $calc = app(\App\Services\VatService::class)->calculate($this->price, $this->vat_rate_type);

        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'meta_title'        => $this->meta_title,
            'meta_description'  => $this->meta_description,
            'price'             => $this->price,
            'gross_price'       => $calc['gross'],
            'quantity'          => $this->quantity,
            'weight'            => $this->weight,
            'length'            => $this->length,
            'width'             => $this->width,
            'height'            => $this->height,

            // Imagine principală
            'image'             => $this->getFirstMediaUrl('images'),
            'main_image_url'    => $this->getFirstMediaUrl('images'),

            // Galerie imagini
            'images' => $images->map(function ($image) {
                return [
                    'id'    => $image->id,
                    'thumb' => $image->getUrl('thumb'),
                    'small' => $image->getUrl('small'),
                    'large' => $image->getUrl('large'),
                ];
            }),

            // Informații vânzător
            'user' => [
                'id'         => optional($this->user)->id,
                'name'       => optional($this->user)->name,
                'store_name' => optional(optional($this->user)->vendor)->store_name,
            ],

            // Informații departament
            'department' => [
                'id'   => optional($this->department)->id,
                'name' => optional($this->department)->name,
                'slug' => optional($this->department)->slug,
            ],

            // Variante produs (ex: culoare, mărime)
            'variationTypes' => $this->variationTypes->map(function ($variationType) {
                return [
                    'id'      => $variationType->id,
                    'name'    => $variationType->name,
                    'type'    => $variationType->type,
                    'options' => $variationType->options->map(function ($option) {
                        return [
                            'id'     => $option->id,
                            'name'   => $option->name,
                            'images' => $option->getMedia('images')->map(function ($image) {
                                return [
                                    'id'    => $image->id,
                                    'thumb' => $image->getUrl('thumb'),
                                    'small' => $image->getUrl('small'),
                                    'large' => $image->getUrl('large'),
                                ];
                            }),
                        ];
                    }),
                ];
            }),

            // Variante concrete (ex: culoare roșie + mărime M)
            'variations' => $this->variations->map(function ($variation) {
                $price = $variation->price !== null ? $variation->price : $this->price;

                $calc = app(\App\Services\VatService::class)->calculate($price, $this->vat_rate_type);

                return [
                    'id'                        => $variation->id,
                    'variation_type_option_ids' => $variation->variation_type_option_ids,
                    'quantity'                  => $variation->quantity,
                    'price'                     => $variation->price,
                    'gross_price'               => $calc['gross'],
                ];
            }),
            'average_rating' => round($this->averageRating() ?? 0, 1),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
