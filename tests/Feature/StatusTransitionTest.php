<?php

namespace Tests\Feature;

use App\Filament\Components\Admin;
use App\Filament\Components\Support\StatusTransition;
use App\Filament\User\Resources\Activity\ActivityResource\Pages\ViewActivities;
use App\Filament\User\Resources\Admin\AdminResource\Pages\EditAdmin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\ListAdmins;
use App\Filament\User\Resources\Finance\FinanceResource\Pages\ListFinances;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages\CreateSales;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages\EditSales;
use App\Filament\User\Resources\Service\ServiceResource\Pages\EditService;
use App\Filament\User\Resources\Service\ServiceResource\Pages\ListServices;
use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, Status> */
    private array $statuses = [];

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('user'));

        foreach (['red' => 1, 'yellow' => 2, 'blue' => 3, 'green' => 11] as $color => $order) {
            $this->statuses[$color] = Status::create([
                'name' => "Status {$color}",
                'description' => 'Desc',
                'color' => $color,
                'order' => $order,
                'category' => 'test',
            ]);
        }
    }

    private function actAs(string $role): User
    {
        Role::findOrCreate($role);
        $user = User::create(['name' => ucfirst($role), 'email' => "{$role}@example.com", 'password' => bcrypt('password')]);
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function recordIn(string $color, User $owner, array $attributes = []): RegistrationData
    {
        $record = RegistrationData::factory()->create(array_merge([
            'users_id' => $owner->id,
            'status_id' => $this->statuses[$color]->id,
            'date_register' => '2026-01-01',
            'implementation_estimate' => now()->addMonth(),
            'principal' => 'Budi',
            'principal_phone' => '+6281234567890',
            'student_count' => 100,
            'schools' => 'SMA Contoh',
            'curriculum_deputies' => 'Sari',
            'curriculum_deputies_phone' => '+6281234567891',
        ], $attributes));

        RegistrationStatus::create(['registration_id' => $record->id, 'status_id' => $record->status_id, 'user_id' => $owner->id]);

        return $record;
    }

    /**
     * @return array<int, string> colours of the record's log, oldest first
     */
    private function logColours(RegistrationData $record): array
    {
        return $record->activity()->orderBy('id')->get()->map(fn (RegistrationStatus $log): string => $log->status->color)->all();
    }

    public function test_admin_can_pick_any_status_and_each_change_is_appended_to_the_log(): void
    {
        $record = $this->recordIn('yellow', $this->actAs('admin'));

        foreach (['green', 'red', 'blue'] as $color) {
            Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
                ->fillForm(['status_id' => $this->statuses[$color]->id])
                ->callAction('saveHeader')
                ->assertHasNoErrors();

            $this->assertSame($color, $record->refresh()->status->color);
        }

        $this->assertSame(['yellow', 'green', 'red', 'blue'], $this->logColours($record));
    }

    public function test_service_moves_a_record_forward_and_the_log_only_grows(): void
    {
        $record = $this->recordIn('yellow', $this->actAs('service'));

        Livewire::test(EditService::class, ['record' => $record->getKey()])
            ->fillForm(['status_id' => $this->statuses['blue']->id])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $this->assertSame(['yellow', 'blue'], $this->logColours($record));
    }

    public function test_complete_red_record_becomes_yellow_by_itself_when_sales_saves_it(): void
    {
        $sales = $this->actAs('sales');
        $service = $this->actAs('service');
        $this->actingAs($sales);
        $record = $this->recordIn('red', $sales, ['schools' => 'SMA Lengkap']);

        Livewire::test(EditSales::class, ['record' => $record->getKey()])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $record->refresh();

        $this->assertSame('yellow', $record->status->color);
        $this->assertSame('yellow', $record->status_color);
        $this->assertSame('Otomatis: data wajib sudah lengkap', $record->latestStatusLog->note);
        $this->assertSame(1, $service->notifications()->count());
    }

    public function test_red_record_stays_red_while_a_required_field_is_blank_or_the_estimate_is_past(): void
    {
        $sales = $this->actAs('sales');
        $incomplete = $this->recordIn('red', $sales, ['principal_phone' => null]);
        $overdue = $this->recordIn('red', $sales, ['implementation_estimate' => now()->subDay()]);

        foreach ([$incomplete, $overdue] as $record) {
            Livewire::test(EditSales::class, ['record' => $record->getKey()])
                ->callAction('saveHeader')
                ->assertHasNoErrors();

            $this->assertSame('red', $record->refresh()->status->color);
        }
    }

    public function test_a_status_the_admin_picks_is_not_overwritten_by_auto_promotion(): void
    {
        $record = $this->recordIn('yellow', $this->actAs('admin'));

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->fillForm(['status_id' => $this->statuses['red']->id])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $this->assertSame('red', $record->refresh()->status->color);
    }

    public function test_sales_create_starts_red_and_turns_yellow_once_every_required_field_is_filled(): void
    {
        $this->actAs('sales');

        $form = ['type' => 'anbk', 'date_register' => '2026-02-01 08:00:00', 'schools' => 'SMA Baru Lengkap'];
        $required = ['principal' => 'Budi', 'principal_phone' => '+6281234567890', 'student_count' => 100, 'implementation_estimate' => now()->addMonth()->format('Y-m-d H:i:s'), 'curriculum_deputies' => 'Sari', 'curriculum_deputies_phone' => '+6281234567891'];

        Livewire::test(CreateSales::class)->fillForm($form)->call('create')->assertHasNoFormErrors();
        $this->assertSame('red', RegistrationData::where('schools', 'SMA BARU LENGKAP')->first()->status->color);

        Livewire::test(CreateSales::class)->fillForm(array_merge($form, $required, ['schools' => 'SMA Baru Penuh']))->call('create')->assertHasNoFormErrors();
        $this->assertSame('yellow', RegistrationData::where('schools', 'SMA BARU PENUH')->first()->status->color);
    }

    public function test_service_sees_yellow_and_blue_only_and_finance_sees_blue_and_green_only(): void
    {
        $owner = $this->actAs('service');
        Role::findOrCreate('finance');
        $owner->assignRole('finance');

        $year = now('Asia/Jakarta')->format('Y');
        $records = collect(['red', 'yellow', 'blue', 'green'])
            ->mapWithKeys(fn (string $color): array => [$color => $this->recordIn($color, $owner, ['years' => $year])]);

        Livewire::test(ListServices::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$records['yellow'], $records['blue']])
            ->assertCanNotSeeTableRecords([$records['red'], $records['green']]);

        Livewire::test(ListFinances::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$records['blue'], $records['green']])
            ->assertCanNotSeeTableRecords([$records['red'], $records['yellow']]);
    }

    public function test_bulk_change_moves_every_selected_record_and_logs_each_one_with_the_note(): void
    {
        $admin = $this->actAs('admin');
        $year = now('Asia/Jakarta')->format('Y');
        $records = collect(['red', 'yellow', 'blue'])->map(fn (string $color): RegistrationData => $this->recordIn($color, $admin, ['years' => $year]));
        $alreadyGreen = $this->recordIn('green', $admin, ['years' => $year]);

        Livewire::test(ListAdmins::class)
            ->loadTable()
            ->callTableBulkAction('changeStatus', $records->push($alreadyGreen), ['status_id' => $this->statuses['green']->id, 'note' => 'Serentak Kab. Bogor'])
            ->assertHasNoTableBulkActionErrors()
            ->assertNotified('3 data diubah ke Status green');

        foreach ($records->take(3) as $record) {
            $record->refresh();

            $this->assertSame('green', $record->status->color);
            $this->assertSame('green', $record->status_color);
            $this->assertSame('Ubah massal: Serentak Kab. Bogor', $record->latestStatusLog->note);
        }

        $this->assertSame(1, $alreadyGreen->activity()->count(), 'a record already in the status gets no extra entry');
        $this->assertSame(['red', 'green'], $this->logColours($records[0]));
    }

    public function test_bulk_change_in_service_only_offers_the_statuses_service_works_in(): void
    {
        $service = $this->actAs('service');
        $record = $this->recordIn('yellow', $service, ['years' => now('Asia/Jakarta')->format('Y')]);

        Livewire::test(ListServices::class)
            ->loadTable()
            ->callTableBulkAction('changeStatus', [$record], ['status_id' => $this->statuses['red']->id])
            ->assertHasTableBulkActionErrors(['status_id']);

        $this->assertSame('yellow', $record->refresh()->status->color);
    }

    public function test_change_many_handles_more_than_one_chunk(): void
    {
        $owner = User::create(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('password')]);
        $records = RegistrationData::factory()->count(501)->create(['users_id' => $owner->id, 'status_id' => $this->statuses['red']->id]);

        $result = StatusTransition::changeMany($records, $this->statuses['blue'], $owner->id);

        $this->assertSame(['changed' => 501, 'unchanged' => 0], $result);
        $this->assertSame(501, RegistrationData::where('status_id', $this->statuses['blue']->id)->where('status_color', 'blue')->count());
        $this->assertSame(501, RegistrationStatus::where('status_id', $this->statuses['blue']->id)->count());
    }

    public function test_activity_timeline_shows_the_note_of_an_entry(): void
    {
        $admin = $this->actAs('admin');
        $record = $this->recordIn('red', $admin);
        StatusTransition::changeMany(collect([$record]), $this->statuses['blue'], $admin->id, 'Serentak Kab. Bogor');

        Livewire::test(ViewActivities::class, ['record' => $record->getKey()])
            ->assertSee('Catatan: Ubah massal: Serentak Kab. Bogor');

        $modal = collect(Admin::getActivityTimelineData($record)['activities'])->pluck('description')->implode(' ');
        $this->assertStringContainsString('Catatan: Ubah massal: Serentak Kab. Bogor', $modal);
    }
}
