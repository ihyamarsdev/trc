<?php

namespace App\Livewire;

use App\Filament\Components\Support\DocumentData;
use App\Filament\Components\Support\DocumentTemplate;
use App\Models\RegistrationData;
use Closure;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Fill in the kwitansi or invoice of a registration and see it on the chosen company's letterhead while
 * typing. Saves to the record, and downloads the same PDF the preview shows.
 */
class DocumentWorkbench extends Component implements HasForms
{
    use InteractsWithForms;

    public const ROLES = ['admin', 'finance'];

    private const COMPANIES = [
        'rasyidu' => 'Rasyidu',
        'edunesia' => 'Edunesia',
    ];

    private const DOCUMENTS = [
        'spk' => 'SPK',
        'kuitansi' => 'Kwitansi',
        'invoice' => 'Invoice',
    ];

    /**
     * Invoice tax choices: [PPh 23 %, PPN %].
     */
    private const TAXES = [
        'pph' => [2, 0],
        'ppn' => [0, 11],
        'none' => [0, 0],
    ];

    public RegistrationData $record;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(RegistrationData $record): void
    {
        abort_unless(Auth::user()?->hasRole(self::ROLES), 403);

        $this->record = $record;

        $this->form->fill([
            'company' => array_key_first(self::COMPANIES),
            'document' => 'spk',
            'detail_kwitansi' => $record->detail_kwitansi,
            'number_invoice' => $record->number_invoice,
            'detail_invoice' => $record->detail_invoice,
            'qty_invoice' => $record->qty_invoice,
            'unit_price' => $record->unit_price,
            'amount_invoice' => $record->amount_invoice,
            'subtotal_invoice' => $record->subtotal_invoice,
            'total_invoice' => $record->total_invoice,
            'tax_option' => $this->taxOptionOf($record),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Grid::make(['default' => 1, 'lg' => 5])->schema([
                    Group::make([
                        ToggleButtons::make('company')
                            ->label('Template')
                            ->options(self::COMPANIES)
                            ->icons(['rasyidu' => 'heroicon-o-building-office-2', 'edunesia' => 'heroicon-o-building-office'])
                            ->inline()
                            ->grouped()
                            ->live(),
                        ToggleButtons::make('document')
                            ->label('Dokumen')
                            ->options(self::DOCUMENTS)
                            ->icons(['spk' => 'heroicon-o-document-text', 'kuitansi' => 'heroicon-o-banknotes', 'invoice' => 'heroicon-o-receipt-percent'])
                            ->inline()
                            ->grouped()
                            ->live(),

                        $this->spkFields(),
                        $this->kwitansiFields(),
                        $this->invoiceFields(),
                    ])->columnSpan(['lg' => 2]),

                    View::make('livewire.document-preview')
                        ->viewData(fn (): array => ['html' => $this->previewHtml()])
                        ->columnSpan(['lg' => 3]),
                ]),
            ]);
    }

    /**
     * The SPK has nothing to type: everything in it comes from the school's data, so it only lists what is used.
     */
    private function spkFields(): Section
    {
        $value = fn (string $key): Closure => fn (): string => (string) (DocumentData::spk($this->record)[$key] ?? '-');

        return Section::make('SPK')
            ->icon('heroicon-o-document-text')
            ->description('Seluruh isi SPK diambil dari data sekolah dan Finance. Untuk mengubahnya, pakai tombol Ubah.')
            ->visible(fn (Get $get): bool => $get('document') === 'spk')
            ->compact()
            ->columns(2)
            ->schema([
                Placeholder::make('spk_to')->label('Pihak Pertama')->content($value('to'))->columnSpanFull(),
                Placeholder::make('spk_jabatan')->label('Jabatan')->content($value('jabatan'))->columnSpanFull(),
                Placeholder::make('spk_siswa')->label('Jumlah Siswa')->content($value('siswa')),
                Placeholder::make('spk_tanggal')->label('Tanggal Pelaksanaan')->content($value('tanggal')),
                Placeholder::make('spk_harga')->label('Harga per Siswa')->content(fn (): string => 'Rp'.DocumentData::spk($this->record)['harga']),
                Placeholder::make('spk_total')->label('Total')->content(fn (): string => 'Rp'.DocumentData::spk($this->record)['total']),
                Placeholder::make('spk_payment')->label('Pembayaran Via')->content($value('payment')),
                Placeholder::make('spk_province')->label('Provinsi')->content($value('province')),
            ]);
    }

