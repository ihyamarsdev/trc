<?php

namespace Tests\Feature;

use App\Filament\User\Resources\Admin\AdminResource;
use App\Filament\User\Resources\TimelineResource;
use App\Filament\User\Widgets\CalendarWidget;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TimelineResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_resource_excludes_red_status_records(): void
    {
        // Create user to satisfy users_id NOT NULL constraint
        $user = User::create([
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

    public function test_calendar_widget_excludes_red_status_records(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_cal@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $redStatus = Status::create([
            'name' => 'Red Status',
            'description' => 'A red status description',
            'color' => 'red',
            'order' => 1,
            'category' => 'sales',
        ]);

        $greenStatus = Status::create([
            'name' => 'Green Status',
            'description' => 'A green status description',
            'color' => 'green',
            'order' => 2,
            'category' => 'finance',
        ]);

        $redRecord = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $redStatus->id,
            'status_color' => 'red',
            'implementation_estimate' => now(),
        ]);

        $greenRecord = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $greenStatus->id,
            'status_color' => 'green',
            'implementation_estimate' => now(),
        ]);

        $widget = new CalendarWidget;
        $events = $widget->fetchEvents([
            'start' => now()->subDay()->toIso8601String(),
            'end' => now()->addDay()->toIso8601String(),
        ]);

        $eventIds = array_column($events, 'id');

        $this->assertContains($greenRecord->id, $eventIds);
        $this->assertNotContains($redRecord->id, $eventIds);
    }

    public function test_calendar_widget_provides_edit_url_for_admin_role(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_cal@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $status = Status::create([
            'name' => 'Green Status',
            'description' => 'A green status description',
            'color' => 'green',
            'order' => 1,
            'category' => 'finance',
        ]);

        $record = RegistrationData::factory()->create([
            'users_id' => $admin->id,
            'status_id' => $status->id,
            'status_color' => 'green',
            'schools' => 'SMA Test Admin',
            'implementation_estimate' => now(),
        ]);

        $widget = new CalendarWidget;
        $events = $widget->fetchEvents([
            'start' => now()->subDay()->toIso8601String(),
            'end' => now()->addDay()->toIso8601String(),
        ]);

        $this->assertNotEmpty($events);
        $targetEvent = collect($events)->firstWhere('id', $record->id);
        $this->assertNotNull($targetEvent);

        $expectedEditUrl = AdminResource::getUrl('edit', ['record' => $record], panel: 'user');
        $this->assertSame($expectedEditUrl, $targetEvent['url']);
    }

    public function test_calendar_widget_provides_view_url_for_non_admin(): void
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'regular_cal@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $status = Status::create([
            'name' => 'Green Status 2',
            'description' => 'A green status description 2',
            'color' => 'green',
            'order' => 2,
            'category' => 'finance',
        ]);

        $record = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'status_color' => 'green',
            'schools' => 'SMA Test Regular',
            'implementation_estimate' => now(),
        ]);

        $widget = new CalendarWidget;
        $events = $widget->fetchEvents([
            'start' => now()->subDay()->toIso8601String(),
            'end' => now()->addDay()->toIso8601String(),
        ]);

        $this->assertNotEmpty($events);
        $targetEvent = collect($events)->firstWhere('id', $record->id);
        $this->assertNotNull($targetEvent);

        $expectedViewUrl = TimelineResource::getUrl('view', ['record' => $record], panel: 'user');
        $this->assertSame($expectedViewUrl, $targetEvent['url']);
    }
}
