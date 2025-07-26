<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, Searchable;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'department_id',
        'category_id',
        'price',
        'status',
        'quantity',
        'created_by',
        'updated_by',
        'vat_rate_type',
    ];

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

    public function scopeForVendor(Builder $query): Builder
    {
        return $query->where('created_by', auth()->id());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('products.status', ProductStatusEnum::Published);
    }

    public function scopeSearchable(Builder $query): Builder
    {
        return $this->scopePublished($query);
    }

    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->published()->vendorApproved();
    }

    public function scopeVendorApproved(Builder $query)
    {
        return $query->join('vendors', 'vendors.user_id', '=', 'products.created_by')
            ->where('vendors.status', VendorStatusEnum::Approved->value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variationTypes(): HasMany
    {
        return $this->hasMany(VariationType::class);
    }

    public function options(): HasManyThrough
    {
        return $this->hasManyThrough(
            VariationTypeOption::class,
            VariationType::class,
            'product_id',
            'variation_type_id',
            'id',
            'id'
        );
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getPriceForOptions($optionIds = []): float
    {
        $optionIds = array_values((array)$optionIds);
        sort($optionIds);

        foreach ($this->variations as $variation) {
            $a = $variation->variation_type_option_ids;
            sort($a);
            if ($a === $optionIds) {
                return $variation->price ?? $this->price;
            }
        }

        return $this->price;
    }

    public function getImageForOptions(array $optionIds = null): string
    {
        if ($optionIds) {
            $ids = array_values($optionIds);
            sort($ids);
            $options = VariationTypeOption::whereIn('id', $ids)->get();
            foreach ($options as $option) {
                $url = $option->getFirstMediaUrl('images', 'small');
                if ($url) {
                    return $url;
                }
            }
        }
        return $this->getFirstMediaUrl('images', 'small');
    }

    public function getImagesForOptions(array $optionIds = null)
    {
        if ($optionIds) {
            $ids = array_values($optionIds);
            sort($ids);
            $options = VariationTypeOption::whereIn('id', $ids)->get();
            foreach ($options as $option) {
                $media = $option->getMedia('images');
                if ($media && $media->isNotEmpty()) {
                    return $media;
                }
            }
        }
        return $this->getMedia('images');
    }

    public function getPriceForFirstOptions(): float
    {
        $firstOptions = $this->getFirstOptionsMap();
        return !empty($firstOptions)
            ? $this->getPriceForOptions($firstOptions)
            : $this->price;
    }

    public function getFirstImageUrl(string $collectionName = 'images', string $conversion = 'small'): string
    {
        if ($this->options->count()) {
            foreach ($this->options as $opt) {
                $url = $opt->getFirstMediaUrl($collectionName, $conversion);
                if ($url) {
                    return $url;
                }
            }
        }
        return $this->getFirstMediaUrl($collectionName, $conversion);
    }

    public function getImages(): MediaCollection
    {
        if ($this->options->count()) {
            foreach ($this->options as $opt) {
                $media = $opt->getMedia('images');
                if ($media && $media->isNotEmpty()) {
                    return $media;
                }
            }
        }
        return $this->getMedia('images');
    }

    public function getFirstOptionsMap(): array
    {
        return $this->variationTypes
            ->mapWithKeys(fn($type) => [$type->id => $type->options->first()?->id])
            ->toArray();
    }

    public function getTotalQuantity(mixed $optionIds = null): int
    {
        $ids = $optionIds ? (array)$optionIds : [];
        sort($ids);
        $variation = $this->variations->first(fn($v) => $v->variation_type_option_ids === $ids);
        $qty = $variation?->quantity ?? $this->quantity;
        return $qty === null ? PHP_INT_MAX : $qty;
    }

    public function searchableAs(): string
    {
        return 'products_index';
    }
    public function toSearchableArray(): array
    {
        $this->load(['category', 'department', 'user']);

        $basePrice = $this->getPriceForFirstOptions();
        $vatRateType = $this->vat_rate_type;

        // Folosește serviciul de TVA pentru a calcula prețul brut
        $grossPrice = app(\App\Services\VatService::class)
            ->calculate($basePrice, $vatRateType, session('country_code', 'RO'))['gross'] ?? $basePrice;

        return [
            'id' => (string)$this->id,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'price' => (float)$basePrice,
            'gross_price' => (float)$grossPrice,
            'vat_rate_type' => $vatRateType,
            'quantity' => $this->quantity,
            'image' => $this->getFirstImageUrl(),
            'user_id' => (string)$this->user->id,
            'user_name' => $this->user->name,
            'user_store_name' => $this->user->vendor->store_name,
            'department_id' => (string)$this->department->id,
            'department_name' => $this->department->name,
            'department_slug' => $this->department->slug,
            'category_id' => (string)($this->category?->id ?: ''),
            'category_name' => $this->category?->name ?: '',
            'category_slug' => $this->category?->slug ?: '',
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
