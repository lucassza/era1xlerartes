<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PostComment;
use App\Models\User;

class PostCommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('comments.viewAny');
    }

    public function view(User $user, PostComment $comment): bool
    {
        return $user->can('comments.view');
    }

    public function update(User $user, PostComment $comment): bool
    {
        return $user->can('comments.update');
    }

    public function delete(User $user, PostComment $comment): bool
    {
        return $user->can('comments.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('comments.delete');
    }

    public function approve(User $user, PostComment $comment): bool
    {
        return $user->can('comments.approve');
    }

    public function reject(User $user, PostComment $comment): bool
    {
        return $user->can('comments.reject');
    }

    public function submit(User $user): bool
    {
        return ! $user->isBanned();
    }
}
