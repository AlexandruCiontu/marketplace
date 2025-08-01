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

    protected $fillable = [
        'user_id',
        'status',
        'store_name',
        'store_address',
        'country_code',
        'phone',
        'cover_image',
        'commission_rate',
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
