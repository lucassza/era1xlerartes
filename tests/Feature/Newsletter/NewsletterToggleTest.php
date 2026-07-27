<?php

declare(strict_types=1);

namespace Tests\Feature\Newsletter;

use App\Models\BlogSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_hides_newsletter_form_when_disabled(): void
    {
        BlogSetting::query()->create([
            'site_name' => 'TDB Mix Blog',
            'newsletter_enabled' => false,
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertDontSee('name="email"', false);
        $response->assertDontSee('Inscrever-se');
        $response->assertSee('newsletter está temporariamente desabilitada');
    }

    public function test_subscribe_does_not_create_subscriber_when_newsletter_is_disabled(): void
    {
        BlogSetting::query()->create([
            'site_name' => 'TDB Mix Blog',
            'newsletter_enabled' => false,
        ]);

        $this->get(route('blog.index'));

        $response = $this->post(route('newsletter.subscribe'), [
            '_token' => csrf_token(),
            'email' => 'newsletter@gmail.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('newsletter_subscribers', [
            'email' => 'newsletter@gmail.com',
        ]);
    }
}
