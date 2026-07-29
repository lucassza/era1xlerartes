<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserType;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\AdminSecurityService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected array $rolesToSync = [];

    private function userRecord(): User
    {
        /** @var User $user */
        $user = $this->record;

        return $user;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->userRecord()->roles()->pluck('name')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->rolesToSync = $data['roles'] ?? [];
        unset($data['roles']);
        unset($data['email_verified']);

        return $data;
    }

    protected function afterSave(): void
    {
        app(AdminSecurityService::class)->syncUserRoles($this->userRecord(), $this->rolesToSync, auth()->user());
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (auth()->user()?->can('ban', $this->userRecord())) {
            $actions[] = Action::make('toggleBan')
                ->label(fn (): string => $this->userRecord()->isBanned() ? 'Desbloquear' : 'Bloquear')
                ->color(fn (): string => $this->userRecord()->isBanned() ? 'success' : 'warning')
                ->requiresConfirmation()
                ->action(function (): void {
                    $user = $this->userRecord();
                    $user->banned_at = $user->isBanned() ? null : now();
                    $user->save();
                    $this->refreshFormData(['banned_at']);
                });
        }

        if (auth()->user()?->can('promote', $this->userRecord())) {
            $actions[] = Action::make('toggleType')
                ->label(fn (): string => $this->userRecord()->isAdmin() ? 'Rebaixar para usuário do site' : 'Promover para administrador')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (): void {
                    $user = $this->userRecord();
                    $user->type = $user->isAdmin() ? UserType::SiteUser : UserType::Admin;
                    $user->save();
                    $this->refreshFormData(['type']);
                });
        }

        $actions[] = Action::make('delete')
            ->label('Excluir')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (): void {
                app(AdminSecurityService::class)->deleteUser($this->userRecord(), auth()->user());
                $this->redirect(UserResource::getUrl());
            });

        return $actions;
    }
}
