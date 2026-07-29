<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_profile(): void
    {
        $response = $this->get(route('site.profile.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_render_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('site.profile.edit'));

        $response->assertOk();
        $response->assertSee('Meu perfil');
        $response->assertSee('Avatar');
        $response->assertSee('Dados pessoais');
        $response->assertSee('Alterar senha');
        $response->assertSee('noindex,nofollow');
    }

    public function test_user_can_update_profile_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Nome anterior',
            'email' => 'anterior@example.com',
            'city' => 'Cidade anterior',
        ]);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->put(route('site.profile.update'), [
            '_token' => 'test-token',
            'name' => 'Maria Silva',
            'email' => ' MARIA@EXAMPLE.COM ',
            'city' => 'Curitiba',
        ]);

        $response->assertRedirect(route('site.profile.edit'));
        $response->assertSessionHas('site_profile_status', 'Dados do perfil atualizados com sucesso.');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'city' => 'Curitiba',
        ]);
    }

    public function test_profile_email_must_be_unique(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->put(route('site.profile.update'), [
            '_token' => 'test-token',
            'name' => $user->name,
            'email' => 'other@example.com',
            'city' => $user->city,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('user@example.com', $user->fresh()->email);
    }

    public function test_changing_email_removes_email_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'verified@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->put(route('site.profile.update'), [
            '_token' => 'test-token',
            'name' => $user->name,
            'email' => 'changed@example.com',
            'city' => $user->city,
        ]);

        $response->assertRedirect(route('site.profile.edit'));
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_profile_honeypot_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'honeypot@example.com']);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->put(route('site.profile.update'), [
            '_token' => 'test-token',
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'website' => 'https://spam.example.com',
        ]);

        $response->assertSessionHasErrors('website');
        $this->assertSame('honeypot@example.com', $user->fresh()->email);
    }

    public function test_avatar_honeypot_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->post(route('site.profile.avatar.update'), [
            '_token' => 'test-token',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
            'website' => 'https://spam.example.com',
        ]);

        $response->assertSessionHasErrors('website');
        $this->assertCount(0, $user->fresh()->getMedia('avatar'));
    }

    public function test_user_can_upload_avatar_to_s3(): void
    {
        Storage::fake('s3');
        config()->set('media-library.disk_name', 's3');
        config()->set('media-library.conversions_disk_name', 's3');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->post(route('site.profile.avatar.update'), [
            '_token' => 'test-token',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
        ]);

        $response->assertRedirect(route('site.profile.edit'));
        $media = $user->fresh()->getFirstMedia('avatar');
        $this->assertNotNull($media);
        $this->assertStringStartsWith("blog/users/{$user->id}/", $media->getPathRelativeToRoot());
        Storage::disk('s3')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_avatar_upload_replaces_existing_single_file(): void
    {
        Storage::fake('s3');
        config()->set('media-library.disk_name', 's3');
        config()->set('media-library.conversions_disk_name', 's3');
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['_token' => 'test-token'])->post(route('site.profile.avatar.update'), [
            '_token' => 'test-token',
            'avatar' => UploadedFile::fake()->image('first.jpg', 300, 300),
        ]);
        $firstMediaId = $user->fresh()->getFirstMedia('avatar')?->getKey();

        $this->actingAs($user)->withSession(['_token' => 'test-token'])->post(route('site.profile.avatar.update'), [
            '_token' => 'test-token',
            'avatar' => UploadedFile::fake()->image('second.png', 300, 300),
        ]);

        $media = $user->fresh()->getMedia('avatar');
        $this->assertCount(1, $media);
        $this->assertNotSame($firstMediaId, $media->first()->getKey());
    }

    public function test_avatar_removal_is_idempotent(): void
    {
        Storage::fake('s3');
        config()->set('media-library.disk_name', 's3');
        config()->set('media-library.conversions_disk_name', 's3');
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['_token' => 'test-token'])->post(route('site.profile.avatar.update'), [
            '_token' => 'test-token',
            'avatar' => UploadedFile::fake()->image('avatar.webp', 300, 300),
        ]);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->delete(route('site.profile.avatar.destroy'), ['_token' => 'test-token']);
        $response->assertRedirect(route('site.profile.edit'));
        $response->assertSessionHas('site_profile_status', 'Avatar removido com sucesso.');
        $this->assertCount(0, $user->fresh()->getMedia('avatar'));

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->delete(route('site.profile.avatar.destroy'), ['_token' => 'test-token']);
        $response->assertRedirect(route('site.profile.edit'));
        $this->assertCount(0, $user->fresh()->getMedia('avatar'));
    }

    public function test_user_can_update_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->put(route('site.profile.password.update'), [
            '_token' => 'test-token',
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('site.profile.edit'));
        $response->assertSessionHas('site_profile_status', 'Senha atualizada com sucesso.');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_update_requires_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->put(route('site.profile.password.update'), [
            '_token' => 'test-token',
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
    }

    public function test_password_update_requires_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->put(route('site.profile.password.update'), [
            '_token' => 'test-token',
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_update_rejects_the_current_password_as_new_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->put(route('site.profile.password.update'), [
            '_token' => 'test-token',
            'current_password' => 'current-password',
            'password' => 'current-password',
            'password_confirmation' => 'current-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
    }
}
