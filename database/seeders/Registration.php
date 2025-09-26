<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use App\Models\RegistrationData;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class Registration extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada user untuk foreign key
        if (User::count() === 0) {
            User::factory()->count(30)->create();
        }

        // Ambil semua user id yg mau dipakai (mis. exclude admin jika perlu)
        $userIds = User::where('email', '!=', 'admin@trc.com')->pluck('id')->all();
        // atau kalau mau termasuk admin: $userIds = User::pluck('id')->all();

        // Seed registration_data 100 baris, setiap baris users_id diacak dari $userIds
        RegistrationData::factory(100)->state(fn () => [
            'users_id' => Arr::random($userIds),
        ])
        ->create();
    }
}
