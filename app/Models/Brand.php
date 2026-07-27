<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'name',
    'slug',
    'description',
    'website',
    'sort_order',
    'is_active',
])]
class Brand extends Model implements HasMedia
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;
    use LogsActivity;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saving(static function (self $brand): void {
            if (blank($brand->slug) && filled($brand->name)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('brands')
            ->logOnly(['name', 'slug', 'description', 'website', 'sort_order', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->nonQueued()
            ->format('webp');

        $this->addMediaConversion('thumb-300')
            ->queued()
            ->fit(Fit::Max, 300, 300)
            ->format('webp');

        $this->addMediaConversion('thumb-600')
            ->queued()
            ->fit(Fit::Max, 600, 600)
            ->format('webp');
    }
}
