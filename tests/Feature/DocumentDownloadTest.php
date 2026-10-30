<?php

namespace Tests\Feature;

use App\Filament\Components\Support\DocumentData;
use App\Filament\Components\Support\DocumentTemplate;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function routes(): array
    {
        $routes = [];

        foreach (DocumentTemplate::COMPANIES as $company) {
            foreach (['anbk', 'apps', 'snbt', 'kwitansi', 'invoice'] as $document) {
                $routes["{$company}.{$document}"] = [$company, $document];
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
            foreach (DocumentTemplate::DOCUMENTS as $document) {
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
}
