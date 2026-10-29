<?php

namespace App\Filament\Components\Support;

use App\Filament\Enum\Program;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Model;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

/**
 * Shared building blocks for the Admin, Salesforce, Service and Finance view pages,
 * so every role sees the same layout and only the included tabs differ.
 */
class InfolistSchema
{
    public const PART_SERVICE = 'service';

    public const PART_FINANCE = 'finance';

    public const PART_DOCUMENTS = 'documents';

    private const EMPTY = 'Belum Terisi';

    private const DATE_FORMAT = 'l, jS F Y';

    /**
     * Header card followed by the tabs for the requested parts.
     * The school tab is always present; without extra parts its sections are shown directly.
     *
     * @param  list<string>  $parts  any of the PART_* constants
     * @return array<int, Component>
     */
    public static function compose(Model $record, array $parts = [], ?Action $statusAction = null): array
    {
        $schema = [self::header($record, $statusAction)];

        if ($parts === []) {
            return [...$schema, ...self::schoolSections()];
        }

        $tabs = [
            Tab::make('Sekolah')
                ->icon('heroicon-o-building-library')
                ->schema(self::schoolSections()),
        ];

        if (in_array(self::PART_SERVICE, $parts, true)) {
            $tabs[] = Tab::make('Service')
                ->icon('heroicon-o-wrench-screwdriver')
                ->schema(self::serviceSections());
        }

        if (in_array(self::PART_FINANCE, $parts, true)) {
            $tabs[] = Tab::make('Finance')
                ->icon('heroicon-o-banknotes')
                ->schema(self::financeSections());
        }

        if (in_array(self::PART_DOCUMENTS, $parts, true)) {
            $tabs[] = Tab::make('Kwitansi & Invoice')
                ->icon('heroicon-o-document-text')
                ->schema(self::documentSections());
        }

        return [...$schema, Tabs::make('Detail')->tabs($tabs)->persistTabInQueryString()->columnSpanFull()];
    }

