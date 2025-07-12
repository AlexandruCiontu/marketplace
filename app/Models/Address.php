<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'country_code',
        'full_name',
        'phone',
        'city',
        'type',
        'zipcode',
        'address1',
        'address2',
        'state',
        'primary',
        'delivery_instructions',
    ];
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function belongs(User $user): bool
    {
        return $this->addressable_type === User::class && $this->addressable_id === $user->id;
    }
}
