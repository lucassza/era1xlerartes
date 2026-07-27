<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AttributeGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'sort_order',
])]
class AttributeGroup extends Model
{
    /** @use HasFactory<AttributeGroupFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saving(static function (self $group): void {
            if (blank($group->slug) && filled($group->name)) {
                $group->slug = Str::slug($group->name);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }

    public function catalogCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            CatalogCategory::class,
            'attribute_group_catalog_category',
        )->withTimestamps();
    }
}
