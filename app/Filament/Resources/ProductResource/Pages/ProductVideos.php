<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class ProductVideos extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Videos';

    protected static ?string $navigationIcon = 'heroicon-c-film';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('videos')
                    ->label(false)
                    ->openable()
                    ->panelLayout('grid')
                    ->collection('videos')
                    ->acceptedFileTypes([
                        'video/mp4',
                        'video/webm',
                        'video/quicktime',
                    ])
                    ->maxFiles(1)
                    ->rules([
                        function (string $attribute, $value, \Closure $fail) {
                            $file = is_array($value) ? $value[0] : $value;
                            if ($file instanceof \Illuminate\Http\UploadedFile) {
                                $getID3 = new \getID3();
                                $info = $getID3->analyze($file->getRealPath());
                                if (($info['playtime_seconds'] ?? 0) > 60) {
                                    $fail('Video must be max 60 seconds long.');
                                }
                            }
                        },
                    ])
                    ->hidden(fn ($livewire) => $livewire->record?->hasMedia('videos'))
                    ->columnSpan(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
