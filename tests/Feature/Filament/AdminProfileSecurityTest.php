<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Auth\Pages\EditProfile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Storage::fake('s3');
        config()->set('media-library.disk_name', 's3');
        config()->set('media-library.conversions_disk_name', 's3');

        return User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    public function test_admin_can_upload_avatar_from_profile(): void
    {
        $user = $this->admin();

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->set('data.avatar', UploadedFile::fake()->image('avatar.png', 300, 300))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertCount(1, $user->fresh()->getMedia('avatar'));
    }

    public function test_saving_profile_without_new_avatar_keeps_existing_avatar(): void
    {
        $user = $this->admin();
        $user->addMedia(UploadedFile::fake()->image('first.png', 300, 300))->toMediaCollection('avatar');
        $mediaId = $user->fresh()->getFirstMedia('avatar')->getKey();

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->set('data.name', 'Novo Nome')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($mediaId, $user->fresh()->getFirstMedia('avatar')?->getKey());
        $this->assertSame('Novo Nome', $user->fresh()->name);
    }

    public function test_admin_can_remove_current_avatar(): void
    {
        $user = $this->admin();
        $user->addMedia(UploadedFile::fake()->image('first.png', 300, 300))->toMediaCollection('avatar');

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->set('data.removeAvatar', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertCount(0, $user->fresh()->getMedia('avatar'));
    }

    public function test_panel_offers_app_two_factor_authentication_and_unsaved_changes_alerts(): void
    {
        $user = $this->admin();

        $panel = Filament::getPanel('admin');
        $this->assertTrue($panel->hasMultiFactorAuthentication());
        $this->assertTrue($panel->hasUnsavedChangesAlerts());

        $this->actingAs($user)->get('/admin/profile')->assertOk()->assertSee('Autenticação');
    }

    public function test_two_factor_secret_is_encrypted_and_hidden(): void
    {
        $user = $this->admin();
        $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

        $this->assertSame('JBSWY3DPEHPK3PXP', $user->fresh()->getAppAuthenticationSecret());
        $this->assertNotSame(
            'JBSWY3DPEHPK3PXP',
            DB::table('users')->where('id', $user->id)->value('app_authentication_secret'),
        );
        $this->assertArrayNotHasKey('app_authentication_secret', $user->fresh()->toArray());
    }
}
