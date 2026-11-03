<?php

namespace Tests\Feature;

use App\Filament\Components\Support\StatusTransition;
use App\Filament\User\Resources\Activity\ActivityResource\Pages\ViewActivities;
use App\Filament\User\Resources\Admin\AdminResource\Pages\CreateAdmin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\EditAdmin;
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
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function colourMoves(): array
    {
        return [
            'red to yellow' => ['red', 'yellow', true],
            'yellow to blue' => ['yellow', 'blue', true],
            'blue to green' => ['blue', 'green', true],
            'yellow back to red' => ['yellow', 'red', true],
            'green back to blue' => ['green', 'blue', true],
            'same colour' => ['blue', 'blue', true],
            'red to blue' => ['red', 'blue', false],
            'red to green' => ['red', 'green', false],
            'yellow to green' => ['yellow', 'green', false],
            'green back to yellow' => ['green', 'yellow', false],
        ];
    }

    #[DataProvider('colourMoves')]
    public function test_a_status_may_only_move_one_colour_at_a_time(string $from, string $to, bool $allowed): void
    {
        $complete = array_fill_keys(array_keys(StatusTransition::REQUIRED_FIELDS), 'x');

        $denial = StatusTransition::denial($this->statuses[$from], $this->statuses[$to], $complete);

        $allowed ? $this->assertNull($denial) : $this->assertNotNull($denial);
    }

    public function test_leaving_red_needs_every_required_field_and_names_the_missing_ones(): void
    {
        $attributes = array_fill_keys(array_keys(StatusTransition::REQUIRED_FIELDS), 'x');
        $attributes['principal_phone'] = null;
        $attributes['student_count'] = '';

        $denial = StatusTransition::denial($this->statuses['red'], $this->statuses['yellow'], $attributes);

        $this->assertStringContainsString('No Handphone Kepala Sekolah', $denial);
        $this->assertStringContainsString('Jumlah Siswa', $denial);
    }

    public function test_a_new_record_cannot_start_beyond_yellow(): void
    {
        $complete = array_fill_keys(array_keys(StatusTransition::REQUIRED_FIELDS), 'x');

        $this->assertNull(StatusTransition::initialDenial($this->statuses['red'], []));
        $this->assertNull(StatusTransition::initialDenial($this->statuses['yellow'], $complete));
        $this->assertNotNull(StatusTransition::initialDenial($this->statuses['yellow'], []));
        $this->assertNotNull(StatusTransition::initialDenial($this->statuses['blue'], $complete));
    }

    public function test_admin_cannot_skip_a_colour_from_the_normal_form(): void
    {
        $record = $this->recordIn('red', $this->actAs('admin'));

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->fillForm(['status_id' => $this->statuses['blue']->id])
            ->callAction('saveHeader')
            ->assertNotified('Status tidak dapat diubah');

        $this->assertSame($this->statuses['red']->id, $record->refresh()->status_id);
        $this->assertSame(1, $record->activity()->count());
    }

    public function test_admin_override_needs_a_reason_and_is_flagged_in_the_log(): void
    {
        $record = $this->recordIn('red', $this->actAs('admin'));

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->callAction('overrideStatus', ['status_id' => $this->statuses['blue']->id, 'reason' => 'pendek'])
            ->assertHasActionErrors(['reason']);

        $this->assertSame($this->statuses['red']->id, $record->refresh()->status_id);

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->callAction('overrideStatus', ['status_id' => $this->statuses['blue']->id, 'reason' => 'Sudah berjalan di luar sistem'])
            ->assertHasNoActionErrors();

        $record->refresh();
        $latest = $record->latestStatusLog;

        $this->assertSame($this->statuses['blue']->id, $record->status_id);
        $this->assertSame('blue', $record->status_color);
        $this->assertTrue($latest->is_override);
        $this->assertSame('Sudah berjalan di luar sistem', $latest->note);
    }

    public function test_service_moves_a_record_forward_and_the_log_only_grows(): void
    {
        $record = $this->recordIn('yellow', $this->actAs('service'));

        Livewire::test(EditService::class, ['record' => $record->getKey()])
            ->fillForm(['status_id' => $this->statuses['blue']->id])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $this->assertSame([$this->statuses['yellow']->id, $this->statuses['blue']->id], $record->activity()->orderBy('id')->pluck('status_id')->all());
    }

    public function test_moving_back_appends_a_log_entry_instead_of_deleting_history(): void
    {
        $record = $this->recordIn('yellow', $this->actAs('admin'));

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->fillForm(['status_id' => $this->statuses['red']->id])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $this->assertSame('red', $record->refresh()->status->color, 'a status picked in this save is not auto-promoted');

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->fillForm(['status_id' => $this->statuses['yellow']->id])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $this->assertSame(
            ['yellow', 'red', 'yellow'],
            $record->activity()->orderBy('id')->get()->map(fn (RegistrationStatus $log): string => $log->status->color)->all(),
        );
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

    public function test_admin_cannot_create_a_record_straight_in_blue(): void
    {
        $this->actAs('admin');

        Livewire::test(CreateAdmin::class)
            ->fillForm(['type' => 'anbk', 'status_id' => $this->statuses['blue']->id, 'schools' => 'SMA Baru', 'date_register' => '2026-02-01 08:00:00'])
            ->call('create')
            ->assertNotified('Status tidak dapat diubah');

        $this->assertDatabaseMissing(RegistrationData::class, ['schools' => 'SMA BARU']);
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

    public function test_activity_timeline_marks_an_override_and_shows_its_reason(): void
    {
        $admin = $this->actAs('admin');
        $record = $this->recordIn('red', $admin);
        StatusTransition::override($record, $this->statuses['blue'], 'Sudah berjalan di luar sistem', $admin->id);

        Livewire::test(ViewActivities::class, ['record' => $record->getKey()])
            ->assertSee('Override admin')
            ->assertSee('Alasan: Sudah berjalan di luar sistem');
    }
}
