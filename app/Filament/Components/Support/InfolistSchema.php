<?php

namespace App\Filament\Components\Support;

use App\Filament\Enum\OptionGroup;
use App\Models\Option;
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
use Illuminate\Support\HtmlString;
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

    private const DATE_FORMAT = 'j F Y';

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
            self::tab('Sekolah', 'heroicon-o-building-library', self::schoolSections()),
        ];

        if (in_array(self::PART_SERVICE, $parts, true)) {
            $tabs[] = self::tab('Service', 'heroicon-o-wrench-screwdriver', self::serviceSections());
        }

        if (in_array(self::PART_FINANCE, $parts, true)) {
            $tabs[] = self::tab('Finance', 'heroicon-o-banknotes', self::financeSections());
        }

        if (in_array(self::PART_DOCUMENTS, $parts, true)) {
            $tabs[] = self::tab('Kwitansi & Invoice', 'heroicon-o-document-text', self::documentSections());
        }

        return [...$schema, Tabs::make('Detail')->tabs($tabs)->persistTabInQueryString()->columnSpanFull()];
    }

    /**
     * Icon-only on phones (four labelled tabs do not fit), icon + label from `sm` up.
     *
     * @param  array<int, Component>  $schema
     */
    private static function tab(string $label, string $icon, array $schema): Tab
    {
        return Tab::make($label)
            ->icon($icon)
            ->label(new HtmlString('<span class="sr-only sm:hidden">'.e($label).'</span><span class="hidden sm:block">'.e($label).'</span>'))
            ->extraAttributes(['style' => 'padding: 0.75rem;'])
            ->schema($schema);
    }

    public static function header(Model $record, ?Action $statusAction = null): Section
    {
        $program = Option::programMeta($record->type, 'none');

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
                        TextEntry::make('badges')
                            ->hiddenLabel()
                            ->badge()
                            ->state(fn (Model $record): array => array_values(array_filter([
                                filled($record->type) ? $program['nameRegister'] : null,
                                self::choiceLabel(OptionGroup::Periode, $record->periode),
                                $record->years,
                                self::choiceLabel(OptionGroup::Jenjang, $record->education_level),
                                self::choiceLabel(OptionGroup::SchoolType, $record->schools_type),
                            ])))
                            ->color(fn (string $state): string => $state === $program['nameRegister'] ? 'primary' : 'gray'),
                    ]),
                    Group::make([$status])->grow(false),
                ])->from('md'),

                Grid::make(['default' => 2, 'xl' => 4])
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
                ->compact()
                ->columns(['default' => 2, 'xl' => 4])
                ->schema([
                    self::optionText('education_level', 'Jenjang', OptionGroup::Jenjang),
                    self::text('class', 'Kelas'),
                    self::optionText('schools_type', 'Negeri / Swasta', OptionGroup::SchoolType),
                    self::optionText('description', 'Keterangan', OptionGroup::Keterangan),
                    self::text('provinces', 'Provinsi'),
                    self::text('regencies', 'Kota / Kabupaten'),
                    self::text('area', 'Wilayah')->columnSpan(['default' => 2, 'xl' => 2]),
                ]),

            Section::make('Kontak Sekolah')
                ->icon('heroicon-o-user-group')
                ->compact()
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
                ->compact()
                ->columns(['default' => 3])
                ->schema([
                    self::stat('account_count_created', 'Akun Dibuat', 'Belum Terbuat'),
                    self::stat('implementer_count', 'Pelaksanaan', 'Belum Terbuat'),
                    self::stat('difference', 'Selisih', 'Belum Terbuat'),
                ]),

            Section::make('Jadwal')
                ->icon('heroicon-o-calendar-days')
                ->compact()
                ->columns(['default' => 2, 'xl' => 4])
                ->schema([
                    self::date('group', 'Grup'),
                    self::date('bimtek', 'Bimtek'),
                    self::date('counselor_consultation_date', 'Konsul BK'),
                    self::date('student_consultation_date', 'Konsul Siswa'),
                ]),

            Section::make('Konsultasi')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->compact()
                ->columns(['default' => 3])
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
                ->compact()
                ->columns(['default' => 2])
                ->schema([
                    TextEntry::make('option_price')
                        ->label('Dasar Jumlah')
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing(fn (?string $state): string => self::optionLabel($state))
                        ->placeholder('Belum di Pilih')
                        ->columnSpan(['default' => 2, 'sm' => 1]),
                    TextEntry::make('real_count')
                        ->label('Jumlah Siswa Real')
                        ->placeholder(self::EMPTY)
                        ->visible(fn (Model $record): bool => $record->option_price === 'real'),
                ]),

            Section::make('Invoice')
                ->icon('heroicon-o-receipt-percent')
                ->compact()
                ->columns(['default' => 2])
                ->schema([
                    self::date('invoice_date', 'Tanggal Invoice'),
                    self::money('invoice_amount', 'Nominal Invoice'),
                ]),

            Section::make('Nominal')
                ->icon('heroicon-o-banknotes')
                ->compact()
                ->columns(['default' => 2, 'sm' => 3])
                ->schema([
                    self::money('price', 'Harga SPJ'),
                    self::money('support_school', 'SS (Support Sekolah)'),
                    self::money('net_2', 'Harga NET'),
                ]),

            Section::make('Pajak')
                ->icon('heroicon-o-scale')
                ->compact()
                ->columns(['default' => 2])
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
                ->compact()
                ->columns(['default' => 1])
                ->schema([
                    self::optionText('payment_name', 'Pembayaran Via', OptionGroup::PaymentVia)
                        ->badge()
                        ->color('gray'),
                    self::money('total', 'Total Dana Sesuai SPJ'),
                    Grid::make(['default' => 1, 'sm' => 2])
                        ->schema([
                            self::money('total_received', 'Total Uang Masuk Rekening'),
                            self::optionText('bank_name', 'Bank', OptionGroup::Bank)
                                ->badge()
                                ->color('gray'),
                        ]),
                    self::money('total_support_school', 'Total SS'),
                    self::money('total_net', 'Total Net')
                        ->size(TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->color('primary'),
                ]),

            Section::make('Pembayaran')
                ->icon('heroicon-o-credit-card')
                ->compact()
                ->columns(['default' => 2])
                ->schema([
                    self::date('payment_date', 'Jadwal Pembayaran'),
                    self::date('spk', 'SPK Dikirim'),
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
                ->compact()
                ->columns(['default' => 1, 'lg' => 3])
                ->schema([
                    self::text('schools', 'Telah Terima Dari'),
                    self::money('total', 'Uang Sejumlah'),
                    self::text('detail_kwitansi', 'Guna Pembayaran'),
                ]),

            Section::make('Invoice')
                ->description('Lakukan Edit untuk merubah Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->compact()
                ->columns(['default' => 2, 'xl' => 4])
                ->schema([
                    self::text('schools', 'Bill To')->columnSpan(['default' => 2, 'xl' => 1]),
                    self::text('number_invoice', 'Nomor Invoice')->columnSpan(['default' => 2, 'xl' => 1]),
                    self::text('detail_invoice', 'Deskripsi')->columnSpan(['default' => 2, 'xl' => 2]),
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
                        ->columnSpan(['default' => 2, 'xl' => 2]),
                ]),
        ];
    }

    private static function text(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)->label($label)->placeholder(self::EMPTY);
    }

    /**
     * Text entry that shows the label of the stored choice instead of its raw value.
     */
    private static function optionText(string $name, string $label, OptionGroup $group): TextEntry
    {
        return self::text($name, $label)
            ->formatStateUsing(fn (?string $state): ?string => self::choiceLabel($group, $state));
    }

    private static function choiceLabel(OptionGroup $group, ?string $value): ?string
    {
        return Option::label($group, $value) ?? $value;
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

    private static function contact(string $label, string $name): Grid
    {
        return Grid::make(['default' => 2])->schema([
            self::text($name, $label)->icon('heroicon-m-user'),
            PhoneEntry::make($name.'_phone')
                ->label('No Hp')
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
