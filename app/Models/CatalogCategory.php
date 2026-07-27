<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CatalogCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'parent_id',
    'name',
    'slug',
    'description',
    'icon',
    'sort_order',
    'is_active',
    'seo_title',
    'seo_description',
])]
class CatalogCategory extends Model implements HasMedia
{
    /** @use HasFactory<CatalogCategoryFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;
    use LogsActivity;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saving(static function (self $category): void {
            if (blank($category->slug) && filled($category->name)) {
                $category->slug = Str::slug($category->name);
            }

            if (filled($category->parent_id) && filled($category->id)) {
                static::guardAgainstCycle($category);
            }
        });
    }

    public static function guardAgainstCycle(self $category): void
    {
        $visited = [];
        $currentId = $category->parent_id;

        while (filled($currentId)) {
            if ($currentId === $category->id || in_array($currentId, $visited, true)) {
                throw new \DomainException('Hierarquia de categoria inválida (ciclo detectado).');
            }

            $visited[] = $currentId;
            $currentId = static::query()
                ->whereKey($currentId)
                ->value('parent_id');
        }
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributeGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeGroup::class,
            'attribute_group_catalog_category',
            'catalog_category_id',
            'attribute_group_id',
        )->withTimestamps();
    }

    /**
     * @return Collection<int, self>
     */
    public function descendantIds(): Collection
    {
        $ids = collect();
        $stack = [$this->id];

        while ($stack) {
            $current = array_shift($stack);
            $children = static::query()->where('parent_id', $current)->pluck('id')->all();
            foreach ($children as $child) {
                if (! $ids->contains($child) && $child !== $this->id) {
                    $ids->push($child);
                    $stack[] = $child;
                }
            }
        }

        return $ids;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('catalog_categories')
            ->logOnly([
                'parent_id',
                'name',
                'slug',
                'description',
                'icon',
                'sort_order',
                'is_active',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
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
            ->fit(Fit::Crop, 300, 300)
            ->format('webp');

        $this->addMediaConversion('thumb-600')
            ->queued()
            ->fit(Fit::Crop, 600, 600)
            ->format('webp');

        $this->addMediaConversion('thumb-1200')
            ->queued()
            ->fit(Fit::Max, 1200, 1200)
            ->format('webp');
    }
}
