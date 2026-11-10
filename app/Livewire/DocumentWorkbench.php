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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
        'edusindo' => 'Edusindo',
        'tdw' => 'TDW',
    ];

    private const DOCUMENTS = [
        'spk' => 'SPK ANBK',
        'spk-apps' => 'SPK APPS',
        'spk-snbt' => 'SPK SNBT',
        'spk-tka' => 'SPK TKA',
        'kuitansi' => 'Kwitansi',
        'invoice' => 'Invoice',
        'invoice-mandiri' => 'Invoice Mandiri',
        'invoice-dki' => 'Invoice Bank DKI',
    ];

    /**
     * Invoice taxes, each a percentage added on top of the subtotal; any combination may be chosen.
     */
    private const TAXES = [
        'pph' => 2,
        'ppn' => 11,
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
            'document' => $this->defaultDocument(array_key_first(self::COMPANIES), 'spk'),
            'detail_kwitansi' => $record->detail_kwitansi,
            'number_spk' => $record->number_spk,
            'number_kwitansi' => $record->number_kwitansi,
            'number_invoice' => $record->number_invoice,
            'detail_invoice' => $record->detail_invoice,
            'note_invoice' => $record->note_invoice,
            'qty_invoice' => $record->qty_invoice,
            'unit_price' => $record->unit_price,
            'amount_invoice' => $record->amount_invoice,
            'subtotal_invoice' => $record->subtotal_invoice,
            'total_invoice' => $record->total_invoice,
            'taxes' => $taxes = $this->taxesOf($record),
            'manual_total' => filled($record->total_invoice)
                && (float) $record->total_invoice !== (float) $this->taxedTotal((float) $record->qty_invoice * (float) $record->unit_price, $taxes),
        ]);
    }

    /**
     * The document of a kind to open for a company: the one made for the record's program when there is one
     * (`spk-tka` for a TKA record), else the company's first of that kind, else the kwitansi.
     */
    private function defaultDocument(string $company, string $kind): string
    {
        $documents = DocumentTemplate::documents($company);
        $preferred = "{$kind}-".Str::lower((string) $this->record->type);

        return in_array($preferred, $documents, true)
            ? $preferred
            : (collect($documents)->first(fn (string $document): bool => DocumentTemplate::kind($document) === $kind) ?? 'kuitansi');
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
                            ->icons(['rasyidu' => 'heroicon-o-building-office-2', 'edunesia' => 'heroicon-o-building-office', 'edusindo' => 'heroicon-o-building-library', 'tdw' => 'heroicon-o-building-storefront'])
                            ->inline()
                            ->grouped()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state, Get $get): void {
                                $documents = DocumentTemplate::documents((string) $state);

                                if (! in_array($get('document'), $documents, true)) {
                                    $set('document', $this->defaultDocument((string) $state, DocumentTemplate::kind((string) $get('document'))));
                                }
                            }),
                        Select::make('document')
                            ->label('Dokumen')
                            ->options(fn (Get $get): array => array_intersect_key(self::DOCUMENTS, array_flip(DocumentTemplate::documents((string) $get('company')))))
                            ->selectablePlaceholder(false)
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
     * The SPK is filled from the school's data; only its number is typed. The rest is listed so it can be checked.
     */
    private function spkFields(): Section
    {
        $value = fn (string $key): Closure => fn (): string => (string) (DocumentData::spk($this->record)[$key] ?? '-');

        return Section::make('SPK')
            ->icon('heroicon-o-document-text')
            ->description('Isi SPK diambil dari data sekolah dan Finance; hanya nomor yang diisi di sini. Bulan romawi mengikuti bulan pendaftaran.')
            ->visible(fn (Get $get): bool => DocumentTemplate::kind((string) $get('document')) === 'spk')
            ->compact()
            ->columns(2)
            ->schema([
                TextInput::make('number_spk')
                    ->label('Nomor SPK')
                    ->helperText('Hanya angka urut, contoh: 505. Tercetak sebagai 505/APPS/IX/2026.')
                    ->maxLength(50)
                    ->live(debounce: 600)
                    ->columnSpanFull(),
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
                TextInput::make('number_kwitansi')
                    ->label('Nomor Kwitansi')
                    ->maxLength(50)
                    ->live(debounce: 600),
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
            ->visible(fn (Get $get): bool => DocumentTemplate::kind((string) $get('document')) === 'invoice')
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
                    ->helperText('Contoh: Try Out Asesmen Nasional (AKM). Maksimal '.DocumentData::MAX_DETAIL.' karakter agar muat di invoice.')
                    ->maxLength(DocumentData::MAX_DETAIL)
                    ->live(debounce: 600)
                    ->columnSpanFull(),
                Textarea::make('note_invoice')
                    ->label('Catatan (NB)')
                    ->helperText('Tercetak di invoice Mandiri. Satu baris satu poin, maksimal '.DocumentData::MAX_NOTE_LINES.' poin dan '.DocumentData::MAX_NOTE_LENGTH.' karakter per baris.')
                    ->visible(fn (Get $get): bool => $get('document') === 'invoice-mandiri')
                    ->rows(2)
                    ->rules([fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                        $lines = DocumentData::noteLines($value);

                        if (count($lines) > DocumentData::MAX_NOTE_LINES || collect($lines)->contains(fn (string $line): bool => Str::length($line) > DocumentData::MAX_NOTE_LENGTH)) {
                            $fail('Catatan maksimal '.DocumentData::MAX_NOTE_LINES.' baris dengan '.DocumentData::MAX_NOTE_LENGTH.' karakter per baris.');
                        }
                    }])
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
                ToggleButtons::make('taxes')
                    ->label('Pajak')
                    ->options(['pph' => 'PPh 23 (2%)', 'ppn' => 'PPN (11%)'])
                    ->helperText('Boleh dipilih lebih dari satu, atau dikosongkan bila tanpa pajak.')
                    ->multiple()
                    ->inline()
                    ->live()
                    ->afterStateUpdated(fn () => $this->recalculate())
                    ->columnSpanFull(),
                Toggle::make('manual_total')
                    ->label('Isi Total Manual')
                    ->helperText('Aktifkan bila total berbeda dari Sub Total + pajak.')
                    ->live()
                    ->afterStateUpdated(fn () => $this->recalculate())
                    ->columnSpanFull(),
                TextInput::make('total_invoice')
                    ->label('Total')
                    ->prefix('Rp')
                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                    ->readOnly(fn (Get $get): bool => ! $get('manual_total'))
                    ->live(debounce: 600)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Amount, subtotal, taxes and total follow from quantity, unit price and the chosen taxes.
     * As before, the tax percentages are added on top of the subtotal.
     */
    private function recalculate(): void
    {
        $amount = (float) ($this->data['qty_invoice'] ?? 0) * (float) ($this->data['unit_price'] ?? 0);

        $this->data['amount_invoice'] = (string) round($amount);
        $this->data['subtotal_invoice'] = (string) round($amount);

        if (! ($this->data['manual_total'] ?? false)) {
            $this->data['total_invoice'] = $this->taxedTotal($amount, $this->data['taxes'] ?? []);
        }
    }

    /**
     * @param  list<string>  $taxes
     */
    private function taxedTotal(float $subtotal, array $taxes): string
    {
        $percent = array_sum(array_intersect_key(self::TAXES, array_flip($taxes)));

        return (string) round($subtotal * (1 + $percent / 100));
    }

    private function taxPercent(string $tax): int
    {
        return in_array($tax, $this->data['taxes'] ?? [], true) ? self::TAXES[$tax] : 0;
    }

    /**
     * The taxes the saved percentages correspond to; other (older or hand-edited) percentages are not offered.
     *
     * @return list<string>
     */
    private function taxesOf(RegistrationData $record): array
    {
        return array_keys(array_filter(self::TAXES, fn (int $percent, string $tax): bool => (float) $record->{$tax} === (float) $percent, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * The values of the form as they will be stored on the record.
     *
     * @return array<string, scalar|null>
     */
    private function documentValues(): array
    {
        [$pph, $ppn] = [$this->taxPercent('pph'), $this->taxPercent('ppn')];

        return [
            'detail_kwitansi' => $this->upper($this->data['detail_kwitansi'] ?? null),
            'number_spk' => $this->data['number_spk'] ?? null,
            'number_kwitansi' => $this->data['number_kwitansi'] ?? null,
            'number_invoice' => $this->data['number_invoice'] ?? null,
            'detail_invoice' => $this->upper($this->data['detail_invoice'] ?? null),
            'note_invoice' => implode("\n", DocumentData::noteLines($this->data['note_invoice'] ?? null)) ?: null,
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

        // a browser frame ignores the template's @page margin, so show it as padding; the PDF keeps the real margin
        $margin = preg_match('/@page\s*\{[^}]*?margin:\s*([^;}]+)/', $html, $page) ? trim($page[1]) : '0';
        $padding = (float) $margin > 0 ? "<style>body{margin:0;padding:{$margin};box-sizing:border-box}</style>" : '';

        // the template refers to its images relative to its own folder
        $html = str_replace('<head>', '<head><base href="'.e(asset("template/{$company}")).'/">', $html);

        return str_replace('</head>', $padding.'</head>', $html);
    }

    private function chosenCompany(): string
    {
        return array_key_exists($this->data['company'] ?? '', self::COMPANIES) ? $this->data['company'] : array_key_first(self::COMPANIES);
    }

    private function chosenDocument(): string
    {
        $documents = DocumentTemplate::documents($this->chosenCompany());

        return in_array($this->data['document'] ?? '', $documents, true) ? $this->data['document'] : $documents[0];
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->hasRole(self::ROLES), 403);

        $this->form->getState();

        $this->record->fill($this->documentValues())->save();

        Notification::make()
            ->title('Data dokumen tersimpan')
            ->success()
            ->send();
    }

    /**
     * Saves what was typed and starts the PDF download.
     */
    public function download(): void
    {
        $document = $this->chosenDocument();

        $this->save();

        $company = $this->chosenCompany();
        $route = match ($document) {
            'kuitansi' => 'kwitansi',
            'spk' => $this->spkRouteSegment(),
            default => $document,
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
