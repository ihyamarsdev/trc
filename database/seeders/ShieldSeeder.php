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
                'ServiceResource',
                'ActivityResource',
                'FinanceResource',
                'SalesResource',
                'TimelineResource',
                'RekapitulasiServiceResource',
                'AllProgramFinanceResource',
                'RoleResource',
                'UserResource',
            ];

            $permissions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

            foreach ($resources as $resource) {
                foreach ($permissions as $permission) {
                    Permission::firstOrCreate([
                        'name' => ucfirst($permission).':'.$resource,
                        'guard_name' => $guard,
                    ]);
                }
            }

            // Assign permissions to roles
            // Admin role
            $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();
            if ($adminRole) {
                $adminRole->givePermissionTo(
                    Permission::where('guard_name', $guard)->get()
                );
            }

            // Sales role
            $salesRole = Role::where('name', 'sales')->where('guard_name', $guard)->first();
            if ($salesRole) {
                $salesPermissions = [
                    'ViewAny:SalesResource', 'View:SalesResource',
                    'ViewAny:TimelineResource', 'View:TimelineResource', 'Create:TimelineResource', 'Update:TimelineResource',
                ];
                $salesRole->givePermissionTo(
                    Permission::whereIn('name', $salesPermissions)->get()
                );
            }

            // Finance role
            $financeRole = Role::where('name', 'finance')->where('guard_name', $guard)->first();
            if ($financeRole) {
                $financePermissions = [
                    'ViewAny:FinanceResource', 'View:FinanceResource',
                    'ViewAny:RekapitulasiServiceResource', 'View:RekapitulasiServiceResource',
                    'ViewAny:AllProgramFinanceResource', 'View:AllProgramFinanceResource',
                ];
                $financeRole->givePermissionTo(
                    Permission::whereIn('name', $financePermissions)->get()
                );
            }

            // Akademik role
            $akademikRole = Role::where('name', 'akademik')->where('guard_name', $guard)->first();
            if ($akademikRole) {
                $akademikPermissions = [
                    'ViewAny:ServiceResource', 'View:ServiceResource', 'Create:ServiceResource', 'Update:ServiceResource',
                    'ViewAny:ActivityResource', 'View:ActivityResource',
                    'ViewAny:RekapitulasiServiceResource', 'View:RekapitulasiServiceResource',
                ];
                $akademikRole->givePermissionTo(
                    Permission::whereIn('name', $akademikPermissions)->get()
                );
            }

            // Service role
            $serviceRole = Role::where('name', 'service')->where('guard_name', $guard)->first();
            if ($serviceRole) {
                $servicePermissions = [
                    'ViewAny:AdminResource', 'View:AdminResource',
                    'ViewAny:ServiceResource', 'View:ServiceResource',
                    'ViewAny:FinanceResource', 'View:FinanceResource',
                ];
                $serviceRole->givePermissionTo(
                    Permission::whereIn('name', $servicePermissions)->get()
                );
            }

            // Teknisi role
            $teknisiRole = Role::where('name', 'teknisi')->where('guard_name', $guard)->first();
            if ($teknisiRole) {
                $teknisiPermissions = [
                    'ViewAny:ServiceResource', 'View:ServiceResource',
                    'ViewAny:ActivityResource', 'View:ActivityResource', 'Create:ActivityResource', 'Update:ActivityResource',
                ];
                $teknisiRole->givePermissionTo(
                    Permission::whereIn('name', $teknisiPermissions)->get()
                );
            }
        });
    }
}