    private function kwitansiFields(): Section
    {
        return Section::make('Kwitansi')
            ->icon('heroicon-o-banknotes')
            ->description('Sekolah, nominal, tanggal bayar, dan kepala sekolah diambil dari data Finance.')
            ->visible(fn (Get $get): bool => $get('document') === 'kuitansi')
            ->compact()
            ->schema([
                Placeholder::make('schools')
                    ->label('Sekolah')
                    ->content(fn (): string => (string) $this->record->schools),
                Textarea::make('detail_kwitansi')
                    ->label('Guna Pembayaran')
                    ->helperText('Contoh: 146 Paket Program TRY OUT Ujian Tertulis Berbasis Komputer (UTBK SNBT)')
                    ->rows(3)
                    ->maxLength(255)
                    ->live(debounce: 600),
            ]);
    }

    private function invoiceFields(): Section
    {
        return Section::make('Invoice')
            ->icon('heroicon-o-receipt-percent')
            ->visible(fn (Get $get): bool => $get('document') === 'invoice')
            ->compact()
            ->columns(2)
            ->schema([
                Placeholder::make('invoice_schools')
                    ->label('Sekolah')
                    ->content(fn (): string => (string) $this->record->schools)
                    ->columnSpanFull(),
                TextInput::make('number_invoice')
                    ->label('Nomor Invoice')
                    ->prefix('#')
                    ->numeric()
                    ->live(debounce: 600),
                TextInput::make('qty_invoice')
                    ->label('Jumlah')
                    ->numeric()
                    ->minValue(0)
                    ->live(debounce: 600)
                    ->afterStateUpdated(fn () => $this->recalculate()),
                TextInput::make('detail_invoice')
                    ->label('Deskripsi')
                    ->helperText('Contoh: Try Out Asesmen Nasional (AKM)')
                    ->maxLength(255)
                    ->live(debounce: 600)
                    ->columnSpanFull(),
                TextInput::make('unit_price')
                    ->label('Harga Satuan')
                    ->prefix('Rp')
                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                    ->live(debounce: 600)
                    ->afterStateUpdated(fn () => $this->recalculate()),
                TextInput::make('subtotal_invoice')
                    ->label('Sub Total')
                    ->prefix('Rp')
                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                    ->readOnly(),
                ToggleButtons::make('tax_option')
                    ->label('Pajak')
                    ->options(['pph' => 'PPh 23 (2%)', 'ppn' => 'PPN (11%)', 'none' => 'Tanpa pajak'])
                    ->inline()
                    ->grouped()
                    ->live()
                    ->afterStateUpdated(fn () => $this->recalculate())
                    ->columnSpanFull(),
                TextInput::make('total_invoice')
                    ->label('Total')
                    ->prefix('Rp')
                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                    ->readOnly()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Amount, subtotal, taxes and total follow from quantity, unit price and the chosen tax.
     * As before, the tax percentage is added on top of the subtotal.
     */
    private function recalculate(): void
    {
        $amount = (float) ($this->data['qty_invoice'] ?? 0) * (float) ($this->data['unit_price'] ?? 0);
        [$pph, $ppn] = self::TAXES[$this->data['tax_option'] ?? 'none'] ?? self::TAXES['none'];

        $this->data['amount_invoice'] = (string) round($amount);
        $this->data['subtotal_invoice'] = (string) round($amount);
        $this->data['total_invoice'] = (string) round($amount * (1 + ($pph + $ppn) / 100));
    }

    /**
     * Which tax choice the saved percentages correspond to; anything else (older or hand-edited values) is none.
     */
    private function taxOptionOf(RegistrationData $record): ?string
    {
        foreach (self::TAXES as $option => [$pph, $ppn]) {
            if ((float) $record->pph === (float) $pph && (float) $record->ppn === (float) $ppn && ($pph + $ppn) > 0) {
                return $option;
            }
        }

        return ((float) $record->pph + (float) $record->ppn) === 0.0 ? 'none' : null;
    }

    /**
     * The values of the form as they will be stored on the record.
     *
     * @return array<string, scalar|null>
     */
    private function documentValues(): array
    {
        [$pph, $ppn] = self::TAXES[$this->data['tax_option'] ?? 'none'] ?? [(float) $this->record->pph, (float) $this->record->ppn];

        return [
            'detail_kwitansi' => $this->upper($this->data['detail_kwitansi'] ?? null),
            'number_invoice' => $this->data['number_invoice'] ?? null,
            'detail_invoice' => $this->upper($this->data['detail_invoice'] ?? null),
            'qty_invoice' => $this->data['qty_invoice'] ?? null,
            'unit_price' => $this->data['unit_price'] ?? null,
            'amount_invoice' => $this->data['amount_invoice'] ?? null,
            'subtotal_invoice' => $this->data['subtotal_invoice'] ?? null,
            'total_invoice' => $this->data['total_invoice'] ?? null,
            'pph' => (string) $pph,
            'ppn' => (string) $ppn,
        ];
    }

    private function upper(?string $value): ?string
    {
        return filled($value) ? Str::upper($value) : null;
    }

    /**
     * The template filled with what is currently typed, ready for the preview frame.
     */
    public function previewHtml(): string
    {
        $company = $this->chosenCompany();
        $document = $this->chosenDocument();

        $draft = $this->record->replicate()->fill($this->documentValues());
        $html = DocumentTemplate::html($company, $document, DocumentData::for($document, $draft));

        // the template refers to its images relative to its own folder
        return str_replace('<head>', '<head><base href="'.e(asset("template/{$company}")).'/">', $html);
    }

    private function chosenCompany(): string
    {
        return array_key_exists($this->data['company'] ?? '', self::COMPANIES) ? $this->data['company'] : array_key_first(self::COMPANIES);
    }

    private function chosenDocument(): string
    {
        return array_key_exists($this->data['document'] ?? '', self::DOCUMENTS) ? $this->data['document'] : array_key_first(self::DOCUMENTS);
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->hasRole(self::ROLES), 403);

        $this->form->getState();

        $this->record->fill($this->documentValues())->save();

        Notification::make()
            ->title('Data kwitansi dan invoice tersimpan')
            ->success()
            ->send();
    }

    /**
     * Saves what was typed (kwitansi and invoice only; an SPK has nothing to save) and starts the PDF download.
     */
    public function download(): void
    {
        $document = $this->chosenDocument();

        if ($document !== 'spk') {
            $this->save();
        }

        $company = $this->chosenCompany();
        $route = match ($document) {
            'invoice' => 'invoice',
            'kuitansi' => 'kwitansi',
            default => $this->spkRouteSegment(),
        };
        $url = route("{$company}.{$route}.download", $this->record);

        $this->js('(() => { const link = document.createElement("a"); link.href = '.json_encode($url, JSON_UNESCAPED_SLASHES).'; link.download = ""; document.body.appendChild(link); link.click(); link.remove(); })()');
    }

    /**
     * SPK downloads are routed per program; programs without their own route use the ANBK one.
     */
    private function spkRouteSegment(): string
    {
        $type = Str::lower((string) $this->record->type);

        return in_array($type, ['anbk', 'apps', 'snbt'], true) ? $type : 'anbk';
    }

    public function render(): ViewContract
    {
        return view('livewire.document-workbench');
    }
}
