<?php

namespace Tests\Feature;

use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckOverdueEstimationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_yellow_status_reverts_to_red_when_implementation_estimate_is_past(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_overdue@example.com',
            'password' => bcrypt('password'),
        ]);

        $redStatus = Status::create([
            'id' => 28,
            'name' => 'Aktifitas Marketing',
            'description' => 'Marketing',
            'color' => 'red',
            'order' => 1,
            'category' => 'sales',
        ]);

        $yellowStatus = Status::create([
            'id' => 29,
            'name' => 'Registrasi / Input Data Sekolah',
            'description' => 'Input data',
            'color' => 'yellow',
            'order' => 2,
            'category' => 'sales',
        ]);

        // Create with future estimate first so it is saved in yellow status
        $overdueRecord = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $yellowStatus->id,
            'status_color' => 'yellow',
            'implementation_estimate' => now()->addDays(2),
        ]);

        RegistrationStatus::create([
            'registration_id' => $overdueRecord->id,
            'status_id' => $yellowStatus->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($yellowStatus->id, $overdueRecord->status_id);

        // Simulate time passing (implementation estimate is now past)
        RegistrationData::where('id', $overdueRecord->id)->update([
            'implementation_estimate' => now()->subDays(2),
        ]);

        $this->artisan('app:check-overdue-estimations')
            ->assertSuccessful();

        $overdueRecord->refresh();

        $this->assertEquals($redStatus->id, $overdueRecord->status_id);
        $this->assertEquals('red', $overdueRecord->status_color);
        $this->assertEquals('red', $overdueRecord->latestStatusLog->status->color);
    }

    public function test_yellow_status_remains_yellow_when_implementation_estimate_is_in_future(): void
    {
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test_future@example.com',
            'password' => bcrypt('password'),
        ]);

        Status::create([
            'id' => 28,
            'name' => 'Aktifitas Marketing',
            'description' => 'Marketing',
            'color' => 'red',
            'order' => 1,
            'category' => 'sales',
        ]);

        $yellowStatus = Status::create([
            'id' => 29,
            'name' => 'Registrasi / Input Data Sekolah',
            'description' => 'Input data',
            'color' => 'yellow',
            'order' => 2,
            'category' => 'sales',
        ]);

        $futureRecord = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $yellowStatus->id,
            'status_color' => 'yellow',
            'implementation_estimate' => now()->addDays(2),
        ]);

        RegistrationStatus::create([
            'registration_id' => $futureRecord->id,
            'status_id' => $yellowStatus->id,
            'user_id' => $user->id,
        ]);

        RegistrationData::updateOverdueYellowStatuses();

        $futureRecord->refresh();

        $this->assertEquals($yellowStatus->id, $futureRecord->status_id);
        $this->assertEquals('yellow', $futureRecord->status_color);
    }

    public function test_saving_model_automatically_converts_yellow_status_to_red_if_past_estimate(): void
    {
        $user = User::create([
            'name' => 'Test User 3',
            'email' => 'test_saving@example.com',
            'password' => bcrypt('password'),
        ]);

        $redStatus = Status::create([
            'id' => 28,
            'name' => 'Aktifitas Marketing',
            'description' => 'Marketing',
            'color' => 'red',
            'order' => 1,
            'category' => 'sales',
        ]);

        $yellowStatus = Status::create([
            'id' => 29,
            'name' => 'Registrasi / Input Data Sekolah',
            'description' => 'Input data',
            'color' => 'yellow',
            'order' => 2,
            'category' => 'sales',
        ]);

        $record = RegistrationData::factory()->make([
            'users_id' => $user->id,
            'status_id' => $yellowStatus->id,
            'status_color' => 'yellow',
            'implementation_estimate' => now()->subHours(5),
        ]);
        $record->save();

        $this->assertEquals($redStatus->id, $record->status_id);
        $this->assertEquals('red', $record->status_color);
    }

    public function test_green_status_does_not_revert_to_red_even_if_past_estimate(): void
    {
        $user = User::create([
            'name' => 'Test User 4',
            'email' => 'test_green@example.com',
            'password' => bcrypt('password'),
        ]);

        Status::create([
            'id' => 28,
            'name' => 'Aktifitas Marketing',
            'description' => 'Marketing',
            'color' => 'red',
            'order' => 1,
            'category' => 'sales',
        ]);

        $greenStatus = Status::create([
            'id' => 38,
            'name' => 'Invoice',
            'description' => 'Tagihan',
            'color' => 'green',
            'order' => 11,
            'category' => 'finance',
        ]);

        $greenRecord = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $greenStatus->id,
            'status_color' => 'green',
            'implementation_estimate' => now()->subDays(10),
        ]);

        RegistrationStatus::create([
            'registration_id' => $greenRecord->id,
            'status_id' => $greenStatus->id,
            'user_id' => $user->id,
        ]);

        RegistrationData::updateOverdueYellowStatuses();

        $greenRecord->refresh();

        $this->assertEquals($greenStatus->id, $greenRecord->status_id);
        $this->assertEquals('green', $greenRecord->status_color);
        $this->assertEquals('green', $greenRecord->latestStatusLog->status->color);
    }
}
