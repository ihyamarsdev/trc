<?php

namespace Tests\Feature;

use App\Filament\User\Resources\Finance\FinanceResource\Pages\EditFinance;
use App\Filament\User\Resources\Finance\FinanceResource\Pages\ViewFinance;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceFinanceFormTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecord(): RegistrationData
    {
        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::create(['name' => 'finance']);

        $user = User::create([
            'name' => 'Finance User',
            'email' => 'finance@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('finance');
        $this->actingAs($user);

        $status = Status::create([
            'name' => 'Status Finance',
            'description' => 'Desc',
            'color' => 'yellow',
            'order' => 10,
            'category' => 'finance',
        ]);

        return RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'date_register' => '2026-01-01',
        ]);
    }

    public function test_real_option_and_tax_recalculate_total_in_finance_form(): void
    {
        $record = $this->makeRecord();

        Livewire::test(EditFinance::class, ['record' => $record->getKey()])
            ->set('data.price', 100000)
            ->set('data.real_count', 10)
            ->set('data.option_price', 'real')
            ->assertSet('data.total', 1000000.0)
            ->set('data.tax_rates', ['11', '2'])
            ->assertSet('data.tax_amount', 130000.0);
    }

    public function test_finance_form_has_requested_fields_and_no_trc_or_mitra(): void
    {
        $record = $this->makeRecord();

        Livewire::test(EditFinance::class, ['record' => $record->getKey()])
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

    public function test_finance_view_page_renders_tax_labels(): void
    {
        $record = $this->makeRecord();
        $record->update(['tax_rates' => ['2', '0.5'], 'tax_amount' => '25000']);

        Livewire::test(ViewFinance::class, ['record' => $record->getKey()])
            ->assertSuccessful()
            ->assertSee(['2%', '0,5%']);
    }
}
