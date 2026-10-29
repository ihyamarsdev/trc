<?php

namespace Tests\Feature;

use App\Filament\Components\Admin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\EditAdmin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\ViewAdmin;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminFinanceFormTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecord(): RegistrationData
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

        return RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'date_register' => '2026-01-01',
        ]);
    }

    public function test_real_option_uses_manual_student_count_and_tax_is_applied_to_total(): void
    {
        $record = $this->makeRecord();

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->set('data.price', 100000)
            ->set('data.real_count', 10)
            ->set('data.option_price', 'real')
            ->assertSet('data.total', 1000000.0)
            ->set('data.tax_rates', ['11', 'custom'])
            ->set('data.tax_custom_rate', '1')
            ->assertSet('data.tax_amount', 120000.0)
            ->set('data.real_count', 20)
            ->assertSet('data.total', 2000000.0)
            ->assertSet('data.tax_amount', 240000.0);
    }

    public function test_tax_rate_total_sums_selected_rates_and_manual_rate(): void
    {
        $this->assertSame(13.5, Admin::taxRateTotal(['11', '2', '0.5'], null));
        $this->assertSame(1.5, Admin::taxRateTotal(['custom'], '1.5'));
        $this->assertSame(0.0, Admin::taxRateTotal(null, '3'));
        $this->assertSame(['11%', '0,5%', '1,25%'], Admin::taxRateLabels(['11', '0.5', 'custom'], '1.25'));
    }

    public function test_finance_form_has_requested_fields_and_no_trc_or_mitra(): void
    {
        $record = $this->makeRecord();

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->assertFormFieldExists('invoice_amount')
            ->assertFormFieldExists('support_school')
            ->assertFormFieldExists('tax_rates')
            ->assertFormFieldExists('tax_amount')
            ->assertFormFieldDoesNotExist('student_count_1')
            ->assertFormFieldDoesNotExist('mitra_difference')
            ->assertFormFieldDoesNotExist('ss_difference')
            ->assertFormFieldDoesNotExist('dll_difference')
            ->assertFormFieldIsHidden('real_count');
    }

    public function test_finance_values_are_saved_and_shown_on_view_page(): void
    {
        $record = $this->makeRecord();

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->fillForm([
                'invoice_amount' => 500000,
                'support_school' => 25000,
                'tax_rates' => ['2', '0.5'],
            ])
            ->callAction('saveHeader')
            ->assertHasNoErrors();

        $record->refresh();
        $this->assertSame(['2', '0.5'], array_map('strval', $record->tax_rates));
        $this->assertEquals(500000, $record->invoice_amount);
        $this->assertEquals(25000, $record->support_school);

        Livewire::test(ViewAdmin::class, ['record' => $record->getKey()])
            ->assertSuccessful()
            ->assertSee(['2%', '0,5%']);
    }
}
