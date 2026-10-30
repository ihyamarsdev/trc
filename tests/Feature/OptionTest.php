<?php

namespace Tests\Feature;

use App\Filament\Enum\OptionGroup;
use App\Filament\Resources\OptionResource;
use App\Filament\Resources\OptionResource\Pages\CreateOption;
use App\Filament\Resources\OptionResource\Pages\EditOption;
use App\Filament\Resources\OptionResource\Pages\ListOptions;
use App\Filament\User\Resources\Admin\AdminResource\Pages\EditAdmin;
use App\Models\Option;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OptionTest extends TestCase
{
    use RefreshDatabase;

    private function actAs(string $role, string $panel): User
    {
        Filament::setCurrentPanel(Filament::getPanel($panel));

        Role::findOrCreate($role);
        $user = User::create([
            'name' => 'Role User',
            'email' => $role.'@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    public function test_migration_seeds_the_choices_that_used_to_be_hard_coded(): void
    {
        $this->assertSame(['anbk', 'apps', 'snbt', 'tka', 'pasj'], array_keys(Option::list(OptionGroup::Program)));
        $this->assertSame(['PERIODE 1', 'PERIODE 2'], array_keys(Option::list(OptionGroup::Periode)));
        $this->assertSame(['SD', 'MI', 'SMP', 'MTS', 'SMA', 'MA', 'SMK'], array_keys(Option::list(OptionGroup::Jenjang)));
        $this->assertSame(['SIPLAH', 'SI/TF', 'CASH'], array_keys(Option::list(OptionGroup::PaymentVia)));
        $this->assertCount(4, Option::list(OptionGroup::Bank));
        $this->assertSame('0,5%', Option::list(OptionGroup::Tax)['0.5']);
    }

    public function test_program_meta_ignores_case_and_falls_back(): void
    {
        $this->assertSame('ANBK', Option::programMeta('ANBK')['nameRegister']);
        $this->assertSame('ASESMEN NASIONAL BERBASIS KOMPUTER', Option::programMeta('anbk')['DescriptionRegister']);
        $this->assertSame('APPS', Option::programMeta('tidak-ada')['nameRegister']);
        $this->assertSame(['nameRegister' => 'NONE', 'DescriptionRegister' => 'NONE'], Option::programMeta('tidak-ada', 'none'));
        $this->assertSame('PROGRAM ANALISIS SIDIK JARI', Option::programMeta('pasj')['DescriptionRegister']);
    }

    public function test_changes_show_up_immediately_despite_the_cache(): void
    {
        $this->assertArrayNotHasKey('PT. BARU BANK XYZ', Option::list(OptionGroup::Bank));

        $bank = Option::factory()->inGroup(OptionGroup::Bank)->create(['value' => 'PT. BARU BANK XYZ', 'label' => 'PT. BARU BANK XYZ']);
        $this->assertArrayHasKey('PT. BARU BANK XYZ', Option::list(OptionGroup::Bank));

        $bank->update(['label' => 'Bank Baru']);
        $this->assertSame('Bank Baru', Option::list(OptionGroup::Bank)['PT. BARU BANK XYZ']);

        $bank->update(['is_active' => false]);
        $this->assertArrayNotHasKey('PT. BARU BANK XYZ', Option::list(OptionGroup::Bank));
        $this->assertArrayHasKey('PT. BARU BANK XYZ', Option::list(OptionGroup::Bank, onlyActive: false));

        $bank->delete();
        $this->assertArrayNotHasKey('PT. BARU BANK XYZ', Option::list(OptionGroup::Bank, onlyActive: false));
    }

    public function test_choices_keep_a_retired_value_the_record_still_holds(): void
    {
        Option::query()->where('group', 'jenjang')->where('value', 'SMK')->update(['is_active' => false]);
        Option::flush(OptionGroup::Jenjang);

        $this->assertArrayNotHasKey('SMK', Option::choices(OptionGroup::Jenjang));
        $this->assertSame('SMK (nonaktif)', Option::choices(OptionGroup::Jenjang, 'SMK')['SMK']);
        $this->assertSame('SMK', Option::label(OptionGroup::Jenjang, 'smk'));
    }

    public function test_edit_form_offers_options_from_the_table_and_saves_the_chosen_one(): void
    {
        $user = $this->actAs('admin', 'user');
        Option::factory()->inGroup(OptionGroup::Bank)->create(['value' => 'PT. BARU BANK XYZ', 'label' => 'PT. BARU BANK XYZ']);
        $status = Status::create(['name' => 'S', 'description' => 'd', 'color' => 'yellow', 'order' => 2, 'category' => 'sales']);
        $record = RegistrationData::factory()->create(['users_id' => $user->id, 'status_id' => $status->id, 'date_register' => '2026-01-01']);

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->fillForm(['bank_name' => 'PT. BARU BANK XYZ'])
            ->callAction('saveHeader')
            ->assertHasNoFormErrors();

        $this->assertSame('PT. BARU BANK XYZ', $record->refresh()->bank_name);
    }

    public function test_saving_a_record_does_not_wipe_a_bank_that_was_retired_later(): void
    {
        $user = $this->actAs('admin', 'user');
        $status = Status::create(['name' => 'S', 'description' => 'd', 'color' => 'yellow', 'order' => 2, 'category' => 'sales']);
        $record = RegistrationData::factory()->create([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'bank_name' => 'PT. TDW BANK MANDIRI',
            'date_register' => '2026-01-01',
        ]);

        Option::query()->where('group', 'bank')->where('value', 'PT. TDW BANK MANDIRI')->firstOrFail()->update(['is_active' => false]);

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->assertSet('data.bank_name', 'PT. TDW BANK MANDIRI')
            ->callAction('saveHeader')
            ->assertHasNoFormErrors();

        $this->assertSame('PT. TDW BANK MANDIRI', $record->refresh()->bank_name);

        Livewire::test(EditAdmin::class, ['record' => $record->getKey()])
            ->fillForm(['bank_name' => 'PT. BANK LAIN'])
            ->callAction('saveHeader')
            ->assertHasFormErrors(['bank_name']);
    }

    public function test_only_admins_reach_the_data_master_resource(): void
    {
        $this->actAs('finance', 'admin');

        $this->assertFalse(Filament::auth()->user()->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_admin_can_add_a_choice_which_goes_to_the_end_of_its_list(): void
    {
        $this->actAs('admin', 'admin');

        Livewire::test(CreateOption::class)
            ->fillForm(['group' => 'program', 'label' => 'TKA Plus', 'value' => 'TKA-PLUS', 'description' => 'Program baru'])
            ->call('create')
            ->assertHasNoFormErrors();

        $program = Option::query()->where('group', 'program')->where('label', 'TKA Plus')->firstOrFail();
        $this->assertSame('tka-plus', $program->value);
        $this->assertSame(6, $program->sort_order);
        $this->assertSame('Program baru', Option::programMeta('tka-plus')['DescriptionRegister']);
    }

    public function test_value_is_unique_inside_a_group_but_may_repeat_across_groups(): void
    {
        $this->actAs('admin', 'admin');

        Livewire::test(CreateOption::class)
            ->fillForm(['group' => 'jenjang', 'label' => 'Sma', 'value' => 'SMA'])
            ->call('create')
            ->assertHasFormErrors(['value']);

        Livewire::test(CreateOption::class)
            ->fillForm(['group' => 'bank', 'label' => 'SMA', 'value' => 'SMA'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_tax_choice_must_be_a_number(): void
    {
        $this->actAs('admin', 'admin');

        Livewire::test(CreateOption::class)
            ->fillForm(['group' => 'tax', 'label' => 'sepuluh', 'value' => 'sepuluh'])
            ->call('create')
            ->assertHasFormErrors(['value']);

        Livewire::test(CreateOption::class)
            ->fillForm(['group' => 'tax', 'label' => '1,5%', 'value' => '1.5'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_value_cannot_change_after_creation_but_label_can(): void
    {
        $this->actAs('admin', 'admin');
        $option = Option::query()->where('group', 'jenjang')->where('value', 'SMA')->firstOrFail();

        Livewire::test(EditOption::class, ['record' => $option->getKey()])
            ->assertFormFieldIsDisabled('value')
            ->assertFormFieldIsDisabled('group')
            ->fillForm(['label' => 'SMA Umum'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('SMA', $option->refresh()->value);
        $this->assertSame('SMA Umum', Option::list(OptionGroup::Jenjang)['SMA']);
    }

    public function test_a_choice_in_use_cannot_be_deleted_but_an_unused_one_can(): void
    {
        $user = $this->actAs('admin', 'admin');
        $status = Status::create(['name' => 'S', 'description' => 'd', 'color' => 'yellow', 'order' => 2, 'category' => 'sales']);
        RegistrationData::factory()->create(['users_id' => $user->id, 'status_id' => $status->id, 'education_level' => 'SMA', 'date_register' => '2026-01-01']);

        $used = Option::query()->where('group', 'jenjang')->where('value', 'SMA')->firstOrFail();
        $unused = Option::query()->where('group', 'jenjang')->where('value', 'SD')->firstOrFail();

        $this->assertSame(1, $used->usageCount());

        Livewire::test(ListOptions::class)
            ->set('activeTab', 'jenjang')
            ->callTableAction('delete', $used)
            ->assertNotified('Tidak bisa dihapus');

        $this->assertModelExists($used);

        Livewire::test(ListOptions::class)
            ->set('activeTab', 'jenjang')
            ->callTableAction('delete', $unused);

        $this->assertModelMissing($unused);
    }

    public function test_tax_usage_is_counted_from_the_json_list(): void
    {
        $user = $this->actAs('admin', 'admin');
        $status = Status::create(['name' => 'S', 'description' => 'd', 'color' => 'yellow', 'order' => 2, 'category' => 'sales']);
        RegistrationData::factory()->create(['users_id' => $user->id, 'status_id' => $status->id, 'tax_rates' => ['11', '2'], 'date_register' => '2026-01-01']);

        $this->assertSame(1, Option::query()->where('group', 'tax')->where('value', '11')->firstOrFail()->usageCount());
        $this->assertSame(0, Option::query()->where('group', 'tax')->where('value', '0.5')->firstOrFail()->usageCount());
    }

    public function test_list_page_shows_each_group_in_its_own_tab(): void
    {
        $this->actAs('admin', 'admin');

        Livewire::test(ListOptions::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(Option::query()->where('group', 'program')->get())
            ->assertCanNotSeeTableRecords(Option::query()->where('group', 'bank')->get())
            ->set('activeTab', 'bank')
            ->assertCanSeeTableRecords(Option::query()->where('group', 'bank')->get());

        $this->assertSame('Data Master', OptionResource::getNavigationLabel());
    }
}
