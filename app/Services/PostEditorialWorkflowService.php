<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PostEditorialWorkflowService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws AuthorizationException
     */
    public function guardAndNormalizePayload(array $data, User $actor): array
    {
        $status = PostStatus::from((string) ($data['status'] ?? PostStatus::Draft->value));
        $publishedAt = $this->normalizeDate($data['published_at'] ?? null);
        $archivedAt = $this->normalizeDate($data['archived_at'] ?? null);

        if (in_array($status, [PostStatus::Scheduled, PostStatus::Published, PostStatus::Archived], true) && ! $actor->can('posts.publish')) {
            throw new AuthorizationException('Você não tem permissão para publicar, agendar ou arquivar posts.');
        }

        if ($status === PostStatus::Scheduled) {
            if ($publishedAt === null || $publishedAt->isPast()) {
                throw new AuthorizationException('Posts agendados exigem uma data futura de publicação.');
            }

            $archivedAt = null;
        }

        if ($status === PostStatus::Published) {
            $publishedAt ??= now();
            $archivedAt = null;
        }

        if ($status === PostStatus::Draft) {
            $publishedAt = null;
            $archivedAt = null;
        }

        if ($status === PostStatus::Archived) {
            $archivedAt ??= now();
        }

        $data['status'] = $status->value;
        $data['published_at'] = $publishedAt;
        $data['archived_at'] = $archivedAt;

        return $data;
    }

    public function publishDueScheduledPosts(): int
    {
        return DB::transaction(function (): int {
            /** @var Collection<int, Post> $posts */
            $posts = Post::query()
                ->where('status', PostStatus::Scheduled)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->lockForUpdate()
                ->get();

            $posts->each(function (Post $post): void {
                $post->update([
                    'status' => PostStatus::Published,
                    'published_at' => $post->published_at ?? now(),
                    'archived_at' => null,
                ]);
            });

            return $posts->count();
        });
    }

    private function normalizeDate(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}
