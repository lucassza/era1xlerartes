<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CatalogCategory;
use App\Models\User;

class CatalogCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('catalog.categories.manage');
    }

    public function view(User $user, CatalogCategory $category): bool
    {
        return $user->can('catalog.categories.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('catalog.categories.manage');
    }

    public function update(User $user, CatalogCategory $category): bool
    {
        return $user->can('catalog.categories.manage');
    }

    public function delete(User $user, CatalogCategory $category): bool
    {
        return $user->can('catalog.categories.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('catalog.categories.manage');
    }
}
