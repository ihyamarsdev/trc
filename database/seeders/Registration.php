<?php

namespace Database\Seeders;

use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

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

        $yearsList = ['2024', '2025', '2026'];

        foreach ($userIds as $userId) {
            foreach ($yearsList as $year) {
                // Create 7 records for each year per user (total 21 records per user)
                $registrations = RegistrationData::factory(7)->state(function () use ($userId, $year) {
                    $dateRegister = now()->setYear((int) $year)->subDays(rand(1, 300));
                    $implementationEstimate = (clone $dateRegister)->addDays(rand(7, 60));

                    return [
                        'users_id' => $userId,
                        'years' => $year,
                        'date_register' => $dateRegister,
                        'implementation_estimate' => $implementationEstimate,
                    ];
                })->create();

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
}
