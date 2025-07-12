<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'variation_type_option_ids'
    ];

    protected $casts = [
        'variation_type_option_ids' => 'array'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Define variationOptions as an attribute
     * This works with both the admin invoice and frontend
     */
    protected function variationOptions(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->variation_type_option_ids)) {
                    return collect([]);
                }

                return VariationTypeOption::with('variationType')
                    ->whereIn('id', $this->variation_type_option_ids)
                    ->get();
            }
        );
    }
}
