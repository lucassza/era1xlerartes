<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteLoginRequest;
use App\Http\Requests\SiteRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SiteAuthController extends Controller
{
    public function createLogin(): View
    {
        return view('auth.site.login', [
            'meta' => [
                'title' => 'Entrar — TDB Mix Blog',
                'description' => 'Acesse sua conta para comentar e enviar depoimentos.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function storeLogin(SiteLoginRequest $request): RedirectResponse
    {
        $key = sprintf('site-login:%s|%s', $request->ip(), (string) $request->input('email'));

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Muitas tentativas de login. Aguarde alguns minutos.',
            ]);
        }

        $credentials = $request->only(['email', 'password']);
        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $user = $request->user();

        if ($user instanceof User && $user->isBanned()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Sua conta está bloqueada. Procure o administrador.',
            ]);
        }

        return redirect()->intended(route('home'))
            ->with('site_auth_status', 'Bem-vindo de volta!');
    }

    public function createRegister(): View
    {
        return view('auth.site.register', [
            'meta' => [
                'title' => 'Criar conta — TDB Mix Blog',
                'description' => 'Cadastre-se para comentar nos posts e enviar depoimentos.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function storeRegister(SiteRegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'city' => $data['city'] ?? null,
            'password' => Hash::make($data['password']),
            'type' => UserType::SiteUser->value,
            'email_verified_at' => null,
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('site_auth_status', 'Enviamos um e-mail de confirmação para você.');
    }

    public function destroyLogout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('site_auth_status', 'Você saiu da sua conta.');
    }
}
