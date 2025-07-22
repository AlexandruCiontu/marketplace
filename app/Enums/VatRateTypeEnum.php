<?php

namespace App\Enums;

enum VatRateTypeEnum: string
{
    case Standard = 'standard';
    case Reduced = 'reduced';
    case Reduced2 = 'reduced2';
    case Zero = 'zero';

    public static function labels(): array
    {
        return [
            self::Standard->value => __('Standard'),
            self::Reduced->value => __('Reduced'),
            self::Reduced2->value => __('Reduced 2'),
            self::Zero->value => __('Zero'),
        ];
    }
}
