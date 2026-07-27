<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_updates_are_logged_in_activity_log(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $causer = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($causer);

        $user->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'event' => 'updated',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => User::class,
            'causer_id' => $causer->id,
        ]);

        $this->assertTrue(Activity::query()->exists());
    }
}
