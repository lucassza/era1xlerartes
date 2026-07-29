<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SiteRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_verification_email_is_sent(): void
    {
        Event::fake();
        Notification::fake();

        $response = $this->from(route('site.register'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('site.register.store'), [
                '_token' => 'test-token',
                'name' => 'Maria Silva',
                'email' => 'maria@example.com',
                'city' => 'Curitiba',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'terms' => '1',
            ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'name' => 'Maria Silva',
            'city' => 'Curitiba',
        ]);

        Event::assertDispatched(Registered::class);
    }

    public function test_password_must_be_confirmed(): void
    {
        $response = $this->from(route('site.register'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('site.register.store'), [
                '_token' => 'test-token',
                'name' => 'Sem Senha',
                'email' => 'sem@example.com',
                'password' => 'password123',
                'password_confirmation' => 'diferente',
                'terms' => '1',
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'sem@example.com']);
    }

    public function test_terms_must_be_accepted(): void
    {
        $response = $this->from(route('site.register'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('site.register.store'), [
                '_token' => 'test-token',
                'name' => 'Sem Termos',
                'email' => 'semtermos@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertSessionHasErrors('terms');
    }

    public function test_email_verification_route_confirms_user(): void
    {
        $user = User::factory()->siteUser()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())],
        );

        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect(route('home'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_website_honeypot_blocks_registration(): void
    {
        $response = $this->from(route('site.register'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('site.register.store'), [
                '_token' => 'test-token',
                'name' => 'Bot Malicioso',
                'email' => 'bot@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'terms' => '1',
                'website' => 'http://spam.example.com',
            ]);

        $response->assertSessionHasErrors('website');
        $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);
    }
}
