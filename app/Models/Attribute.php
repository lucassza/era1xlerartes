<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AttributeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'attribute_group_id',
    'name',
    'slug',
    'type',
    'sort_order',
])]
class Attribute extends Model
{
    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saving(static function (self $attribute): void {
            if (blank($attribute->slug) && filled($attribute->name)) {
                $attribute->slug = Str::slug($attribute->name);
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }
}
