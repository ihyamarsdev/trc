<?php

namespace App\Filament\User\Resources\Finance\Forms;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;

class FinanceForm
{
    private static function number(Get $get, string $field): float
    {
        return (float) ($get($field) ?? 0);
    }

    private static function netFromPrice(float $price): float
    {
        return match (true) {
            $price >= 0 && $price <= 100000 => 25000,
            $price <= 150000 => 50000,
            $price <= 200000 => 75000,
            $price > 200000 => 100000,
            default => 0,
        };
    }

    private static function selectedCount(?string $option): float
    {
        if (! is_string($option) || $option === '') {
            return 0;
        }

        preg_match('/\d+/', $option, $matches);

        return (float) ($matches[0] ?? 0);
    }

    private static function recalculateTotals(Get $get, Set $set): void
    {
        $count = self::selectedCount((string) ($get('option_price') ?? ''));

        $set('total', self::number($get, 'price') * $count);
        $set(
            'total_net',
            abs(self::number($get, 'implementer_count') * self::number($get, 'net_2')),
        );
    }

    private static function recalculateSubtotals(Get $get, Set $set): void
    {
        $set('subtotal_1', self::number($get, 'student_count_1') * self::number($get, 'net'));
        $set('mitra_subtotal', self::number($get, 'mitra_difference') * self::number($get, 'mitra_net'));
        $set('ss_subtotal', self::number($get, 'ss_difference') * self::number($get, 'ss_net'));
        $set('dll_subtotal', self::number($get, 'dll_difference') * self::number($get, 'dll_net'));
    }

    private static function syncNetRates(Set $set, float $net): void
    {
        $set('net', $net);
        $set('mitra_net', $net);
        $set('ss_net', $net);
        $set('dll_net', $net);
    }

