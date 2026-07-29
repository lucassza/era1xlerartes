<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('catalog.products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('catalog.products.view');
    }

    public function create(User $user): bool
    {
        return $user->can('catalog.products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('catalog.products.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('catalog.products.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('catalog.products.delete');
    }

    public function publish(User $user, Product $product): bool
    {
        return $user->can('catalog.products.publish');
    }
}
