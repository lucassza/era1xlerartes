<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductInquiryNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_inquiry_id',
    'user_id',
    'note',
])]
class ProductInquiryNote extends Model
{
    /** @use HasFactory<ProductInquiryNoteFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(ProductInquiry::class, 'product_inquiry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
