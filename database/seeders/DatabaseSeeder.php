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
        $roleNames = ['sales', 'service', 'finance'];

        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@trc.com',
            'password' => 'admin123'
        ]);

        $role = Role::create(['name' => 'admin']);
        $user->assignRole($role);

        foreach ($roleNames as $name) {
            Role::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );
        }

        $this->call([
            StatusSeeder::class,
            // UserSeeder::class,
            // Registration::class,
        ]);
    }
}
