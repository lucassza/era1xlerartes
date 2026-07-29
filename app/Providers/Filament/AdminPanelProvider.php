<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Auth\Pages\EditProfile;
use App\Filament\Pages\Dashboard;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->favicon(asset('favicon.svg'))
            ->brandName('Era1x Ler Artes')
            ->brandLogo(asset('images/brand/logo.jpeg'))
            ->brandLogoHeight('3rem')
            ->darkMode(false)
            ->colors([
                'primary' => Color::hex('#650010'),
                'secondary' => Color::hex('#E95D78'),
                'accent' => Color::hex('#F7B8C6'),
                'success' => Color::hex('#4CAF50'),
                'warning' => Color::hex('#F4B400'),
                'danger' => Color::hex('#D32F2F'),
                'info' => Color::hex('#4A90E2'),
                'gray' => Color::hex('#666666'),
            ])
            ->viteTheme('resources/css/filament/theme.css')
            ->login()
            ->passwordReset()
            ->profile(page: EditProfile::class, isSimple: false)
            ->navigationGroups([
                NavigationGroup::make('Conteúdo')
                    ->collapsible(),
                NavigationGroup::make('Catálogo')
                    ->collapsible(),
                NavigationGroup::make('Comunidade')
                    ->collapsible(),
                NavigationGroup::make('Configurações')
                    ->collapsible(),
            ])
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action
                    ->label('Meu perfil'),
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): HtmlString => new HtmlString(
                    '<div class="px-3 py-2 text-xs text-gray-500">Logado como<br><strong class="text-sm text-gray-700">'.e(Auth::user()->name ?? '').'</strong></div>'
                ),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string => view('filament.auth.partials.brand-header')->render(),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => view('filament.auth.partials.brand-footer')->render(),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_BEFORE,
                fn (): string => view('filament.auth.partials.brand-header', [
                    'title' => 'Recuperar acesso',
                    'subtitle' => 'Informe seu e-mail para receber o link de redefinição.',
                ])->render(),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_RESET_FORM_BEFORE,
                fn (): string => view('filament.auth.partials.brand-header', [
                    'title' => 'Definir nova senha',
                    'subtitle' => 'Escolha uma nova senha forte para sua conta.',
                ])->render(),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
