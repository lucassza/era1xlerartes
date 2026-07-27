<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\LastSuperAdminException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminSecurityService
{
    public function syncUserRoles(User $target, array $roleNames, User $actor): void
    {
        DB::transaction(function () use ($target, $roleNames, $actor): void {
            $target = User::query()->lockForUpdate()->findOrFail($target->getKey());

            $normalizedRoleNames = collect($roleNames)
                ->filter(fn (mixed $roleName): bool => is_string($roleName) && $roleName !== '')
                ->values();

            if ($target->hasRole('Super Admin') && ! $normalizedRoleNames->contains('Super Admin') && $this->countSuperAdmins() <= 1) {
                throw new LastSuperAdminException('Não é permitido remover o último Super Admin.');
            }

            $target->syncRoles($normalizedRoleNames->all());

            activity('admin-security')
                ->causedBy($actor)
                ->performedOn($target)
                ->event('roles_synced')
                ->withProperties([
                    'roles' => $normalizedRoleNames->all(),
                ])
                ->log('Papéis do usuário sincronizados.');
        });
    }

    public function deleteUser(User $target, User $actor): void
    {
        DB::transaction(function () use ($target, $actor): void {
            $target = User::query()->lockForUpdate()->findOrFail($target->getKey());

            if ($target->hasRole('Super Admin') && $this->countSuperAdmins() <= 1) {
                throw new LastSuperAdminException('Não é permitido excluir o último Super Admin.');
            }

            activity('admin-security')
                ->causedBy($actor)
                ->performedOn($target)
                ->event('user_deleted')
                ->log('Usuário administrativo excluído.');

            $target->delete();
        });
    }

    public function deleteRole(Role $role, User $actor): void
    {
        DB::transaction(function () use ($role, $actor): void {
            $role = Role::query()->lockForUpdate()->findOrFail($role->getKey());

            if ($role->name === 'Super Admin') {
                throw new LastSuperAdminException('Não é permitido excluir a role Super Admin.');
            }

            activity('admin-security')
                ->causedBy($actor)
                ->performedOn($role)
                ->event('role_deleted')
                ->log('Role administrativa excluída.');

            $role->delete();
        });
    }

    private function countSuperAdmins(): int
    {
        return User::query()
            ->whereHas('roles', static fn ($query) => $query->where('name', 'Super Admin'))
            ->count();
    }
}
