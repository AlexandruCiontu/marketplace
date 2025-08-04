<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OssTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'order_id',
        'client_country_code',
        'vat_rate',
        'net_amount',
        'vat_amount',
        'gross_amount',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
