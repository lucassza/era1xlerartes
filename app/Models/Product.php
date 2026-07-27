<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PriceDisplayMode;
use App\Enums\ProductAvailability;
use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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
    'catalog_category_id',
    'brand_id',
    'name',
    'slug',
    'internal_code',
    'sku',
    'short_description',
    'description',
    'specifications',
    'features',
    'price',
    'promotional_price',
    'price_display_mode',
    'currency',
    'unit',
    'availability',
    'informational_stock',
    'inquiry_button_text',
    'is_featured',
    'sort_order',
    'status',
    'published_at',
    'seo_title',
    'seo_description',
])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;
    use LogsActivity;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saving(static function (self $product): void {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name);
            }

            match ($product->status) {
                ProductStatus::Published => tap($product, static function (self $product): void {
                    $product->published_at ??= now();
                }),
                ProductStatus::Draft => $product->published_at = null,
                default => null,
            };
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'informational_stock' => 'integer',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'status' => ProductStatus::class,
            'price_display_mode' => PriceDisplayMode::class,
            'availability' => ProductAvailability::class,
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'catalog_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag')->withTimestamps();
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            Attribute::class,
            'product_attribute_values',
        )->withPivot('value')->withTimestamps();
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(ProductInquiry::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'product_id',
            'related_product_id',
        )->withPivot('sort_order')->withTimestamps();
    }

    public function relatedTo(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'related_product_id',
            'product_id',
        )->withPivot('sort_order')->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePubliclyVisible(Builder $query, bool $showUnavailable = true): Builder
    {
        $query->where(function (Builder $q) use ($showUnavailable): void {
            $q->where('status', ProductStatus::Published);

            if ($showUnavailable) {
                $q->orWhere('status', ProductStatus::Unavailable);
            }
        });

        return $query;
    }

    /**
     * @param  Collection<int, CatalogCategory>|array<int, CatalogCategory>  $categories
     */
    public function scopeInCategories(Builder $query, iterable $categories): Builder
    {
        $ids = collect($categories)->flatMap(function (CatalogCategory $category) {
            $ids = [$category->getKey()];

            foreach ($category->descendantIds() as $descendant) {
                $ids[] = $descendant;
            }

            return $ids;
        })->unique()->values();

        if ($ids->isEmpty()) {
            return $query;
        }

        return $query->whereIn('catalog_category_id', $ids->all());
    }

    public function scopeOfBrand(Builder $query, int|string|null $brandId): Builder
    {
        return $brandId ? $query->where('brand_id', $brandId) : $query;
    }

    public function scopeWithAvailability(Builder $query, ?string $availability): Builder
    {
        return $availability ? $query->where('availability', $availability) : $query;
    }

    public function scopePriceBetween(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }

        return $query;
    }

    public function scopeSearchTerm(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        $needle = '%'.preg_replace('/\s+/', '%', $term).'%';

        return $query->where(function (Builder $q) use ($needle, $term): void {
            $q->whereRaw("search_tsv @@ plainto_tsquery('public.pt_unaccent', ?)", [$term])
                ->orWhere('name', 'ILIKE', $needle)
                ->orWhere('sku', 'ILIKE', $needle)
                ->orWhere('internal_code', 'ILIKE', $needle)
                ->orWhere('short_description', 'ILIKE', $needle)
                ->orWhere('description', 'ILIKE', $needle)
                ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'ILIKE', $needle))
                ->orWhereHas('tags', fn (Builder $t) => $t->where('name', 'ILIKE', $needle));
        });
    }

    public function scopeOrdered(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderByRaw('COALESCE(promotional_price, price) ASC NULLS LAST')->orderBy('name'),
            'price_desc' => $query->orderByRaw('COALESCE(promotional_price, price) DESC NULLS LAST')->orderBy('name'),
            'featured' => $query->orderByDesc('is_featured')->orderByDesc('published_at')->orderBy('name'),
            'sort_order' => $query->orderBy('sort_order')->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }

    public function effectivePrice(): ?float
    {
        if ($this->promotional_price !== null && $this->promotional_price > 0) {
            return (float) $this->promotional_price;
        }

        return $this->price !== null ? (float) $this->price : null;
    }

    public function showPrice(): bool
    {
        return $this->price_display_mode === PriceDisplayMode::Show;
    }

    public function inquiriesCount(): int
    {
        return (int) $this->inquiries()->count();
    }

    public function attributeValuesMap(): Collection
    {
        return $this->attributeValues()->with('attribute.group')->get()->groupBy('attribute.group.name');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('products')
            ->logOnly([
                'catalog_category_id',
                'brand_id',
                'name',
                'slug',
                'sku',
                'internal_code',
                'price',
                'promotional_price',
                'price_display_mode',
                'availability',
                'status',
                'published_at',
                'is_featured',
                'sort_order',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('gallery')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('attachments')
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
            ->withResponsiveImages()
            ->fit(Fit::Max, 1200, 1200)
            ->format('webp');
    }
}
