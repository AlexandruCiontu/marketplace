<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';

    public static function labels(): array
    {
        return [
            self::Draft->value => __('Draft'),
            self::Pending->value => __('Pending'),
            self::Published->value => __('Published'),
        ];
    }

    public static function colors(): array
    {
        return [
            'gray' => self::Draft->value,
            'warning' => self::Pending->value,
            'success' => self::Published->value,
        ];
    }
}
