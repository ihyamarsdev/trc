<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class Registration extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada minimal 10 user untuk foreign key
        $currentUserCount = User::count();
        if ($currentUserCount < 10) {
            User::factory()->count(10 - $currentUserCount)->create();
        }

        // Ambil 10 user pertama (exclude admin jika ada)
        $userIds = User::where('email', '!=', 'admin@trc.com')->take(10)->pluck('id')->all();

        // Jika masih kurang dari 10, ambil semua user
        if (count($userIds) < 10) {
            $userIds = User::take(10)->pluck('id')->all();
        }

        // Seed registration_data 200 baris untuk 10 user (20 baris per user)
        foreach ($userIds as $userId) {
            $registrations = RegistrationData::factory(20)->state(fn () => [
                'users_id' => $userId,
            ])->create();

            // Buat RegistrationStatus untuk setiap registration
            foreach ($registrations as $registration) {
                RegistrationStatus::create([
                    'registration_id' => $registration->id,
                    'status_id' => $registration->status_id,
                    'user_id' => $userId, // user yang membuat registration
                ]);
            }
        }
    }
}
