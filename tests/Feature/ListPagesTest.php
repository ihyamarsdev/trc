<?php

namespace Tests\Feature;

use App\Filament\User\Resources\Admin\AdminResource\Pages\ListAdmins;
use App\Filament\User\Resources\Finance\FinanceResource\Pages\ListFinances;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages\ListSales;
use App\Filament\User\Resources\Service\ServiceResource\Pages\ListServices;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: class-string, 2: int}>
     */
    public static function listPages(): array
    {
        return [
            'admin' => ['admin', ListAdmins::class, 1],
            'sales' => ['sales', ListSales::class, 1],
            'service' => ['service', ListServices::class, 5],
            'finance' => ['finance', ListFinances::class, 8],
        ];
    }

    /**
     * @param  class-string  $page
     */
    #[DataProvider('listPages')]
    public function test_list_shows_school_with_program_and_status_for_its_role(string $role, string $page, int $statusOrder): void
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
            'name' => 'Status Daftar',
            'description' => 'Desc',
            'color' => 'blue',
            'order' => $statusOrder,
            'category' => $role,
        ]);

        $record = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'schools' => 'SMA Negeri Daftar',
            'type' => 'anbk',
            'years' => now('Asia/Jakarta')->format('Y'),
            'date_register' => '2026-01-01',
        ]);

        Livewire::test($page)
            ->loadTable()
            ->assertCanSeeTableRecords([$record])
            ->assertSee(['SMA Negeri Daftar', 'ANBK', 'Status Daftar'])
            ->assertDontSee('Urutkan menurut')
            ->searchTable('SMA Negeri Daftar')
            ->assertCanSeeTableRecords([$record])
            ->searchTable('tidak-ada-sekolah-ini')
            ->assertCanNotSeeTableRecords([$record]);
    }
}
