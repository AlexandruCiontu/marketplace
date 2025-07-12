<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CartItem extends Model
{
    //

    protected $casts = [
        'variation_type_option_ids' => 'array'
    ];

    public function scopeFilterByOptions($query, array $optionIds)
    {
        ksort($optionIds);

        if (DB::getDriverName() === 'sqlite') {
            return $query->where('variation_type_option_ids', json_encode($optionIds));
        } else {
            return $query->whereJsonContains('variation_type_option_ids', $optionIds);
        }
    }
}
