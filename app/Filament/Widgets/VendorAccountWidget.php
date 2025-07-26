<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class VendorAccountWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.vendor-account-widget';
}
