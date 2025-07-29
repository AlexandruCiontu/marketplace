<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VariationTypeOption extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $timestamps = false;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->nonQueued();

        $this->addMediaConversion('small')
            ->width(480)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1200)
            ->nonQueued();
    }

    public function variationType(): BelongsTo
    {
        return $this->belongsTo(VariationType::class);
    }

    /**
     * Allow eager loading media as 'images'.
     */
    public function images(): MorphMany
    {
        /** @var \Illuminate\Database\Eloquent\Relations\MorphMany $relation */
        $relation = $this->media();
        return $relation->where('collection_name', 'images');
    }
}
