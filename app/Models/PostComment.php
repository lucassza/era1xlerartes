<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostCommentStatus;
use Database\Factories\PostCommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'post_id',
    'user_id',
    'name',
    'email',
    'content',
    'score',
    'status',
    'approved_at',
    'rejected_at',
    'rejection_reason',
    'ip',
    'user_agent',
])]
class PostComment extends Model
{
    /** @use HasFactory<PostCommentFactory> */
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
            'status' => PostCommentStatus::class,
            'score' => 'integer',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === PostCommentStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === PostCommentStatus::Approved;
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PostCommentStatus::Approved->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PostCommentStatus::Pending->value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('post_comments')
            ->logOnly(['status', 'approved_at', 'rejected_at', 'rejection_reason', 'score'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
