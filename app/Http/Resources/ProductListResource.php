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
        // 1. Calculăm prețul net de bază pentru produs
        $net = $this->getPriceForFirstOptions();

        // 2. Calculăm TVA și prețul brut (gross)
        $vatResult = app(VatService::class)->calculate($net, $this->vat_rate_type);

        // 3. Formatare pentru frontend
        $netFormatted   = number_format($net, 2, '.', '');
        $vatFormatted   = number_format($vatResult['vat'], 2, '.', '');
        $grossFormatted = number_format($vatResult['gross'], 2, '.', '');

        return [
            // Identificator și date de bază
            'id'                 => $this->id,
            'title'              => $this->title,
            'slug'               => $this->slug,

            // Prețuri (brut + net + TVA)
            'net_raw'            => $net,
            'vat_raw'            => $vatResult['vat'],
            'gross_raw'          => $vatResult['gross'],
            'net'                => $netFormatted,
            'vat'                => $vatFormatted,
            'gross'              => $grossFormatted,
            'gross_price'        => $vatResult['gross'], // ✅ important pentru React
            'net_price'          => round($net, 2),
            'vat_rate_type'      => $this->vat_rate_type ?? 'standard',
            'country_code'       => session('country_code', 'RO'),

            // Stoc și imagine
            'quantity'           => $this->quantity,
            'image'              => $this->getFirstImageUrl(),

            // Informații despre vânzător
            'user_id'            => $this->user->id,
            'user_name'          => $this->user->name,
            'user_store_name'    => optional($this->user->vendor)->store_name,

            // Informații despre departament
            'department_id'      => optional($this->department)->id,
            'department_name'    => optional($this->department)->name,
            'department_slug'    => optional($this->department)->slug,
        ];
    }
}
