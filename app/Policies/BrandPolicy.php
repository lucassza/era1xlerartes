<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('catalog.brands.manage');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->can('catalog.brands.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('catalog.brands.manage');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('catalog.brands.manage');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('catalog.brands.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('catalog.brands.manage');
    }
}
