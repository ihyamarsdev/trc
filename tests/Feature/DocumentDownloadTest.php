<?php

namespace Tests\Feature;

use App\Filament\Components\Support\DocumentData;
use App\Filament\Components\Support\DocumentTemplate;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function record(array $attributes = []): RegistrationData
    {
        $user = User::query()->first() ?? User::create(['name' => 'Finance', 'email' => 'finance@example.com', 'password' => bcrypt('password')]);
        $status = Status::query()->first() ?? Status::create(['name' => 'S', 'description' => 'd', 'color' => 'blue', 'order' => 8, 'category' => 'finance']);

        return RegistrationData::factory()->create(array_merge([
            'users_id' => $user->id,
            'status_id' => $status->id,
            'type' => 'anbk',
            'schools' => 'SMAN 1 Contoh',
            'principal' => 'Budi Santoso',
            'provinces' => 'DI Yogyakarta',
            'years' => '2026',
            'student_count' => 250,
            'price' => '100000',
            'total' => '25000000',
            'payment_name' => 'SI/TF',
            'date_register' => '2026-03-02',
            'payment_date' => '2026-04-15',
            'detail_kwitansi' => 'Pembayaran Try Out',
            'detail_invoice' => 'Try Out ANBK',
            'number_invoice' => 'INV/2026/0001',
            'qty_invoice' => '250',
            'unit_price' => '100000',
            'amount_invoice' => '25000000',
            'subtotal_invoice' => '25000000',
            'total_invoice' => '24500000',
            'pph' => '2',
            'ppn' => '0',
        ], $attributes));
    }

    private function actAsFinance(): void
    {
        Role::findOrCreate('finance');
        $user = User::create(['name' => 'Finance', 'email' => 'finance@example.com', 'password' => bcrypt('password')]);
        $user->assignRole('finance');
        $this->actingAs($user);
    }

    public function test_kwitansi_amount_in_words_capitalises_every_word(): void
    {
        $data = DocumentData::kwitansi($this->record(['total' => '29575000']));

        $this->assertSame('Dua Puluh Sembilan Juta Lima Ratus Tujuh Puluh Lima Ribu Rupiah', $data['totalSpell']);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function routes(): array
    {
        $routes = [];

        foreach (DocumentTemplate::COMPANIES as $company) {
            foreach (DocumentTemplate::documents($company) as $template) {
                $documents = match ($template) {
                    'spk' => ['anbk', 'apps', 'snbt'],
                    'kuitansi' => ['kwitansi'],
                    default => [$template],
                };

                foreach ($documents as $document) {
                    $routes["{$company}.{$document}"] = [$company, $document];
                }
            }
        }

        return $routes;
    }

    #[DataProvider('routes')]
    public function test_each_download_route_returns_a_pdf_named_after_the_school(string $company, string $document): void
    {
        $this->actAsFinance();
        $record = $this->record(['schools' => 'SMAN 1 / Contoh: "Utama"']);

        $response = $this->get(route("{$company}.{$document}.download", $record))->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('SMAN 1 Contoh Utama.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_guests_cannot_download(): void
    {
        $record = $this->record();

        $this->get(route('rasyidu.anbk.download', $record))->assertForbidden();
    }

    public function test_the_download_is_announced_to_the_user(): void
    {
        $this->actAsFinance();
        $record = $this->record();

        $this->get(route('edunesia.kwitansi.download', $record))->assertOk();

        $this->assertSame('Kwitansi Berhasil di Download', auth()->user()->notifications()->first()->data['title']);
    }

    /**
     * A name in a template that the controller never fills would print as a literal `${name}`.
     */
    #[DataProvider('templates')]
    public function test_every_placeholder_in_a_template_gets_a_value(string $company, string $document): void
    {
        $values = DocumentData::for($document, $this->record());

        $this->assertSame([], array_values(array_diff(DocumentTemplate::placeholders($company, $document), array_keys($values))));
        $this->assertStringNotContainsString('${', DocumentTemplate::html($company, $document, $values));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function templates(): array
    {
        $templates = [];

        foreach (DocumentTemplate::COMPANIES as $company) {
            foreach (DocumentTemplate::documents($company) as $document) {
                $templates["{$company}/{$document}"] = [$company, $document];
            }
        }

        return $templates;
    }

    public function test_values_are_escaped_and_unknown_placeholders_stay_visible(): void
    {
        $html = DocumentTemplate::html('rasyidu', 'kuitansi', ['schools' => 'SMA <b>"A"</b> & B', 'detail' => "baris satu\nbaris dua"]);

        $this->assertStringContainsString('SMA &lt;b&gt;&quot;A&quot;&lt;/b&gt; &amp; B', $html);
        $this->assertStringContainsString("baris satu<br>\nbaris dua", $html);
        $this->assertStringContainsString('${principal}', $html);
    }

    public function test_an_unknown_template_is_refused(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        DocumentTemplate::path('rasyidu', '../.env');
    }

    public function test_spk_uses_the_payment_label_and_numbers_in_indonesian_style(): void
    {
        $values = DocumentData::spk($this->record());

        $this->assertSame('SI / TF', $values['payment']);
        $this->assertSame('100.000', $values['harga']);
        $this->assertSame('25.000.000', $values['total']);
        $this->assertSame('250', (string) $values['siswa']);
        $this->assertSame('KEPALA SEKOLAH SMAN 1 Contoh', $values['jabatan']);
    }

    public function test_invoice_shows_tax_percentages_or_a_dash(): void
    {
        $values = DocumentData::invoice($this->record(['pph' => '2', 'ppn' => '0']));
        $this->assertSame('2%', $values['pph']);
        $this->assertSame('-', $values['ppn']);

        $values = DocumentData::invoice($this->record(['pph' => '0.5', 'ppn' => '11']));
        $this->assertSame('0,5%', $values['pph']);
        $this->assertSame('11%', $values['ppn']);

        $values = DocumentData::invoice($this->record(['pph' => null, 'ppn' => null, 'number_invoice' => null]));
        $this->assertSame('-', $values['pph']);
        $this->assertSame('-', $values['number_invoice']);
        $this->assertSame('25.000.000', $values['subtotal_invoice']);
    }

    public function test_invoice_tax_rows_carry_the_amount_on_the_subtotal_and_stay_blank_without_tax(): void
    {
        $values = DocumentData::invoice($this->record(['subtotal_invoice' => '47000000', 'ppn' => '11', 'pph' => '0']));

        $this->assertSame('PPN (11%)', $values['ppn_label']);
        $this->assertSame('5.170.000', $values['ppn_amount']);
        $this->assertSame('Rp 5.170.000', $values['ppn_rupiah']);
        $this->assertSame('', $values['pph_label']);
        $this->assertSame('-', $values['pph_amount']);
        $this->assertSame('', $values['pph_rupiah']);
        $this->assertSame('Program ANBK', $values['program']);
    }

    public function test_invoice_date_is_today_not_a_date_of_the_record(): void
    {
        $this->travelTo(Carbon::parse('2026-11-04 09:00'));

        $values = DocumentData::invoice($this->record(['date_register' => '2026-03-02', 'payment_date' => '2026-09-17']));

        $this->assertSame('04/11/2026', $values['tanggal']);
    }

    public function test_a_long_invoice_description_is_printed_smaller(): void
    {
        $short = DocumentData::invoice($this->record(['detail_invoice' => str_repeat('A', DocumentData::LONG_DETAIL)]));
        $long = DocumentData::invoice($this->record(['detail_invoice' => str_repeat('A', DocumentData::LONG_DETAIL + 1)]));

        $this->assertSame('normal', $short['detail_size']);
        $this->assertSame('small', $long['detail_size']);
    }

    public function test_kwitansi_carries_the_number_count_and_short_date(): void
    {
        $values = DocumentData::kwitansi($this->record([
            'number_kwitansi' => '055',
            'qty_invoice' => '100',
            'payment_date' => '2026-06-10',
        ]));

        $this->assertSame('055', $values['number']);
        $this->assertSame('100', $values['qty']);
        $this->assertSame('10 / 06 / 2026', $values['tanggal_pendek']);

        $values = DocumentData::kwitansi($this->record(['number_kwitansi' => null, 'qty_invoice' => null, 'real_count' => '90']));

        $this->assertSame('', $values['number']);
        $this->assertSame('90', $values['qty']);
    }

    public function test_invoice_note_lines_become_the_nb_bullets(): void
    {
        $values = DocumentData::invoice($this->record(['note_invoice' => "Bayar sebelum jatuh tempo\n\n  Konfirmasi via WhatsApp \n"]));

        $this->assertSame('Bayar sebelum jatuh tempo', $values['note_1']);
        $this->assertSame('Konfirmasi via WhatsApp', $values['note_2']);

        $values = DocumentData::invoice($this->record(['note_invoice' => null]));

        $this->assertSame('', $values['note_1']);
        $this->assertSame('', $values['note_2']);
    }
}
