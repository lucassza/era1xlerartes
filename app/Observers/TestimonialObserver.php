<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TestimonialStatus;
use App\Mail\NewTestimonialNotification;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TestimonialObserver
{
    public function created(Testimonial $testimonial): void
    {
        if ($testimonial->status !== TestimonialStatus::Pending) {
            return;
        }

        $recipients = User::query()
            ->whereNull('banned_at')
            ->where(function ($query): void {
                $query->where('type', 'admin')
                    ->where(function ($q): void {
                        $q->whereHas('roles.permissions', fn ($permission) => $permission->where('name', 'testimonials.approve'));
                    });
            })
            ->get();

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->queue(new NewTestimonialNotification($testimonial, $recipient));
        }
    }
}
