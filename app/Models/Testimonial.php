<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TestimonialStatus;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'user_id',
    'name',
    'email',
    'city',
    'content',
    'score',
    'status',
    'approved_at',
    'rejected_at',
    'rejection_reason',
    'ip',
    'user_agent',
])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    use HasUuids;
    use LogsActivity;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TestimonialStatus::class,
            'score' => 'integer',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === TestimonialStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === TestimonialStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === TestimonialStatus::Rejected;
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', TestimonialStatus::Approved->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TestimonialStatus::Pending->value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('testimonials')
            ->logOnly(['status', 'approved_at', 'rejected_at', 'rejection_reason', 'score'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
