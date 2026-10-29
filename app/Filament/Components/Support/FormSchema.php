<?php

namespace App\Filament\Components\Support;

use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Form sections shared by the Admin, Service and Finance resources, so the same
 * data is edited with the same layout whichever role opens the form.
 */
class FormSchema
{
    private const TIME_STEP_MINUTES = 15;

    /**
     * A date + time-of-day input that stores a single datetime attribute.
     * The date is picked on a calendar and the time from a dropdown (15 minute steps),
     * so nobody has to type hours and minutes. Only `$name` is saved; the two
     * `{$name}_date` / `{$name}_time` parts are virtual.
     */
    public static function dateTime(
        string $name,
        string|Htmlable $dateLabel,
        string|Htmlable $timeLabel = 'Jam',
        string $defaultTime = '08:00',
    ): Grid {
        $dateKey = $name.'_date';
        $timeKey = $name.'_time';

        $sync = function (Get $get, Set $set) use ($name, $dateKey, $timeKey, $defaultTime): void {
            $date = $get($dateKey);

            if (blank($date)) {
                $set($name, null);

                return;
            }

            $time = $get($timeKey) ?: $defaultTime;

            $set($timeKey, $time);
            $set($name, $date.' '.$time.':00');
        };

        return Grid::make(['default' => 3])
            ->schema([
                Hidden::make($name)
                    ->afterStateHydrated(fn (Hidden $component, mixed $state) => $component->state(self::formatPart($state, 'Y-m-d H:i:s'))),
                DatePicker::make($dateKey)
                    ->label($dateLabel)
                    ->native(false)
                    ->displayFormat(self::DATE_FORMAT)
                    ->dehydrated(false)
                    ->live()
                    ->afterStateHydrated(fn (DatePicker $component, Get $get) => $component->state(self::formatPart($get($name), 'Y-m-d')))
                    ->afterStateUpdated($sync)
                    ->columnSpan(2),
                Select::make($timeKey)
                    ->label($timeLabel)
                    ->native(false)
                    ->searchable()
                    ->placeholder('Pilih jam')
                    ->prefixIcon('heroicon-m-clock')
                    ->options(fn (Get $get): array => self::timeOptions($get($timeKey)))
                    ->dehydrated(false)
                    ->live()
                    ->afterStateHydrated(fn (Select $component, Get $get) => $component->state(self::formatPart($get($name), 'H:i')))
                    ->afterStateUpdated($sync),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function timeOptions(?string $current = null): array
    {
        $options = [];

        for ($minute = 0; $minute < 1440; $minute += self::TIME_STEP_MINUTES) {
            $time = sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
            $options[$time] = $time;
        }

        if (filled($current) && ! isset($options[$current])) {
            $options[$current] = $current;
            ksort($options);
        }

        return $options;
    }

    private static function formatPart(mixed $value, string $format): ?string
    {
        return filled($value)
            ? Carbon::parse($value)->setTimezone(config('app.timezone'))->format($format)
            : null;
    }

    private const DATE_FORMAT = 'l, jS F Y';

    /**
     * Account counts, schedule and consultation fields owned by the Service role.
     *
     * @return array<int, Component>
     */
    public static function serviceSections(): array
    {
        return [
            Section::make('Akun Siswa')
                ->icon('heroicon-o-identification')
                ->columns(['default' => 1, 'sm' => 3])
                ->schema([
                    TextInput::make('account_count_created')
                        ->label('Jumlah Akun Dibuat')
                        ->live(debounce: 1000)
                        ->default('0')
                        ->afterStateUpdated(fn (Get $get, Set $set) => SharedSchema::getDifference($get, $set)),
                    TextInput::make('implementer_count')
                        ->label('Jumlah Akun Pelaksanaan')
                        ->live(debounce: 1000)
                        ->default('0')
                        ->afterStateUpdated(fn (Get $get, Set $set) => SharedSchema::getDifference($get, $set)),
                    TextInput::make('difference')
                        ->label('Selisih')
                        ->readOnly()
                        ->numeric()
                        ->minValue(0)
                        ->live(),
                ]),

            Section::make('Jadwal')
                ->icon('heroicon-o-calendar-days')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    self::date('group', 'Grup'),
                    self::date('bimtek', 'Bimtek'),
                    self::date('counselor_consultation_date', 'Konsul BK'),
                    self::date('student_consultation_date', 'Konsul Siswa'),
                ]),

            Section::make('Konsultasi')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->columns(['default' => 1, 'sm' => 3])
                ->schema([
                    self::yesNo('schools_download', 'Download Sekolah'),
                    self::yesNo('students_download', 'Download Siswa'),
                    self::yesNo('pm', 'PM'),
                ]),
        ];
    }

    /**
     * Pricing, invoice, tax and payment fields owned by the Finance role.
     *
     * @return array<int, Component>
     */
    public static function financeSections(): array
    {
        return [
            Section::make('Akun Siswa')
                ->icon('heroicon-o-identification')
                ->columns(2)
                ->schema([
                    TextInput::make('account_count_created')
                        ->label('Jumlah Akun Dibuat')
                        ->disabled(),
                    TextInput::make('implementer_count')
                        ->label('Jumlah Pelaksanaan')
                        ->disabled(),
                ]),

            Section::make('Opsi Perhitungan')
                ->icon('heroicon-o-calculator')
                ->columns(2)
                ->schema([
                    Radio::make('option_price')
                        ->label('Pilih Opsi')
                        ->options(fn (Get $get): array => [
                            'implementer_'.$get('implementer_count') => 'Jumlah Pelaksanaan',
                            'account_'.$get('account_count_created') => 'Jumlah Akun',
                            'real' => 'Jumlah Real',
                        ])
                        ->live(1000)
                        ->reactive()
                        ->afterStateUpdated(fn (Get $get, Set $set) => SharedSchema::syncTotal($get, $set)),
                    TextInput::make('real_count')
                        ->label('Jumlah Siswa Real')
                        ->numeric()
                        ->minValue(0)
                        ->live(debounce: 1000)
                        ->visible(fn (Get $get): bool => $get('option_price') === 'real')
                        ->afterStateUpdated(fn (Get $get, Set $set) => SharedSchema::syncTotal($get, $set)),
                ]),

            Section::make('Invoice')
                ->icon('heroicon-o-receipt-percent')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    self::date('invoice_date', 'Tanggal Invoice'),
                    self::rupiah('invoice_amount', 'Nominal Invoice'),
                ]),

            Section::make('Nominal')
                ->icon('heroicon-o-banknotes')
                ->columns(['default' => 1, 'sm' => 3])
                ->schema([
                    self::rupiah('price', 'Harga SPJ')
                        ->live(1000)
                        ->reactive()
                        ->afterStateUpdated(fn (Get $get, Set $set) => SharedSchema::syncTotal($get, $set)),
                    self::rupiah('support_school', 'SS (Support Sekolah)'),
                    self::rupiah('net_2', 'Harga NET')
                        ->live(1000)
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $set('total_net', abs((float) $get('implementer_count') * (float) $get('net_2')));
                        }),
                ]),

            Section::make('Pajak')
                ->icon('heroicon-o-scale')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    Group::make([
                        CheckboxList::make('tax_rates')
                            ->label('Pilih Pajak')
                            ->options([
                                '11' => '11%',
                                '2' => '2%',
                                '0.5' => '0,5%',
                                'custom' => 'Isi Manual',
                            ])
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => SharedSchema::syncTax($get, $set)),
                        TextInput::make('tax_custom_rate')
                            ->label('Pajak Manual')
                            ->suffix('%')
                            ->numeric()
                            ->minValue(0)
                            ->live(debounce: 1000)
                            ->visible(fn (Get $get): bool => in_array('custom', (array) $get('tax_rates'), true))
                            ->afterStateUpdated(fn (Get $get, Set $set) => SharedSchema::syncTax($get, $set)),
                    ]),
                    self::rupiah('tax_amount', 'Nominal Pajak')->readOnly(),
                ]),

            Section::make('Total')
                ->icon('heroicon-o-currency-dollar')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    self::rupiah('total', 'Total Dana Sesuai SPJ')->readOnly(),
                    self::rupiah('total_net', 'Total Net')->readOnly(),
                ]),

            Section::make('Pembayaran')
                ->icon('heroicon-o-credit-card')
                ->columns(['default' => 1, 'sm' => 3])
                ->schema([
                    self::date('payment_date', 'Jadwal Pembayaran'),
                    self::date('spk', 'Jadwal SPK'),
                    Select::make('payment_name')
                        ->label('Pembayaran Via')
                        ->options([
                            'SIPLAH' => 'SIPLAH',
                            'SI/TF' => 'SI / TF',
                            'CASH' => 'CASH',
                        ]),
                ]),
        ];
    }

    private static function date(string $name, string $label): DatePicker
    {
        return DatePicker::make($name)
            ->label($label)
            ->native(false)
            ->displayFormat(self::DATE_FORMAT);
    }

    private static function rupiah(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->prefix('Rp')
            ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0);
    }

    private static function yesNo(string $name, string $label): Radio
    {
        return Radio::make($name)
            ->label($label)
            ->options(['YA' => 'YA', 'TIDAK' => 'TIDAK'])
            ->inline()
            ->inlineLabel(false);
    }
}
