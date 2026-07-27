<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'admin.access',
            'users.view',
            'users.viewAny',
            'users.create',
            'users.update',
            'users.delete',
            'users.ban',
            'users.promote',
            'roles.manage',
            'permissions.manage',
            'settings.manage',
            'newsletter.manage',
            'authors.viewAny',
            'authors.view',
            'authors.create',
            'authors.update',
            'authors.delete',
            'categories.viewAny',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'tags.viewAny',
            'tags.view',
            'tags.create',
            'tags.update',
            'tags.delete',
            'posts.viewAny',
            'posts.view',
            'posts.create',
            'posts.update',
            'posts.delete',
            'posts.publish',
            'testimonials.viewAny',
            'testimonials.view',
            'testimonials.update',
            'testimonials.delete',
            'testimonials.approve',
            'testimonials.reject',
            'comments.viewAny',
            'comments.view',
            'comments.update',
            'comments.delete',
            'comments.approve',
            'comments.reject',
            'audit.read',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::query()->firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);
        $editor = Role::query()->firstOrCreate([
            'name' => 'Editor',
            'guard_name' => 'web',
        ]);
        $author = Role::query()->firstOrCreate([
            'name' => 'Author',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions(Permission::all());
        $editor->syncPermissions([
            'admin.access',
            'users.view',
            'users.ban',
            'authors.viewAny',
            'authors.view',
            'authors.create',
            'authors.update',
            'authors.delete',
            'categories.viewAny',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'tags.viewAny',
            'tags.view',
            'tags.create',
            'tags.update',
            'tags.delete',
            'posts.viewAny',
            'posts.view',
            'posts.create',
            'posts.update',
            'posts.delete',
            'posts.publish',
            'testimonials.viewAny',
            'testimonials.view',
            'testimonials.update',
            'testimonials.delete',
            'testimonials.approve',
            'testimonials.reject',
            'comments.viewAny',
            'comments.view',
            'comments.update',
            'comments.delete',
            'comments.approve',
            'comments.reject',
            'audit.read',
            'newsletter.manage',
        ]);
        $author->syncPermissions([
            'admin.access',
            'authors.viewAny',
            'authors.view',
            'authors.update',
            'categories.viewAny',
            'categories.view',
            'tags.viewAny',
            'tags.view',
            'posts.viewAny',
            'posts.view',
            'posts.create',
            'posts.update',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'email_verified_at' => now(),
                'type' => UserType::Admin->value,
            ],
        );

        if (! $user->hasRole($superAdmin)) {
            $user->assignRole($superAdmin);
        }

        if (! $user->type) {
            $user->type = UserType::Admin->value;
            $user->save();
        }
    }
}
