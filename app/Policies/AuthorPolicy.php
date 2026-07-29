<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Author;
use App\Models\User;

class AuthorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('authors.viewAny');
    }

    public function view(User $user, Author $author): bool
    {
        if ($user->hasRole('Editor')) {
            return $user->can('authors.view');
        }

        return $user->can('authors.view') && $user->author?->is($author);
    }

    public function create(User $user): bool
    {
        return $user->can('authors.create');
    }

    public function update(User $user, Author $author): bool
    {
        if ($user->hasRole('Editor')) {
            return $user->can('authors.update');
        }

        return $user->can('authors.update') && $user->author?->is($author);
    }

    public function delete(User $user, Author $author): bool
    {
        return $user->hasRole('Editor') && $user->can('authors.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('Editor') && $user->can('authors.delete');
    }
}
