<?php

namespace Tests\Feature;

use App\Filament\User\Resources\Admin\AdminResource\Pages\CreateAdmin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\EditAdmin;
use App\Filament\User\Resources\Finance\FinanceResource\Pages\EditFinance;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages\CreateSales;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages\EditSales;
use App\Filament\User\Resources\Service\ServiceResource\Pages\EditService;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private function actAs(string $role): User
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

        return $user;
    }

    private function recordFor(User $user): RegistrationData
    {
        $status = Status::create([
            'name' => 'Status Aktif',
            'description' => 'Desc',
            'color' => 'yellow',
            'order' => 1,
            'category' => 'sales',
        ]);

        return RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'date_register' => '2026-01-01',
        ]);
    }

    /**
     * @return array<string, array{0: string, 1: class-string, 2: list<string>}>
     */
    public static function editPages(): array
    {
        return [
            'admin' => ['admin', EditAdmin::class, ['schools', 'principal_phone', 'account_count_created', 'price', 'tax_rates']],
            'sales' => ['sales', EditSales::class, ['schools', 'principal_phone', 'notes', 'implementation_estimate']],
            'service' => ['service', EditService::class, ['status_id', 'account_count_created', 'difference', 'pm']],
            'finance' => ['finance', EditFinance::class, ['status_id', 'price', 'tax_amount', 'payment_name']],
        ];
    }

    /**
     * @param  class-string  $page
     * @param  list<string>  $fields
     */
    #[DataProvider('editPages')]
    public function test_edit_form_contains_the_fields_of_its_role(string $role, string $page, array $fields): void
    {
        $record = $this->recordFor($this->actAs($role));

        $component = Livewire::test($page, ['record' => $record->getKey()])->assertSuccessful();

        foreach ($fields as $field) {
            $component->assertFormFieldExists($field);
        }
    }

    public function test_admin_create_page_saves_school_and_service_data_from_separate_tabs(): void
    {
        $this->actAs('admin');
        $status = Status::create([
            'name' => 'Status Aktif',
            'description' => 'Desc',
            'color' => 'yellow',
            'order' => 1,
            'category' => 'sales',
        ]);

        Livewire::test(CreateAdmin::class)
            ->fillForm([
                'type' => 'anbk',
                'status_id' => $status->id,
                'schools' => 'SMA Contoh',
                'date_register' => '2026-02-01 08:00:00',
                'group' => '2026-03-01',
                'price' => 100000,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(RegistrationData::class, ['schools' => 'SMA Contoh', 'type' => 'anbk', 'price' => '100000']);
    }

    public function test_sales_create_page_renders_with_status_hidden_until_required_fields_are_filled(): void
    {
        $this->actAs('sales');

        Livewire::test(CreateSales::class)
            ->assertSuccessful()
            ->assertFormFieldExists('schools')
            ->assertFormFieldIsHidden('status_id');
    }
}
