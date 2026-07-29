<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\Role;
use App\Services\AdminSecurityService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    private function roleRecord(): Role
    {
        /** @var Role $role */
        $role = $this->record;

        return $role;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('delete')
                ->label('Excluir')
                ->requiresConfirmation()
                ->color('danger')
                ->action(function (): void {
                    app(AdminSecurityService::class)->deleteRole($this->roleRecord(), auth()->user());
                    $this->redirect(RoleResource::getUrl());
                }),
        ];
    }
}
