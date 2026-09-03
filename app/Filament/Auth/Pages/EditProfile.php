<?php

declare(strict_types=1);

namespace App\Filament\Auth\Pages;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Avatar')
                    ->schema([
                        FileUpload::make('avatar')
                            ->disk(config('media-library.disk_name'))
                            ->image()
                            ->maxSize(2048)
                            ->rule('mimes:jpg,jpeg,png,webp')
                            ->imageAspectRatio('1:1')
                            ->visibility('private')
                            ->storeFiles(false),
                    ]),
                Section::make('Dados')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->live(debounce: 500),
                        TextInput::make('city')
                            ->label('Cidade')
                            ->maxLength(120),
                    ]),
                Section::make('Senha')
                    ->schema([
                        TextInput::make('currentPassword')
                            ->label('Senha atual')
                            ->helperText('Por segurança, confirme sua senha para continuar.')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required()
                            ->currentPassword(guard: Filament::getAuthGuard())
                            ->visible(fn (Get $get): bool => filled($get('password')) || $get('email') !== $this->getUser()->getAttributeValue('email'))
                            ->dehydrated(false),
                        TextInput::make('password')
                            ->label('Nova senha')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->rule(Password::default())
                            ->showAllValidationMessages()
                            ->autocomplete('new-password')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state): string => Hash::make((string) $state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation'),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirmar nova senha')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->autocomplete('new-password')
                            ->required()
                            ->visible(fn (Get $get): bool => filled($get('password')))
                            ->dehydrated(false),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->getUser();

        $user->clearMediaCollection('avatar');

        if (filled($data['avatar'] ?? null)) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        unset($data['avatar']);

        return $data;
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('back')
            ->label('Cancelar')
            ->url(fn (): string => Filament::getUrl())
            ->color('gray');
    }
}
