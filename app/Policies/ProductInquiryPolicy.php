<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductInquiry;
use App\Models\User;

class ProductInquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('catalog.inquiries.view');
    }

    public function view(User $user, ProductInquiry $inquiry): bool
    {
        return $user->can('catalog.inquiries.view');
    }

    public function update(User $user, ProductInquiry $inquiry): bool
    {
        return $user->can('catalog.inquiries.update');
    }

    public function assign(User $user, ProductInquiry $inquiry): bool
    {
        return $user->can('catalog.inquiries.assign');
    }

    public function export(User $user): bool
    {
        return $user->can('catalog.inquiries.export');
    }
}
