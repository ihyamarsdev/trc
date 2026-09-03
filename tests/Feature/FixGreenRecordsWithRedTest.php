<?php

namespace Tests\Feature;

use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixGreenRecordsWithRedTest extends TestCase
{
    use RefreshDatabase;

    public function test_restores_green_record_with_erroneous_red_log_added_after(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_fix_green@example.com',
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

        $greenStatus = Status::create([
            'id' => 38,
            'name' => 'Invoice',
            'description' => 'Tagihan',
            'color' => 'green',
            'order' => 11,
            'category' => 'finance',
        ]);

        $record = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $redStatus->id,
            'status_color' => 'red',
            'implementation_estimate' => now()->subDays(5),
        ]);

        // Log 1: Red
        RegistrationStatus::create([
            'registration_id' => $record->id,
            'status_id' => $redStatus->id,
            'user_id' => $user->id,
        ]);

        // Log 2: Green
        $greenLog = RegistrationStatus::create([
            'registration_id' => $record->id,
            'status_id' => $greenStatus->id,
            'user_id' => $user->id,
        ]);

        // Log 3: Erroneous Red appended after Green
        $erroneousRedLog = RegistrationStatus::create([
            'registration_id' => $record->id,
            'status_id' => $redStatus->id,
            'user_id' => $user->id,
        ]);

        $this->artisan('app:fix-green-records-with-red')
            ->assertSuccessful();

        $record->refresh();

        $this->assertEquals($greenStatus->id, $record->status_id);
        $this->assertEquals('green', $record->status_color);
        $this->assertEquals('green', $record->latestStatusLog->status->color);
        $this->assertDatabaseMissing('registration_statuses', [
            'id' => $erroneousRedLog->id,
        ]);
        $this->assertDatabaseHas('registration_statuses', [
            'id' => $greenLog->id,
        ]);
    }

    public function test_does_not_affect_normal_green_record(): void
    {
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test_normal_green@example.com',
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
            'implementation_estimate' => now()->subDays(5),
        ]);

        RegistrationStatus::create([
            'registration_id' => $greenRecord->id,
            'status_id' => $greenStatus->id,
            'user_id' => $user->id,
        ]);

        $this->artisan('app:fix-green-records-with-red')
            ->assertSuccessful();

        $greenRecord->refresh();

        $this->assertEquals($greenStatus->id, $greenRecord->status_id);
        $this->assertEquals('green', $greenRecord->status_color);
    }
}
