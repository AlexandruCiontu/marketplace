<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Ibericode\Vat\Facades\Vat;
use App\Helpers\VatHelper;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, Searchable;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(100)->nonQueued();
        $this->addMediaConversion('small')->width(480)->nonQueued();
        $this->addMediaConversion('large')->width(1200)->nonQueued();
    }

    public function scopeForVendor(Builder $query): Builder
    {
        return $query->where('created_by', auth()->user()->id);
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

    public function getPriceForOptions($optionIds = [])
    {
        $optionIds = array_values($optionIds);
        sort($optionIds);
        foreach ($this->variations as $variation) {
            $a = $variation->variation_type_option_ids;
            sort($a);
            if ($optionIds == $a) {
                return $variation->price !== null ? $variation->price : $this->price;
            }
        }
        return $this->price;
    }

    public function getImageForOptions(array $optionIds = null)
    {
        if ($optionIds) {
            $optionIds = array_values($optionIds);
            sort($optionIds);
            $options = VariationTypeOption::whereIn('id', $optionIds)->get();
            foreach ($options as $option) {
                $image = $option->getFirstMediaUrl('images', 'small');
                if ($image) {
                    return $image;
                }
            }
        }
        return $this->getFirstMediaUrl('images', 'small');
    }

    public function getImagesForOptions(array $optionIds = null)
    {
        if ($optionIds) {
            $optionIds = array_values($optionIds);
            $options = VariationTypeOption::whereIn('id', $optionIds)->get();
            foreach ($options as $option) {
                $images = $option->getMedia('images');
                if ($images) {
                    return $images;
                }
            }
        }
        return $this->getMedia('images');
    }

    public function getPriceForFirstOptions(): float
    {
        $firstOptions = $this->getFirstOptionsMap();
        if ($firstOptions) {
            return $this->getPriceForOptions($firstOptions);
        }
        return $this->price;
    }

    public function getFirstImageUrl($collectionName = 'images', $conversion = 'small'): string
    {
        if ($this->options->count() > 0) {
            foreach ($this->options as $option) {
                $imageUrl = $option->getFirstMediaUrl($collectionName, $conversion);
                if ($imageUrl) {
                    return $imageUrl;
                }
            }
        }
        return $this->getFirstMediaUrl($collectionName, $conversion);
    }

    public function getImages(): MediaCollection
    {
        if ($this->options->count() > 0) {
            foreach ($this->options as $option) {
                $images = $option->getMedia('images');
                if ($images) {
                    return $images;
                }
            }
        }
        return $this->getMedia('images');
    }

    public function getVideos(): MediaCollection
    {
        return $this->getMedia('videos');
    }

    public function getFirstOptionsMap(): array
    {
        return $this->variationTypes
            ->mapWithKeys(function ($type) {
                $firstOptionId = $type->options->first()?->id;
                return [$type->id => $firstOptionId];
            })
            ->toArray();
    }

    public function getTotalQuantity(mixed $optionIds)
    {
        $optionIds = $optionIds ? array_values($optionIds) : [];
        sort($optionIds);
        $variation = $this->variations->first(fn($variation) => $variation->variation_type_option_ids == $optionIds);

        $quantity = $this->quantity;
        if ($variation) {
            $quantity = $variation->quantity;
        }

        if ($quantity === null) {
            $quantity = $this->quantity;
        }

        return $quantity === null ? PHP_INT_MAX : $quantity;
    }

    public function searchableAs()
    {
        return 'products_index';
    }

    public function toSearchableArray()
    {
        $this->load(['category', 'department', 'user']);

        return [
            'id' => (string)$this->id,
            'title' => $this->title,
            'description' => strip_tags($this->description),
            'slug' => $this->slug,
            'price' => (float)$this->getPriceForFirstOptions(),
            'vat_rate_type' => $this->vat_rate_type,
            'quantity' => $this->quantity,
            'image' => $this->getFirstImageUrl(),
            'user_id' => (string)$this->user->id,
            'user_name' => $this->user->name,
            'user_store_name' => $this->user->vendor->store_name,
            'department_id' => (string)($this->department->id ?? ''),
            'department_name' => $this->department->name ?? '',
            'department_slug' => $this->department->slug ?? '',
            'category_id' => (string)($this->category?->id ?? ''),
            'category_name' => $this->category?->name ?? '',
            'category_slug' => $this->category?->slug ?? '',
            'created_at' => $this->created_at->timestamp,
        ];
    }


    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ✅ TVA calculat dinamic pe baza codului de țară
    public function getVatAmountAttribute(): float
    {
        $country = session('country_code', 'RO'); // fallback dacă nu e setată
        $rate = \App\Helpers\VatHelper::getRate($country, $this->vat_rate_type);

        return round($this->price * ($rate / 100), 2);
    }

    public function getGrossPriceAttribute(): float
    {
        $country = session('country_code', 'RO'); // fallback
        $rate = \App\Helpers\VatHelper::getRate($country, $this->vat_rate_type);

        return round($this->price * (1 + $rate / 100), 2);
    }

}
