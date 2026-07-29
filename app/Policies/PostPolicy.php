<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('posts.viewAny');
    }

    public function view(User $user, Post $post): bool
    {
        if ($user->hasRole('Editor')) {
            return $user->can('posts.view');
        }

        return $user->can('posts.view') && $user->author?->is($post->author);
    }

    public function create(User $user): bool
    {
        return $user->can('posts.create');
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->hasRole('Editor')) {
            return $user->can('posts.update');
        }

        return $user->can('posts.update') && $user->author?->is($post->author);
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->hasRole('Editor')) {
            return $user->can('posts.delete');
        }

        return $user->can('posts.delete') && $user->author?->is($post->author);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('posts.delete');
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->can('posts.publish');
    }
}
