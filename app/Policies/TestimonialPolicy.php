<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('testimonials.viewAny');
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        return $user->can('testimonials.view');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->can('testimonials.update');
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $user->can('testimonials.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('testimonials.delete');
    }

    public function approve(User $user, Testimonial $testimonial): bool
    {
        return $user->can('testimonials.approve');
    }

    public function reject(User $user, Testimonial $testimonial): bool
    {
        return $user->can('testimonials.reject');
    }

    public function submit(User $user): bool
    {
        return ! $user->isBanned();
    }
}
