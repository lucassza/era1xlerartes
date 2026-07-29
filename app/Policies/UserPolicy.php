<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.viewAny');
    }

    public function view(User $user, User $target): bool
    {
        return $user->is($target) || $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return true;
        }

        return $user->can('users.update');
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }

        return $user->can('users.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('users.delete');
    }

    public function ban(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->can('users.ban');
    }

    public function promote(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }

        return $user->hasRole('Super Admin') || $user->can('users.promote');
    }
}
