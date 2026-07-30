<?php

declare(strict_types=1);

namespace Tests\Feature\Newsletter;

use App\Enums\NewsletterSubscriberStatus;
use App\Mail\NewsletterConfirmationMail;
use App\Models\BlogSetting;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_creates_pending_subscriber_and_queues_email(): void
    {
        Mail::fake();

        $this->get(route('blog.index'));

        $response = $this->post(route('newsletter.subscribe'), [
            '_token' => csrf_token(),
            'email' => 'newsletter@gmail.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('newsletter_status', 'Enviamos um e-mail de confirmação para sua inscrição.');
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'newsletter@gmail.com',
            'status' => NewsletterSubscriberStatus::Pending->value,
        ]);

        Mail::assertQueued(NewsletterConfirmationMail::class);
    }

    public function test_confirm_route_confirms_subscriber(): void
    {
        $subscriber = NewsletterSubscriber::factory()->create();
        $url = app(NewsletterService::class)->confirmationUrl($subscriber);

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('role="alert"', false);
        $response->assertSee('Tudo pronto');
        $this->assertSame(NewsletterSubscriberStatus::Confirmed, $subscriber->fresh()->status);
        $this->assertNotNull($subscriber->fresh()->confirmed_at);
    }

    public function test_unsubscribe_route_marks_subscriber_as_unsubscribed(): void
    {
        $subscriber = NewsletterSubscriber::factory()->confirmed()->create();
        $url = app(NewsletterService::class)->unsubscribeUrl($subscriber);

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('role="alert"', false);
        $response->assertSee('Preferências atualizadas');
        $this->assertSame(NewsletterSubscriberStatus::Unsubscribed, $subscriber->fresh()->status);
        $this->assertNotNull($subscriber->fresh()->unsubscribed_at);
    }

    public function test_blog_renders_an_accessible_newsletter_form_when_enabled(): void
    {
        BlogSetting::query()->create([
            'site_name' => 'Era1x Ler Artes',
            'newsletter_enabled' => true,
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('id="newsletter-title"', false);
        $response->assertSee('<form method="POST" action="'.route('newsletter.subscribe').'"', false);
        $response->assertSee('for="newsletter-email"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('type="email"', false);
        $response->assertSee('autocomplete="email"', false);
        $response->assertSee('required', false);
        $response->assertSee('Inscrever-se');
        $response->assertDontSee('temporariamente desabilitada');
    }

    public function test_subscription_validation_preserves_the_email_field_and_does_not_queue_mail(): void
    {
        Mail::fake();
        BlogSetting::query()->create([
            'site_name' => 'Era1x Ler Artes',
            'newsletter_enabled' => true,
        ]);

        $response = $this->from(route('home'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('newsletter.subscribe'), [
                '_token' => 'test-token',
                'email' => 'email-invalido',
            ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'email-invalido']);
        Mail::assertNothingQueued();
    }
}
