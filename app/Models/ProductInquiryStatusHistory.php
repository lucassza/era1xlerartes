<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InquiryStatus;
use Database\Factories\ProductInquiryStatusHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_inquiry_id',
    'from_status',
    'to_status',
    'user_id',
    'note',
])]
class ProductInquiryStatusHistory extends Model
{
    /** @use HasFactory<ProductInquiryStatusHistoryFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => InquiryStatus::class,
            'to_status' => InquiryStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(ProductInquiry::class, 'product_inquiry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
