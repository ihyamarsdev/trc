<?php

namespace Tests\Feature;

use App\Filament\User\Resources\Admin\AdminResource\Pages\ViewAdmin;
use App\Filament\User\Resources\Finance\FinanceResource\Pages\ViewFinance;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages\ViewSales;
use App\Filament\User\Resources\Service\ServiceResource\Pages\ViewService;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InfolistViewPagesTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecord(string $role): RegistrationData
    {
        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::create(['name' => $role]);

        $user = User::create([
            'name' => 'Role User',
            'email' => $role.'@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);
        $this->actingAs($user);

        $status = Status::create([
            'name' => 'Status Aktif',
            'description' => 'Desc',
            'color' => 'blue',
            'order' => 3,
            'category' => 'service',
        ]);

        return RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'type' => 'anbk',
            'schools' => 'SMA Negeri Contoh',
            'student_count' => 120,
            'date_register' => '2026-01-01',
            'tax_rates' => ['11', '0.5'],
            'option_price' => 'implementer_5',
            'students_download' => 'YA',
            'pm' => 'TIDAK',
        ]);
    }

    /**
     * @return array<string, array{0: string, 1: class-string, 2: list<string>, 3: list<string>}>
     */
    public static function pages(): array
    {
        $always = ['SMA Negeri Contoh', 'ANBK', 'Status Aktif', 'Kontak Sekolah'];

        return [
            'admin' => ['admin', ViewAdmin::class, [...$always, 'Service', 'Finance', 'Dokumen'], []],
            'finance' => ['finance', ViewFinance::class, [...$always, 'Service', 'Finance', 'Dokumen'], []],
            'service' => ['service', ViewService::class, [...$always, 'Service'], ['Finance', 'Dokumen']],
            'sales' => ['sales', ViewSales::class, $always, ['Service', 'Finance', 'Dokumen']],
        ];
    }

    /**
     * @param  class-string  $page
     * @param  list<string>  $expected
     * @param  list<string>  $absent
     */
    #[DataProvider('pages')]
    public function test_view_page_shows_the_tabs_of_its_role(string $role, string $page, array $expected, array $absent): void
    {
        $record = $this->makeRecord($role);

        Livewire::test($page, ['record' => $record->getKey()])
            ->assertSuccessful()
            ->assertSee($expected)
            ->assertDontSee($absent);
    }

    public function test_finance_tab_formats_option_and_tax(): void
    {
        $record = $this->makeRecord('admin');

        Livewire::test(ViewAdmin::class, ['record' => $record->getKey()])
            ->assertSee(['Jumlah Pelaksanaan (5)', '11%', '0,5%']);
    }
}
