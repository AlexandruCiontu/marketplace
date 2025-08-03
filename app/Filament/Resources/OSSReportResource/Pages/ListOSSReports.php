<?php

namespace App\Filament\Resources\OSSReportResource\Pages;

use App\Enums\RolesEnum;
use App\Filament\Resources\OSSReportResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ListOSSReports extends Page
{
    protected static string $resource = OSSReportResource::class;

    protected static string $view = 'filament.resources.oss-report-resource.pages.list-oss-reports';

    public array $reports = [];

    public function mount(): void
    {
        $user = Filament::auth()->user();

        $directories = Storage::directories('exports/oss');
        $reports = [];

        foreach ($directories as $directory) {
            $period = basename($directory);

            foreach (Storage::files($directory) as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) !== 'csv') {
                    continue;
                }

                $vendorId = pathinfo($file, PATHINFO_FILENAME);

                if ($user->hasRole(RolesEnum::Vendor->value) && (int) $vendorId !== $user->id) {
                    continue;
                }

                $reports[] = [
                    'period' => $period,
                    'vendor' => $vendorId,
                    'url' => Storage::url($file),
                ];
            }
        }

        $this->reports = $reports;
    }
}
