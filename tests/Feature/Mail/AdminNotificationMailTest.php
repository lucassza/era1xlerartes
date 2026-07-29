<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Enums\TestimonialStatus;
use App\Mail\NewTestimonialNotification;
use App\Models\BlogSetting;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminNotificationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_testimonial_queues_email_to_admin_with_approve_permission(): void
    {
        Mail::fake();
        $this->seed(RolesAndPermissionsSeeder::class);
        BlogSetting::query()->create(BlogSetting::defaults());

        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $admin->assignRole('Super Admin');

        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        Testimonial::factory()->forUser($user)->pending()->create([
            'status' => TestimonialStatus::Pending,
        ]);

        Mail::assertQueued(NewTestimonialNotification::class, function ($mail) use ($admin): bool {
            return $mail->hasTo($admin->email);
        });
    }

    public function test_new_testimonial_notification_renders_view(): void
    {
        Mail::fake();
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $admin->assignRole('Super Admin');

        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        $testimonial = Testimonial::factory()->forUser($user)->pending()->create();

        $mail = new NewTestimonialNotification($testimonial, $admin);
        $rendered = $mail->content()->with['testimonial'] ?? null;

        $this->assertSame($testimonial->id, $rendered?->id);
    }
}
