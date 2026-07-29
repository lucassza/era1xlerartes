<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriberResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_newsletter_subscribers_index(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        NewsletterSubscriber::factory()->create();

        $this->actingAs($editor)->get('/admin/newsletter-subscribers')->assertOk();
    }
}