    public static function header(Model $record, ?Action $statusAction = null): Section
    {
        $program = Program::getMetadata($record->type, 'none');

        $status = TextEntry::make('status.name')
            ->hiddenLabel()
            ->badge()
            ->size(TextEntrySize::Large)
            ->color(fn (Model $record): string => StatusPalette::color($record->latestStatusLog?->status?->order))
            ->icon(fn (Model $record): string => StatusPalette::icon($record->latestStatusLog?->status?->order))
            ->placeholder('Tidak Ada Status');

        if ($statusAction !== null) {
            $status->action($statusAction)->tooltip('Lihat riwayat activity');
        }

        return Section::make()
            ->schema([
                Split::make([
                    Group::make([
                        TextEntry::make('schools')
                            ->hiddenLabel()
                            ->size(TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->placeholder('Nama Sekolah Belum Terisi'),
                        Split::make([
                            TextEntry::make('type')
                                ->hiddenLabel()
                                ->badge()
                                ->color('primary')
                                ->icon('heroicon-m-academic-cap')
                                ->formatStateUsing(fn (): string => $program['nameRegister'])
                                ->tooltip($program['DescriptionRegister'])
                                ->grow(false),
                            self::badge('periode')->grow(false),
                            self::badge('years')->icon('heroicon-m-calendar')->grow(false),
                            self::badge('education_level')->grow(false),
                            self::badge('schools_type')->grow(false),
                        ]),
                    ]),
                    Group::make([$status])->grow(false),
                ])->from('md'),

                Grid::make(['default' => 1, 'sm' => 2, 'xl' => 4])
                    ->schema([
                        TextEntry::make('users.name')
                            ->label('Salesforce')
                            ->icon('heroicon-m-user-circle')
                            ->placeholder(self::EMPTY),
                        TextEntry::make('student_count')
                            ->label('Jumlah Siswa')
                            ->icon('heroicon-m-users')
                            ->placeholder(self::EMPTY),
                        TextEntry::make('date_register')
                            ->label('Tanggal Pendaftaran')
                            ->icon('heroicon-m-calendar-days')
                            ->dateTime(self::DATE_FORMAT)
                            ->placeholder(self::EMPTY),
                        TextEntry::make('implementation_estimate')
                            ->label('Estimasi Pelaksanaan')
                            ->icon('heroicon-m-clock')
                            ->dateTime(self::DATE_FORMAT)
                            ->placeholder(self::EMPTY),
                    ]),
            ]);
    }

    /**
     * @return array<int, Component>
     */
    public static function schoolSections(): array
    {
        return [
            Section::make('Data Sekolah')
                ->icon('heroicon-o-building-library')
                ->columns(['default' => 1, 'sm' => 2, 'xl' => 4])
                ->schema([
                    self::text('education_level', 'Jenjang'),
                    self::text('class', 'Kelas'),
                    self::text('schools_type', 'Negeri / Swasta'),
                    self::text('description', 'Keterangan'),
                    self::text('provinces', 'Provinsi'),
                    self::text('regencies', 'Kota / Kabupaten'),
                    self::text('area', 'Wilayah')->columnSpan(['xl' => 2]),
                ]),

            Section::make('Kontak Sekolah')
                ->icon('heroicon-o-user-group')
                ->columns(['default' => 1, 'lg' => 2])
                ->schema([
                    self::contact('Kepala Sekolah', 'principal'),
                    self::contact('Wakakurikulum', 'curriculum_deputies'),
                    self::contact('Koordinator BK', 'counselor_coordinators'),
                    self::contact('Proktor', 'proctors'),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function serviceSections(): array
    {
        return [
            Section::make('Akun Siswa')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([
                    self::stat('account_count_created', 'Akun Dibuat', 'Belum Terbuat'),
                    self::stat('implementer_count', 'Pelaksanaan', 'Belum Terbuat'),
                    self::stat('difference', 'Selisih', 'Belum Terbuat'),
                ]),

            Section::make('Jadwal')
                ->icon('heroicon-o-calendar-days')
                ->columns(['default' => 1, 'sm' => 2, 'xl' => 4])
                ->schema([
                    self::date('group', 'Grup'),
                    self::date('bimtek', 'Bimtek'),
                    self::date('counselor_consultation_date', 'Konsul BK'),
                    self::date('student_consultation_date', 'Konsul Siswa'),
                ]),

            Section::make('Konsultasi')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->columns(3)
                ->schema([
                    self::yesNo('students_download', 'Download Siswa'),
                    self::yesNo('schools_download', 'Download Sekolah'),
                    self::yesNo('pm', 'PM'),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function financeSections(): array
    {
        return [
            Section::make('Opsi Perhitungan')
                ->icon('heroicon-o-calculator')
                ->columns(2)
                ->schema([
                    TextEntry::make('option_price')
                        ->label('Dasar Jumlah')
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing(fn (?string $state): string => self::optionLabel($state))
                        ->placeholder('Belum di Pilih'),
                    TextEntry::make('real_count')
                        ->label('Jumlah Siswa Real')
                        ->placeholder(self::EMPTY)
                        ->visible(fn (Model $record): bool => $record->option_price === 'real'),
                ]),

            Section::make('Invoice')
                ->icon('heroicon-o-receipt-percent')
                ->columns(2)
                ->schema([
                    self::date('invoice_date', 'Tanggal Invoice'),
                    self::money('invoice_amount', 'Nominal Invoice'),
                ]),

            Section::make('Nominal')
                ->icon('heroicon-o-banknotes')
                ->columns(['default' => 1, 'sm' => 3])
                ->schema([
                    self::money('price', 'Harga SPJ'),
                    self::money('support_school', 'SS (Support Sekolah)'),
                    self::money('net_2', 'Harga NET'),
                ]),

            Section::make('Pajak')
                ->icon('heroicon-o-scale')
                ->columns(2)
                ->schema([
                    TextEntry::make('tax_rates')
                        ->label('Pilihan Pajak')
                        ->badge()
                        ->state(fn (Model $record): array => SharedSchema::taxRateLabels($record->tax_rates, $record->tax_custom_rate))
                        ->placeholder('Tanpa Pajak'),
                    self::money('tax_amount', 'Nominal Pajak'),
                ]),

            Section::make('Total')
                ->icon('heroicon-o-currency-dollar')
                ->columns(2)
                ->schema([
                    self::money('total', 'Total Dana Sesuai SPJ')
                        ->size(TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->color('primary'),
                    self::money('total_net', 'Total Net')
                        ->size(TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->color('primary'),
                ]),

            Section::make('Pembayaran')
                ->icon('heroicon-o-credit-card')
                ->columns(['default' => 1, 'sm' => 3])
                ->schema([
                    self::date('payment_date', 'Jadwal Pembayaran'),
                    self::date('spk', 'SPK Dikirim'),
                    TextEntry::make('payment_name')
                        ->label('Pembayaran Via')
                        ->badge()
                        ->color('gray')
                        ->placeholder(self::EMPTY),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function documentSections(): array
    {
        return [
            Section::make('Kwitansi')
                ->description('Lakukan Edit untuk merubah Kwitansi')
                ->icon('heroicon-o-document-text')
                ->columns(['default' => 1, 'lg' => 3])
                ->schema([
                    self::text('schools', 'Telah Terima Dari'),
                    self::money('total', 'Uang Sejumlah'),
                    self::text('detail_kwitansi', 'Guna Pembayaran'),
                ]),

            Section::make('Invoice')
                ->description('Lakukan Edit untuk merubah Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->columns(['default' => 1, 'sm' => 2, 'xl' => 4])
                ->schema([
                    self::text('schools', 'Bill To'),
                    self::text('number_invoice', 'Nomor Invoice'),
                    self::text('detail_invoice', 'Deskripsi')->columnSpan(['xl' => 2]),
                    self::text('qty_invoice', 'Kuantitas'),
                    self::money('unit_price', 'Harga Per Unit'),
                    self::money('amount_invoice', 'Jumlah'),
                    self::money('subtotal_invoice', 'Sub Total'),
                    self::percent('pph', 'PPH 23'),
                    self::percent('ppn', 'PPN'),
                    self::money('total_invoice', 'Total')
                        ->size(TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->color('primary')
                        ->columnSpan(['xl' => 2]),
                ]),
        ];
    }

    private static function text(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)->label($label)->placeholder(self::EMPTY);
    }

    private static function stat(string $name, string $label, string $placeholder): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->size(TextEntrySize::Large)
            ->weight(FontWeight::Bold)
            ->placeholder($placeholder);
    }

    private static function date(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->icon('heroicon-m-calendar-days')
            ->dateTime(self::DATE_FORMAT)
            ->placeholder('Belum Terjadwal');
    }

    private static function money(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)->label($label)->money('IDR')->placeholder(self::EMPTY);
    }

    private static function percent(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->formatStateUsing(fn (mixed $state): string => self::formatPercent($state))
            ->placeholder(self::EMPTY);
    }

    private static function badge(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->hiddenLabel()
            ->badge()
            ->color('gray')
            ->visible(fn (Model $record): bool => filled($record->{$name}));
    }

    private static function yesNo(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->badge()
            ->color(fn (?string $state): string => match ($state) {
                'YA' => 'success',
                'TIDAK' => 'danger',
                default => 'gray',
            })
            ->icon(fn (?string $state): ?string => match ($state) {
                'YA' => 'heroicon-m-check-circle',
                'TIDAK' => 'heroicon-m-x-circle',
                default => null,
            })
            ->placeholder('Tidak Ada Status');
    }

    private static function contact(string $label, string $name): Group
    {
        return Group::make([
            self::text($name, $label)->icon('heroicon-m-user'),
            PhoneEntry::make($name.'_phone')
                ->label('No Hp '.$label)
                ->icon('heroicon-m-phone')
                ->displayFormat(PhoneInputNumberType::NATIONAL)
                ->placeholder(self::EMPTY),
        ]);
    }

    /**
     * `implementer_12` / `account_7` keep the count in the key; `real` uses the manual real count.
     */
    private static function optionLabel(?string $state): string
    {
        return match (true) {
            $state === 'real' => 'Jumlah Real',
            str_starts_with((string) $state, 'implementer_') => 'Jumlah Pelaksanaan ('.substr($state, 12).')',
            str_starts_with((string) $state, 'account_') => 'Jumlah Akun ('.substr($state, 8).')',
            default => (string) $state,
        };
    }

    /**
     * 2 and 0.02 both mean 2%; trailing zeros are dropped.
     */
    private static function formatPercent(mixed $state): string
    {
        $value = is_numeric($state) && (float) $state > 0 && (float) $state < 1
            ? (float) $state * 100
            : (float) $state;

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').'%';
    }
}
