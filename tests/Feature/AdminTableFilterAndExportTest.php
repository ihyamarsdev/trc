<?php

namespace Tests\Feature;

use App\Filament\Enum\OptionGroup;
use App\Filament\User\Resources\Admin\AdminResource\Pages\ListAdmins;
use App\Models\Option;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTableFilterAndExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::create(['name' => 'admin']);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
        $this->actingAs($admin);
    }

    private function recordWithColor(string $color, int $order): RegistrationData
    {
        $status = Status::create([
            'name' => "Status {$color}",
            'description' => 'Desc',
            'color' => $color,
            'order' => $order,
            'category' => 'admin',
        ]);

        return RegistrationData::factory()->create([
            'users_id' => auth()->id(),
            'status_id' => $status->id,
            'years' => now('Asia/Jakarta')->format('Y'),
            'date_register' => '2026-01-01',
        ]);
    }

    public function test_status_color_filter_accepts_multiple_colors(): void
    {
        $red = $this->recordWithColor('red', 1);
        $yellow = $this->recordWithColor('yellow', 2);
        $green = $this->recordWithColor('green', 3);

        Livewire::test(ListAdmins::class)
            ->loadTable()
            ->filterTable('status_color', ['red', 'yellow'])
            ->assertCanSeeTableRecords([$red, $yellow])
            ->assertCanNotSeeTableRecords([$green]);
    }

    public function test_export_column_list_has_select_all_toggle(): void
    {
        $record = $this->recordWithColor('red', 1);

        Livewire::test(ListAdmins::class)
            ->loadTable()
            ->mountTableBulkAction('Export', [$record->getKey()])
            ->assertSee('Pilih semua');
    }

    public function test_export_uses_current_labels_and_choice_labels(): void
    {
        Option::query()->where('group', 'program')->where('value', 'tka')->update(['label' => 'Tes Kemampuan Akademik']);
        Option::flush(OptionGroup::Program);
        $record = $this->recordWithColor('red', 1);
        $record->update(['type' => 'tka', 'option_price' => 'implementer_30', 'tax_rates' => ['11'], 'notes' => 'Catatan uji']);

        $component = Livewire::test(ListAdmins::class)
            ->loadTable()
            ->callTableBulkAction('Export', [$record->getKey()], ['file_name' => 'uji', 'format' => 'csv'])
            ->assertHasNoTableBulkActionErrors();

        $csv = base64_decode($component->effects['download']['content']);

        $this->assertStringContainsString('Salesforce', $csv);
        $this->assertStringContainsString('Harga SPJ', $csv);
        $this->assertStringContainsString('TES KEMAMPUAN AKADEMIK', $csv);
        $this->assertStringContainsString('Jumlah Pelaksanaan (30)', $csv);
        $this->assertStringContainsString('11%', $csv);
        $this->assertStringContainsString('Catatan uji', $csv);
        $this->assertStringContainsString('Status red', $csv);
    }
}
