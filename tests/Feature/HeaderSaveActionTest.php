<?php

namespace Tests\Feature;

use App\Filament\User\Resources\Academic\AcademicResource\Pages\EditAcademic;
use App\Filament\User\Resources\Admin\AdminResource\Pages\CreateAdmin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\EditAdmin;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages\CreateSales;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages\EditSales;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeaderSaveActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_save_action_updates_record_in_edit_page(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::create(['name' => 'admin']);

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $status = Status::create([
            'name' => 'Status Initial',
            'description' => 'Desc',
            'color' => 'yellow',
            'order' => 2,
            'category' => 'sales',
        ]);

        $record = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'schools' => 'Original School Name',
            'date_register' => '2026-01-01',
        ]);

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->fillForm([
                'schools' => 'Updated School Name Via Header Action',
            ])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(RegistrationData::class, [
            'id' => $record->id,
            'schools' => 'Updated School Name Via Header Action',
        ]);
    }

    public function test_header_create_action_creates_record_in_create_page(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::create(['name' => 'admin']);

        $user = User::create([
            'name' => 'Admin User 2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $status = Status::create([
            'id' => 28,
            'name' => 'Status 1',
            'description' => 'Desc 1',
            'color' => 'red',
            'order' => 1,
            'category' => 'sales',
        ]);

        Livewire::test(CreateAdmin::class)
            ->fillForm([
                'type' => 'anbk',
                'schools' => 'Newly Created School Via Header Action',
                'date_register' => '2026-03-01',
                'status_id' => $status->id,
            ])
            ->callAction('createHeader')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(RegistrationData::class, [
            'schools' => 'Newly Created School Via Header Action',
        ]);
    }

    public function test_header_save_action_works_with_confirmation_on_edit_sales(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::create(['name' => 'sales']);

        $user = User::create([
            'name' => 'Sales User',
            'email' => 'sales@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('sales');

        $this->actingAs($user);

        $status = Status::create([
            'name' => 'Sales Status',
            'description' => 'Sales Desc',
            'color' => 'yellow',
            'order' => 2,
            'category' => 'sales',
        ]);

        $record = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'schools' => 'Sales School Name',
            'date_register' => '2026-01-01',
        ]);

        Livewire::test(EditSales::class, ['record' => $record->getKey()])
            ->fillForm([
                'schools' => 'Updated Sales School Via Header',
            ])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(RegistrationData::class, [
            'id' => $record->id,
            'schools' => 'UPDATED SALES SCHOOL VIA HEADER',
        ]);
    }

    public function test_header_save_action_works_on_edit_academic(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::create(['name' => 'service']);

        $user = User::create([
            'name' => 'Service User',
            'email' => 'service@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('service');

        $this->actingAs($user);

        $status = Status::create([
            'name' => 'Academic Status',
            'description' => 'Academic Desc',
            'color' => 'yellow',
            'order' => 2,
            'category' => 'academic',
        ]);

        $record = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'schools' => 'Academic School Name',
            'date_register' => '2026-01-01',
        ]);

        $newStatus = Status::create([
            'name' => 'Academic Status 2',
            'description' => 'Academic Desc 2',
            'color' => 'yellow',
            'order' => 3,
            'category' => 'academic',
        ]);

        Livewire::test(EditAcademic::class, ['record' => $record->getKey()])
            ->fillForm([
                'status_id' => $newStatus->id,
            ])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(RegistrationData::class, [
            'id' => $record->id,
            'status_id' => $newStatus->id,
        ]);
    }

    public function test_header_create_action_works_with_confirmation_on_create_sales(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::create(['name' => 'sales']);

        $user = User::create([
            'name' => 'Sales User 2',
            'email' => 'sales2@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('sales');

        $this->actingAs($user);

        $status = Status::create([
            'id' => 28,
            'name' => 'Status 1',
            'description' => 'Desc 1',
            'color' => 'red',
            'order' => 1,
            'category' => 'sales',
        ]);

        Livewire::test(CreateSales::class)
            ->fillForm([
                'type' => 'anbk',
                'schools' => 'Newly Created Salesforce School',
                'date_register' => '2026-03-01',
                'status_id' => $status->id,
            ])
            ->callAction('createHeader')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(RegistrationData::class, [
            'schools' => 'NEWLY CREATED SALESFORCE SCHOOL',
        ]);
    }
}
