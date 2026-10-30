<?php

namespace Tests\Feature;

use App\Filament\User\Resources\Finance\FinanceResource\Pages\ViewFinance;
use App\Livewire\DocumentWorkbench;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentWorkbenchTest extends TestCase
{
    use RefreshDatabase;

    private function actAs(string $role): User
    {
        Filament::setCurrentPanel(Filament::getPanel('user'));

        Role::findOrCreate($role);
        $user = User::create(['name' => ucfirst($role), 'email' => $role.'@example.com', 'password' => bcrypt('password')]);
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    private function record(array $attributes = []): RegistrationData
    {
        $user = User::query()->first();
        $status = Status::create(['name' => 'S', 'description' => 'd', 'color' => 'blue', 'order' => 8, 'category' => 'finance']);

        return RegistrationData::factory()->create(array_merge([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'type' => 'anbk',
            'schools' => 'SMAN 1 Contoh',
            'total' => '25000000',
            'date_register' => '2026-03-02',
            'payment_date' => '2026-04-15',
        ], $attributes));
    }

    public function test_only_admin_and_finance_may_use_it(): void
    {
        $this->actAs('sales');

        Livewire::test(DocumentWorkbench::class, ['record' => $this->record()])->assertForbidden();
    }

    public function test_preview_follows_the_chosen_company_and_the_typed_text(): void
    {
        $this->actAs('finance');
        $record = $this->record();

        $component = Livewire::test(DocumentWorkbench::class, ['record' => $record])
            ->assertSet('data.company', 'rasyidu')
            ->assertSet('data.document', 'spk');

        $html = $component->instance()->previewHtml();
        $this->assertStringContainsString('template/rasyidu/', $html);
        $this->assertStringContainsString('SURAT PERJANJIAN KERJASAMA', $html);
        $this->assertStringContainsString('KEPALA SEKOLAH SMAN 1 Contoh', $html);
        $this->assertStringNotContainsString('${', $html);

        $component->set('data.company', 'edunesia')->set('data.document', 'kuitansi')->set('data.detail_kwitansi', 'paket try out');

        $html = $component->instance()->previewHtml();
        $this->assertStringContainsString('template/edunesia/', $html);
        $this->assertStringContainsString('PAKET TRY OUT', $html);
        $this->assertNull($record->refresh()->detail_kwitansi, 'typing alone must not save');

        $component->set('data.document', 'invoice');
        $this->assertStringContainsString('invoice-background', $component->instance()->previewHtml());
    }

    public function test_spk_download_goes_to_the_route_of_the_programme_without_saving(): void
    {
        $this->actAs('finance');

        foreach ([['apps', 'edunesia', '/edunesia/download/apps'], ['snbt', 'rasyidu', '/rasyidu/download/snbt'], ['tka', 'rasyidu', '/rasyidu/download/anbk']] as [$type, $company, $path]) {
            $component = Livewire::test(DocumentWorkbench::class, ['record' => $this->record(['type' => $type])])
                ->set('data.company', $company)
                ->call('download')
                ->assertNotNotified();

            $this->assertStringContainsString($path, json_encode($component->effects['xjs'], JSON_UNESCAPED_SLASHES));
        }
    }

    public function test_an_unknown_company_or_document_falls_back_instead_of_failing(): void
    {
        $this->actAs('finance');

        $component = Livewire::test(DocumentWorkbench::class, ['record' => $this->record()])
            ->set('data.company', '../../.env')
            ->set('data.document', 'nope');

        $this->assertStringContainsString('template/rasyidu/', $component->instance()->previewHtml());
    }

    public function test_invoice_totals_follow_quantity_unit_price_and_tax(): void
    {
        $this->actAs('finance');

        Livewire::test(DocumentWorkbench::class, ['record' => $this->record()])
            ->set('data.document', 'invoice')
            ->set('data.qty_invoice', '250')
            ->set('data.unit_price', '100000')
            ->set('data.tax_option', 'none')
            ->assertSet('data.amount_invoice', '25000000')
            ->assertSet('data.subtotal_invoice', '25000000')
            ->assertSet('data.total_invoice', '25000000')
            ->set('data.tax_option', 'ppn')
            ->assertSet('data.total_invoice', '27750000')
            ->set('data.tax_option', 'pph')
            ->assertSet('data.total_invoice', '25500000')
            ->set('data.qty_invoice', '10')
            ->assertSet('data.subtotal_invoice', '1000000')
            ->assertSet('data.total_invoice', '1020000');
    }

    public function test_save_stores_the_documents_data_with_the_taxes_under_their_own_names(): void
    {
        $this->actAs('admin');
        $record = $this->record();

        Livewire::test(DocumentWorkbench::class, ['record' => $record])
            ->set('data.detail_kwitansi', 'paket try out')
            ->set('data.document', 'invoice')
            ->set('data.number_invoice', '12')
            ->set('data.detail_invoice', 'try out asesmen nasional')
            ->set('data.qty_invoice', '250')
            ->set('data.unit_price', '100000')
            ->set('data.tax_option', 'ppn')
            ->call('save')
            ->assertHasNoErrors()
            ->assertNotified('Data kwitansi dan invoice tersimpan');

        $record->refresh();
        $this->assertSame('PAKET TRY OUT', $record->detail_kwitansi);
        $this->assertSame('TRY OUT ASESMEN NASIONAL', $record->detail_invoice);
        $this->assertSame('12', $record->number_invoice);
        $this->assertSame('27750000', $record->total_invoice);
        $this->assertSame('0', $record->pph);
        $this->assertSame('11', $record->ppn);

        Livewire::test(DocumentWorkbench::class, ['record' => $record])
            ->set('data.document', 'invoice')
            ->set('data.tax_option', 'pph')
            ->call('save');

        $this->assertSame('2', $record->refresh()->pph);
        $this->assertSame('0', $record->ppn);
    }

    public function test_the_saved_tax_comes_back_as_the_selected_option(): void
    {
        $this->actAs('finance');

        foreach ([['2', '0', 'pph'], ['0', '11', 'ppn'], ['0', '0', 'none'], [null, null, 'none'], ['0', '2', null]] as [$pph, $ppn, $expected]) {
            Livewire::test(DocumentWorkbench::class, ['record' => $this->record(['pph' => $pph, 'ppn' => $ppn])])
                ->assertSet('data.tax_option', $expected);
        }
    }

    public function test_download_saves_first(): void
    {
        $this->actAs('finance');
        $record = $this->record();

        Livewire::test(DocumentWorkbench::class, ['record' => $record])
            ->set('data.document', 'invoice')
            ->set('data.number_invoice', '7')
            ->call('download')
            ->assertHasNoErrors();

        $this->assertSame('7', $record->refresh()->number_invoice);
    }

    public function test_finance_view_embeds_it_and_no_longer_has_the_old_document_buttons(): void
    {
        $this->actAs('finance');
        $record = $this->record();

        Livewire::test(ViewFinance::class, ['record' => $record->getKey()])
            ->assertSeeLivewire(DocumentWorkbench::class)
            ->assertDontSee('SPK RASYIDUU')
            ->assertDontSee('SPK EDUNESIA')
            ->assertDontSee('Kwitansi RASYIDUU')
            ->assertDontSee('Invoice EDUNESIA');
    }
}
