<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Pastikan roles tersedia
        $roleNames = ['admin', 'sales', 'akademik', 'teknisi', 'finance'];

        $roles = Role::whereIn('name', $roleNames)->get()->values();
        $totalUsers = 10;
        $roleCount = $roles->count();

        User::factory($totalUsers)->create()->each(function (User $u, int $idx) use ($roles, $roleCount) {
            $role = $roles[$idx % $roleCount]; // round-robin: 0..4 berulang
            $u->syncRoles([$role->name]);
        });

    }
}
