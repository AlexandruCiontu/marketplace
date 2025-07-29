<?php

namespace App\Http\Resources;

use App\Services\VatService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Product;
use App\Models\VariationTypeOption;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $vatService = app(VatService::class);
        $countryCode = session('country_code', 'RO');

        $product = $this->whenLoaded('product');
        if (!$product instanceof Product) {
            $product = Product::find($this->product_id);
        }

        $rateType = $product->vat_rate_type ?: 'standard';
        $vatData = $vatService->calculate(
            netAmount: $this->price,
            rateType: $rateType,
            countryCode: $countryCode,
        );

        $options = [];
        $optionIds = $this->variation_type_option_ids ?? [];
        if (!empty($optionIds)) {
            $optionModels = VariationTypeOption::with('variationType')
                ->whereIn('id', $optionIds)
                ->get()
                ->keyBy('id');
            foreach ($optionIds as $id) {
                $option = $optionModels->get($id);
                if ($option) {
                    $options[] = [
                        'id' => $id,
                        'name' => $option->name,
                        'type' => [
                            'id' => $option->variationType->id,
                            'name' => $option->variationType->name,
                        ],
                    ];
                }
            }
        }

        return [
            'id' => $this->id,
            'product_id' => $product->id,
            'title' => $product->title,
            'image' => $product->getFirstImageUrl(),
            'quantity' => $this->quantity,
            'option_ids' => $optionIds,
            'options' => $options,
            'price' => round($this->price, 2),
            'vat_rate_type' => $rateType,
            'vat_rate' => $vatData['rate'],
            'vat_amount' => $vatData['vat'],
            'price_with_vat' => $vatData['gross'],
            'gross_price' => $vatData['gross'],
        ];
    }
}
