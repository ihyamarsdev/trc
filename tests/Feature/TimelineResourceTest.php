<?php

namespace Tests\Feature;

use App\Filament\User\Resources\TimelineResource;
use App\Models\RegistrationData;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_resource_excludes_red_status_records(): void
    {
        // Create user to satisfy users_id NOT NULL constraint
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // 1. Create a status with color red
        $redStatus = Status::create([
            'name' => 'Red Status',
            'description' => 'A red status description',
            'color' => 'red',
            'order' => 1,
            'category' => 'sales',
        ]);

        // 2. Create a status with color green
        $greenStatus = Status::create([
            'name' => 'Green Status',
            'description' => 'A green status description',
            'color' => 'green',
            'order' => 2,
            'category' => 'finance',
        ]);

        // 3. Create registration records
        $redRecord = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $redStatus->id,
            'status_color' => 'red',
        ]);

        $greenRecord = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $greenStatus->id,
            'status_color' => 'green',
        ]);

        // 4. Query using TimelineResource's eloquent query
        $results = TimelineResource::getEloquentQuery()->get();

        // 5. Assertions
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($greenRecord));
        $this->assertFalse($results->contains($redRecord));
    }
}
