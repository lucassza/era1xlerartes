<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\BlogSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** @property Schema $form */
class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $title = 'Configurações';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'Configurações';
    }

    protected string $view = 'filament.pages.manage-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->setting()->toArray());
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.manage') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Geral')
                    ->schema([
                        TextInput::make('site_name')->label('Nome do site')->required()->maxLength(255),
                        Textarea::make('site_description')->label('Descrição do site')->rows(3),
                        TextInput::make('contact_email')->label('E-mail de contato')->email(),
                        Toggle::make('newsletter_enabled')->label('Newsletter habilitada')->inline(false),
                    ]),
                Section::make('Engajamento')
                    ->description('Controles de moderação da comunidade. Quando desabilitados, os recursos ficam indisponíveis para o público.')
                    ->schema([
                        Toggle::make('comments_enabled')->label('Comentários em posts (global)')->inline(false),
                        Toggle::make('testimonials_enabled')->label('Depoimentos na área /depoimentos')->inline(false),
                    ]),
                Section::make('SEO padrão')
                    ->schema([
                        TextInput::make('default_meta_title')->label('Meta title padrão')->maxLength(255),
                        TextInput::make('default_meta_description')->label('Meta description padrão')->maxLength(255),
                    ]),
                Section::make('Redes sociais')
                    ->schema([
                        TextInput::make('facebook_url')->label('Facebook')->url(),
                        TextInput::make('instagram_url')->label('Instagram')->url(),
                        TextInput::make('x_url')->label('X / Twitter')->url(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $this->setting()->fill($this->form->getState())->save();

        Notification::make()
            ->title('Configurações salvas com sucesso.')
            ->success()
            ->send();
    }

    private function setting(): BlogSetting
    {
        return BlogSetting::query()->firstOrCreate([], [
            'site_name' => config('app.name', 'Era1x Ler Artes'),
            'site_description' => 'Blog moderno em Laravel.',
            'default_meta_title' => config('app.name', 'Era1x Ler Artes'),
            'default_meta_description' => 'Blog moderno em Laravel.',
            'newsletter_enabled' => true,
        ]);
    }
}
