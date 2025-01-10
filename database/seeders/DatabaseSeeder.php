<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@trc.com',
            'password' => 'admin123'
        ]);

        $role = Role::create(['name' => 'admin']);
        $user->assignRole($role);
    }
}
