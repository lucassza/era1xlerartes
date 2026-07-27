<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\CatalogSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * @property Schema $form
 */
class ManageCatalogSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configurações do catálogo';

    protected static ?string $title = 'Configurações do catálogo';

    protected static ?string $slug = 'catalog-settings';

    protected static ?int $navigationSort = 90;

    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }

    protected string $view = 'filament.pages.manage-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(CatalogSetting::current()->toArray());
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('catalog.settings.manage') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Apresentação')
                    ->description('Texto e itens exibidos na home do catálogo.')
                    ->schema([
                        TextInput::make('title')->label('Título do catálogo')->required()->maxLength(255),
                        Textarea::make('intro_text')->label('Texto introdutório da home')->rows(3),
                        TextInput::make('per_page')
                            ->label('Produtos por página')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->helperText('Quantos produtos aparecem por página na listagem.'),
                        TextInput::make('default_sort')
                            ->label('Ordenação padrão')
                            ->placeholder('recent')
                            ->helperText('recent|name_asc|name_desc|price_asc|price_desc|featured|sort_order')
                            ->maxLength(30),
                        Toggle::make('show_unavailable')
                            ->label('Exibir produtos indisponíveis')
                            ->inline(false),
                    ]),
                Section::make('Consultas')
                    ->description('Configurações do botão "Consulte" e do recebimento de consultas.')
                    ->schema([
                        TextInput::make('inquiry_button_text')
                            ->label('Texto padrão do botão')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('inquiry_recipients')
                            ->label('E-mails que recebem notificações')
                            ->placeholder('comercial@exemplo.com, vendas@exemplo.com')
                            ->helperText('Separe múltiplos endereços por vírgula.')
                            ->rows(2),
                        Textarea::make('inquiry_confirmation_message')
                            ->label('Mensagem de confirmação exibida ao visitante')
                            ->rows(3),
                        TextInput::make('commercial_phone')
                            ->label('Telefone comercial')
                            ->maxLength(30),
                        TextInput::make('commercial_whatsapp')
                            ->label('WhatsApp comercial')
                            ->placeholder('+55 11 99999 9999')
                            ->maxLength(30),
                        Toggle::make('captcha_enabled')
                            ->label('Habilitar CAPTCHA no formulário')
                            ->inline(false)
                            ->helperText('Flag preparada para integração futura com um provedor de CAPTCHA.'),
                    ]),
                Section::make('SEO')
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('Meta title da home')
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->label('Meta description da home')
                            ->rows(3)
                            ->maxLength(500),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (isset($data['inquiry_recipients'])) {
            $recipients = collect(preg_split('/[\s,]+/', (string) $data['inquiry_recipients']) ?: [])
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
            $data['inquiry_recipients'] = $recipients ?: null;
        }

        CatalogSetting::current()->fill($data)->save();

        Notification::make()
            ->title('Configurações do catálogo salvas.')
            ->success()
            ->send();
    }
}