    public static function schema(): array
    {
        return [
            Section::make('Form Finance')
                ->description('Rincian biaya, status, dan informasi akun')
                ->schema([
                    // === MAIN CONTENT ===
                    Group::make()
                        ->schema([
                            Section::make('Rincian Biaya')
                                ->description('Pengaturan nominal, opsi bayaran, dan bagi hasil')
                                ->schema([
                                    Fieldset::make('Nominal')->schema([
                                        TextInput::make('price')
                                            ->label('Harga SPJ')
                                            ->prefix('Rp')
                                            ->live(3000)
                                            ->reactive()
                                            ->currencyMask(
                                                thousandSeparator: ',',
                                                decimalSeparator: '.',
                                                precision: 0,
                                            )
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $net = self::netFromPrice(self::number($get, 'price'));

                                                self::syncNetRates($set, $net);
                                                self::recalculateTotals($get, $set);
                                                self::recalculateSubtotals($get, $set);
                                            }),
                                        TextInput::make('net_2')
                                            ->label('Harga NET')
                                            ->prefix('Rp')
                                            ->live(3000)
                                            ->reactive()
                                            ->currencyMask(
                                                thousandSeparator: ',',
                                                decimalSeparator: '.',
                                                precision: 0,
                                            )
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                self::recalculateTotals($get, $set);
                                            }),
                                    ]),

                                    Fieldset::make('Opsi')->schema([
                                        Radio::make('option_price')
                                            ->label('Pilih Opsi')
                                            ->options(function (Get $get): array {
                                                return [
                                                    'implementer_'.
                                                    $get(
                                                        'implementer_count',
                                                    ) => 'JUMLAH PELAKSANAAN',
                                                    'account_'.
                                                    $get(
                                                        'account_count_created',
                                                    ) => 'JUMLAH AKUN',
                                                ];
                                            })
                                            ->live(1000)
                                            ->reactive()
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                self::recalculateTotals($get, $set);
                                            }),
                                    ]),

                                    Fieldset::make('Total')->schema([
                                        TextInput::make('total')
                                            ->label('Total Dana Sesuai SPJ')
                                            ->prefix('Rp')
                                            ->currencyMask(
                                                thousandSeparator: ',',
                                                decimalSeparator: '.',
                                                precision: 0,
                                            )
                                            ->readOnly(),
                                        TextInput::make('total_net')
                                            ->label('Total Net')
                                            ->prefix('Rp')
                                            ->currencyMask(
                                                thousandSeparator: ',',
                                                decimalSeparator: '.',
                                                precision: 0,
                                            )
                                            ->readOnly(),
                                    ]),

                                    Fieldset::make('')
                                        ->label('TRC')
                                        ->schema([
                                            TextInput::make('student_count_1')
                                                ->label('Selisih Siswa TRC')
                                                ->numeric()
                                                ->live(debounce: 1000)
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalculateSubtotals($get, $set);
                                                }),
                                            TextInput::make('net')
                                                ->label('Satuan')
                                                ->live(debounce: 1000)
                                                ->prefix('Rp')
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0,
                                                )
                                                ->numeric()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalculateSubtotals($get, $set);
                                                }),
                                            TextInput::make('subtotal_1')
                                                ->label('Subtotal')
                                                ->prefix('Rp')
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0,
                                                )
                                                ->numeric()
                                                ->readOnly(),
                                        ])
                                        ->columns(3),

                                    Fieldset::make('')
                                        ->label('MITRA')
                                        ->schema([
                                            TextInput::make('mitra_difference')
                                                ->label('Selisih Siswa Sekolah')
                                                ->numeric()
                                                ->live(debounce: 1000)
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalculateSubtotals($get, $set);
                                                }),
                                            TextInput::make('mitra_net')
                                                ->label('Satuan')
                                                ->live(debounce: 1000)
                                                ->prefix('Rp')
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0,
                                                )
                                                ->numeric()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalculateSubtotals($get, $set);
                                                }),
                                            TextInput::make('mitra_subtotal')
                                                ->label('Subtotal')
                                                ->prefix('Rp')
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0,
                                                )
                                                ->numeric()
                                                ->readOnly(),

                                            TextInput::make('ss_difference')
                                                ->label('SS')
                                                ->numeric()
                                                ->live(debounce: 1000)
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalculateSubtotals($get, $set);
                                                }),
                                            TextInput::make('ss_net')
                                                ->label('Satuan')
                                                ->live(debounce: 1000)
                                                ->prefix('Rp')
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0,
                                                )
                                                ->numeric()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalculateSubtotals($get, $set);
                                                }),
                                            TextInput::make('ss_subtotal')
                                                ->label('Subtotal')
                                                ->prefix('Rp')
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0,
                                                )
                                                ->numeric()
                                                ->readOnly(),

                                            TextInput::make('dll_difference')
                                                ->label('Lain-lain')
                                                ->numeric()
                                                ->live(debounce: 1000)
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalculateSubtotals($get, $set);
                                                }),
                                            TextInput::make('dll_net')
                                                ->label('Satuan')
                                                ->live(debounce: 1000)
                                                ->prefix('Rp')
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0,
                                                )
                                                ->numeric()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalculateSubtotals($get, $set);
                                                }),
                                            TextInput::make('dll_subtotal')
                                                ->label('Subtotal')
                                                ->prefix('Rp')
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0,
                                                )
                                                ->numeric()
                                                ->readOnly(),
                                        ])
                                        ->columns(3),

                                    Fieldset::make('Jadwal & Pembayaran')->schema([
                                        DatePicker::make('invoice_date')
                                            ->label('Invoice')
                                            ->native(false)
                                            ->displayFormat('l, jS F Y'),
                                        DatePicker::make('payment_date')
                                            ->label('Jadwal Pembayaran')
                                            ->native(false)
                                            ->displayFormat('l, jS F Y'),
                                        DatePicker::make('spk')
                                            ->label('Jadwal SPK')
                                            ->native(false)
                                            ->displayFormat('l, jS F Y'),
                                        Select::make('payment_name')
                                            ->label('Pembayaran Via')
                                            ->options([
                                                'SIPLAH' => 'SIPLAH',
                                                'SI/TF' => 'SI / TF',
                                                'CASH' => 'CASH',
                                            ]),
                                    ])->columns(2),
                                ])
                                ->columns(1),
                        ])
                        ->columnSpan(['lg' => 8]),

                    // === SIDEBAR ===
                    Group::make()
                        ->schema([
                            Section::make('Status')
                                ->schema([
                                    Select::make('status_id')
                                        ->label('Status')
                                        ->preload()
                                        ->relationship(
                                            name: 'status',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (Builder $query) => $query
                                                ->where('order', '>', 9)
                                                ->orderBy('order'),
                                        )
                                        ->searchable()
                                        ->placeholder('Pilih status...')
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),

                            Section::make('Informasi Akun')
                                ->description('Data jumlah akun')
                                ->schema([
                                    TextInput::make('account_count_created')
                                        ->label('Jumlah Akun Dibuat')
                                        ->disabled(),
                                    TextInput::make('implementer_count')
                                        ->label('Jumlah Pelaksanaan')
                                        ->disabled(),
                                ])
                                ->columns(1),
                        ])
                        ->columnSpan(['lg' => 4]),
                ])
                ->columns(['lg' => 12])
                ->columnSpanFull(),
        ];
    }
}
