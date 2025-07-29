<?php

namespace App\Enums;

enum VatRateTypeEnum: string
{
    case Standard = 'standard_rate';
    case Reduced = 'reduced_rate';
    case ReducedAlt = 'reduced_rate_alt';
    case SuperReduced = 'super_reduced_rate';

    public static function labels(): array
    {
        return [
            self::Standard->value => __('Standard'),
            self::Reduced->value => __('Reduced'),
            self::ReducedAlt->value => __('Reduced Alt'),
            self::SuperReduced->value => __('Super Reduced'),
        ];
    }
}
