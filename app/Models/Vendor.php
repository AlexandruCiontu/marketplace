<?php

namespace App\Models;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'status',
        'store_name',
        'store_address',
        'country_code',
        'phone',
        'cover_image',
        'commission_rate',
        'nav_user_id',
        'nav_exchange_key',
        'anaf_pfx_path',
        'anaf_certificate_password',
    ];

    protected $casts = [
        'nav_user_id' => 'encrypted',
        'nav_exchange_key' => 'encrypted',
        'anaf_certificate_password' => 'encrypted',
    ];

    public function scopeEligibleForPayout(Builder $query): Builder
    {
        return $query
            ->where('status', VendorStatusEnum::Approved)
            ->join('users', 'users.id', '=', 'vendors.user_id')
            ->where('users.stripe_account_active', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
