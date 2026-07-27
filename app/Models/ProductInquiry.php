<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InquiryStatus;
use Database\Factories\ProductInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id',
    'product_name',
    'name',
    'email',
    'phone',
    'company',
    'city',
    'state',
    'subject',
    'message',
    'origin',
    'source_url',
    'utm',
    'ip',
    'user_agent',
    'status',
    'assignee_id',
    'first_viewed_at',
    'first_answered_at',
    'completed_at',
])]
class ProductInquiry extends Model
{
    /** @use HasFactory<ProductInquiryFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'utm' => 'array',
            'status' => InquiryStatus::class,
            'first_viewed_at' => 'datetime',
            'first_answered_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProductInquiryStatusHistory::class)->orderBy('created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ProductInquiryNote::class)->orderBy('created_at', 'desc');
    }
}
