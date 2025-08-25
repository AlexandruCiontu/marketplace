<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class VariationTypeOption extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $timestamps = false;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Crop, 64, 64)
            ->nonQueued();

        $this
            ->addMediaConversion('small')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();

        $this
            ->addMediaConversion('medium')
            ->fit(Fit::Contain, 600, 600)
            ->nonQueued();
    }

    public function variationType(): BelongsTo
    {
        return $this->belongsTo(VariationType::class);
    }
}
