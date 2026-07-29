<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileAvatarRequest;
use App\Http\Requests\UpdateProfilePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $this->authenticatedUser($request);

        return view('profile.edit', [
            'user' => $user,
            'avatarUrl' => $user->getFirstMediaUrl('avatar', 'thumb-300') ?: $user->getFirstMediaUrl('avatar'),
            'meta' => [
                'title' => 'Meu perfil — Era1x Ler Artes',
                'description' => 'Gerencie seus dados, avatar e senha no Era1x Ler Artes.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $data = $request->validated();
        $emailChanged = $data['email'] !== $user->email;

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'city' => $data['city'] ?? null,
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('site.profile.edit')
            ->with('site_profile_status', 'Dados do perfil atualizados com sucesso.');
    }

    public function updateAvatar(UpdateProfileAvatarRequest $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request);

        $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');

        return redirect()->route('site.profile.edit')
            ->with('site_profile_status', 'Avatar atualizado com sucesso.');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $user->clearMediaCollection('avatar');

        return redirect()->route('site.profile.edit')
            ->with('site_profile_status', 'Avatar removido com sucesso.');
    }

    public function updatePassword(UpdateProfilePasswordRequest $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $user->update([
            'password' => $request->validated()['password'],
        ]);
        $request->session()->regenerate();

        return redirect()->route('site.profile.edit')
            ->with('site_profile_status', 'Senha atualizada com sucesso.');
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user('web');
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
