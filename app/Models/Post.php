<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostCommentStatus;
use App\Enums\PostStatus;
use App\Services\ContentSanitizer;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'author_id',
    'category_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'status',
    'published_at',
    'archived_at',
    'source_platform',
    'source_url',
    'views_count',
    'comments_locked',
])]
class Post extends Model implements HasMedia
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;
    use LogsActivity;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saving(static function (self $post): void {
            if (blank($post->slug) && filled($post->title)) {
                $post->slug = Str::slug($post->title);
            }

            $post->content = app(ContentSanitizer::class)->sanitize($post->content);

            match ($post->status) {
                PostStatus::Draft => tap($post, static function (self $post): void {
                    $post->published_at = null;
                    $post->archived_at = null;
                }),
                PostStatus::Scheduled => $post->archived_at = null,
                PostStatus::Published => tap($post, static function (self $post): void {
                    $post->published_at ??= now();
                    $post->archived_at = null;
                }),
                PostStatus::Archived => $post->archived_at ??= now(),
            };
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'views_count' => 'integer',
            'comments_locked' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('posts')
            ->logOnly([
                'author_id',
                'category_id',
                'title',
                'slug',
                'excerpt',
                'content',
                'status',
                'published_at',
                'archived_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', PostCommentStatus::Approved);
    }

    public function commentsEnabled(): bool
    {
        if ($this->comments_locked) {
            return false;
        }

        $setting = BlogSetting::query()->first();

        return (bool) ($setting->comments_enabled ?? true);
    }

    public function averageRating(): ?float
    {
        $value = $this->approvedComments()->avg('score');

        return $value === null ? null : round((float) $value, 1);
    }

    public function ratingsCount(): int
    {
        return (int) $this->approvedComments()->count();
    }

    public function ratingDistribution(): array
    {
        $counts = $this->approvedComments()
            ->selectRaw('score, COUNT(*) as total')
            ->groupBy('score')
            ->pluck('total', 'score')
            ->all();

        $distribution = [];
        for ($score = 5; $score >= 1; $score--) {
            $distribution[$score] = (int) ($counts[$score] ?? 0);
        }

        return $distribution;
    }

    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->content ?? '')) / 220));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNull('archived_at');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('body')
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
            ->withResponsiveImages()
            ->queued()
            ->fit(Fit::Max, 1200, 1200)
            ->format('webp');
    }
}
