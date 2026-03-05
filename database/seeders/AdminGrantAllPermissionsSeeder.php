<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminGrantAllPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guards = Permission::query()
            ->select('guard_name')
            ->distinct()
            ->pluck('guard_name')
            ->filter();

        foreach ($guards as $guard) {
            $adminRole = Role::query()->firstOrCreate([
                'name' => 'admin',
                'guard_name' => $guard,
            ]);

            $permissionNames = Permission::query()
                ->where('guard_name', $guard)
                ->pluck('name')
                ->all();

            $adminRole->syncPermissions($permissionNames);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
