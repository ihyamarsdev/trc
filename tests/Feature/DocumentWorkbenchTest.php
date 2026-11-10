<?php

namespace Tests\Feature;

use App\Filament\Components\Support\DocumentData;
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

        foreach ([['apps', 'edunesia', '/edunesia/download/apps'], ['snbt', 'rasyidu', '/rasyidu/download/spk-snbt'], ['tka', 'rasyidu', '/rasyidu/download/spk-tka'], ['anbk', 'rasyidu', '/rasyidu/download/anbk']] as [$type, $company, $path]) {
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

    public function test_rasyidu_opens_the_spk_of_the_records_programme_and_offers_the_others(): void
    {
        $this->actAs('finance');

        $component = Livewire::test(DocumentWorkbench::class, ['record' => $this->record(['type' => 'tka'])])
            ->assertSet('data.document', 'spk-tka');

        $this->assertStringContainsString('template/rasyidu/', $component->instance()->previewHtml());
        $this->assertStringContainsString('Tes Kemampuan Akademik', $component->instance()->previewHtml());

        $component->set('data.document', 'spk-apps');
        $this->assertStringContainsString('Asesmen Psikotes Potensi Siswa', $component->instance()->previewHtml());

        $component->set('data.document', 'spk-snbt');
        $this->assertStringContainsString('UTBK-SNBT', $component->instance()->previewHtml());

        $component->set('data.company', 'edunesia')->assertSet('data.document', 'spk');
    }

    public function test_tdw_has_no_spk_and_offers_an_invoice_per_bank(): void
    {
        $this->actAs('finance');

        $component = Livewire::test(DocumentWorkbench::class, ['record' => $this->record()])
            ->assertSet('data.document', 'spk')
            ->set('data.company', 'tdw')
            ->assertSet('data.document', 'kuitansi');

        $this->assertStringContainsString('template/tdw/', $component->instance()->previewHtml());

        $component->set('data.document', 'spk');
        $this->assertStringContainsString('kuitansi-background', $component->instance()->previewHtml());

        $component->set('data.document', 'invoice-dki');
        $this->assertStringContainsString('invoice-dki-background', $component->instance()->previewHtml());

        $component->set('data.document', 'invoice-mandiri');
        $this->assertStringContainsString('invoice-mandiri-background', $component->instance()->previewHtml());

        $component->call('download');
        $this->assertStringContainsString('/tdw/download/invoice-mandiri', json_encode($component->effects['xjs'], JSON_UNESCAPED_SLASHES));
    }

    public function test_switching_company_keeps_the_kind_of_document(): void
    {
        $this->actAs('finance');

        Livewire::test(DocumentWorkbench::class, ['record' => $this->record()])
            ->set('data.document', 'invoice')
            ->set('data.company', 'tdw')
            ->assertSet('data.document', 'invoice-mandiri')
            ->set('data.company', 'edusindo')
            ->assertSet('data.document', 'invoice');
    }

    public function test_invoice_totals_follow_quantity_unit_price_and_the_chosen_taxes(): void
    {
        $this->actAs('finance');

        Livewire::test(DocumentWorkbench::class, ['record' => $this->record()])
            ->set('data.document', 'invoice')
            ->set('data.qty_invoice', '250')
            ->set('data.unit_price', '100000')
            ->set('data.taxes', [])
            ->assertSet('data.amount_invoice', '25000000')
            ->assertSet('data.subtotal_invoice', '25000000')
            ->assertSet('data.total_invoice', '25000000')
            ->set('data.taxes', ['ppn'])
            ->assertSet('data.total_invoice', '27750000')
            ->set('data.taxes', ['pph'])
            ->assertSet('data.total_invoice', '25500000')
            ->set('data.taxes', ['pph', 'ppn'])
            ->assertSet('data.total_invoice', '28250000')
            ->set('data.qty_invoice', '10')
            ->assertSet('data.subtotal_invoice', '1000000')
            ->assertSet('data.total_invoice', '1130000');
    }

    public function test_the_invoice_description_is_limited_so_it_fits_the_invoice(): void
    {
        $this->actAs('finance');
        $record = $this->record();

        Livewire::test(DocumentWorkbench::class, ['record' => $record])
            ->set('data.document', 'invoice')
            ->set('data.detail_invoice', str_repeat('A', DocumentData::MAX_DETAIL + 1))
            ->call('save')
            ->assertHasErrors(['data.detail_invoice' => 'max']);

        $this->assertNull($record->refresh()->detail_invoice);

        Livewire::test(DocumentWorkbench::class, ['record' => $record])
            ->set('data.document', 'invoice')
            ->set('data.detail_invoice', str_repeat('A', DocumentData::MAX_DETAIL))
            ->call('save')
            ->assertHasNoErrors();
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
            ->set('data.taxes', ['ppn'])
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
            ->set('data.taxes', ['pph', 'ppn'])
            ->call('save');

        $this->assertSame('2', $record->refresh()->pph);
        $this->assertSame('11', $record->ppn);
        $this->assertSame('28250000', $record->total_invoice);
    }

    public function test_the_saved_taxes_come_back_as_the_selected_options(): void
    {
        $this->actAs('finance');

        foreach ([['2', '0', ['pph']], ['0', '11', ['ppn']], ['2', '11', ['pph', 'ppn']], ['0', '0', []], [null, null, []], ['0', '2', []]] as [$pph, $ppn, $expected]) {
            Livewire::test(DocumentWorkbench::class, ['record' => $this->record(['pph' => $pph, 'ppn' => $ppn])])
                ->assertSet('data.taxes', $expected);
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

    public function test_the_kwitansi_number_is_saved_and_printed(): void
    {
        $this->actAs('finance');
        $record = $this->record();

        $component = Livewire::test(DocumentWorkbench::class, ['record' => $record])
            ->set('data.company', 'tdw')
            ->set('data.document', 'kuitansi')
            ->set('data.number_kwitansi', '505')
            ->set('data.detail_kwitansi', '232 program tka');

        $html = $component->instance()->previewHtml();
        $this->assertStringContainsString('Pembayaran 232 PROGRAM TKA', $html);
        $this->assertStringContainsString('>505<', $html);

        $component->call('save');

        $record->refresh();
        $this->assertSame('505', $record->number_kwitansi);
    }

    public function test_the_mandiri_invoice_note_is_saved_printed_and_limited(): void
    {
        $this->actAs('finance');
        $record = $this->record();

        $component = Livewire::test(DocumentWorkbench::class, ['record' => $record])
            ->set('data.company', 'tdw')
            ->set('data.document', 'invoice-mandiri')
            ->set('data.note_invoice', "Bayar sebelum jatuh tempo\nKonfirmasi via WhatsApp");

        $html = $component->instance()->previewHtml();
        $this->assertStringContainsString('>Bayar sebelum jatuh tempo<', $html);
        $this->assertStringContainsString('>Konfirmasi via WhatsApp<', $html);

        $component->call('save')->assertHasNoErrors();
        $this->assertSame("Bayar sebelum jatuh tempo\nKonfirmasi via WhatsApp", $record->refresh()->note_invoice);

        $component->set('data.note_invoice', "satu\ndua\ntiga")->call('save')->assertHasErrors('data.note_invoice');
        $component->set('data.note_invoice', str_repeat('A', DocumentData::MAX_NOTE_LENGTH + 1))->call('save')->assertHasErrors('data.note_invoice');
    }
}
