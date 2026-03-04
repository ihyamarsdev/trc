<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Reset cached roles and permissions
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // Get guard name
            $guard = Utils::getFilamentAuthGuard();

            // Create roles
            $roles = [
                'super_admin',
                'admin',
                'sales',
                'finance',
                'akademik',
                'teknisi',
                'service',
            ];

            foreach ($roles as $role) {
                Role::firstOrCreate([
                    'name' => $role,
                    'guard_name' => $guard,
                ]);
            }

            // Create permissions for resources
            $resources = [
                'AdminResource',
                'AcademicResource',
                'ActivityResource',
                'FinanceResource',
                'SalesResource',
                'TimelineResource',
                'RekapitulasiServiceResource',
                'AllProgramFinanceResource',
            ];

            $permissions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

            foreach ($resources as $resource) {
                foreach ($permissions as $permission) {
                    Permission::firstOrCreate([
                        'name' => $resource.'_'.$permission,
                        'guard_name' => $guard,
                    ]);
                }
            }

            // Assign permissions to roles
            $superAdminRole = Role::where('name', 'super_admin')->where('guard_name', $guard)->first();
            if ($superAdminRole) {
                $superAdminRole->givePermissionTo(
                    Permission::where('guard_name', $guard)->get()
                );
            }

            // Admin role
            $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();
            if ($adminRole) {
                $adminRole->givePermissionTo(
                    Permission::where('guard_name', $guard)
                        ->where('name', 'not like', '%forceDelete%')
                        ->get()
                );
            }

            // Sales role
            $salesRole = Role::where('name', 'sales')->where('guard_name', $guard)->first();
            if ($salesRole) {
                $salesPermissions = [
                    'SalesResource_viewAny', 'SalesResource_view',
                    'TimelineResource_viewAny', 'TimelineResource_view', 'TimelineResource_create', 'TimelineResource_update',
                ];
                $salesRole->givePermissionTo(
                    Permission::whereIn('name', $salesPermissions)->get()
                );
            }

            // Finance role
            $financeRole = Role::where('name', 'finance')->where('guard_name', $guard)->first();
            if ($financeRole) {
                $financePermissions = [
                    'FinanceResource_viewAny', 'FinanceResource_view',
                    'RekapitulasiServiceResource_viewAny', 'RekapitulasiServiceResource_view',
                    'AllProgramFinanceResource_viewAny', 'AllProgramFinanceResource_view',
                ];
                $financeRole->givePermissionTo(
                    Permission::whereIn('name', $financePermissions)->get()
                );
            }

            // Akademik role
            $akademikRole = Role::where('name', 'akademik')->where('guard_name', $guard)->first();
            if ($akademikRole) {
                $akademikPermissions = [
                    'AcademicResource_viewAny', 'AcademicResource_view', 'AcademicResource_create', 'AcademicResource_update',
                    'ActivityResource_viewAny', 'ActivityResource_view',
                    'RekapitulasiServiceResource_viewAny', 'RekapitulasiServiceResource_view',
                ];
                $akademikRole->givePermissionTo(
                    Permission::whereIn('name', $akademikPermissions)->get()
                );
            }

            // Service role
            $serviceRole = Role::where('name', 'service')->where('guard_name', $guard)->first();
            if ($serviceRole) {
                $servicePermissions = [
                    'AdminResource_viewAny', 'AdminResource_view',
                    'AcademicResource_viewAny', 'AcademicResource_view',
                    'FinanceResource_viewAny', 'FinanceResource_view',
                ];
                $serviceRole->givePermissionTo(
                    Permission::whereIn('name', $servicePermissions)->get()
                );
            }

            // Teknisi role
            $teknisiRole = Role::where('name', 'teknisi')->where('guard_name', $guard)->first();
            if ($teknisiRole) {
                $teknisiPermissions = [
                    'AcademicResource_viewAny', 'AcademicResource_view',
                    'ActivityResource_viewAny', 'ActivityResource_view', 'ActivityResource_create', 'ActivityResource_update',
                ];
                $teknisiRole->givePermissionTo(
                    Permission::whereIn('name', $teknisiPermissions)->get()
                );
            }
        });
    }
}
