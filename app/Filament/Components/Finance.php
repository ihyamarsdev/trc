<?php

namespace App\Filament\Components;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\{TextColumn};
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Filament\Forms\Components\{Select, TextInput, Section, DatePicker, Radio, Fieldset, Group};

class Finance
{
    public static function formSchema(array $options = []): array
    {
        return [
            Section::make($options['nameRegister'])
                ->description($options['DescriptionRegister'])
                ->schema([

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('account_count_created')
                                ->label('Akun Dibuat')
                                ->disabled(),
                            TextInput::make('implementer_count')
                                ->label('Pelaksanaan')
                                ->disabled(),
                        ]),

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('price')
                                ->label('Harga')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0),
                        ]),

                    Fieldset::make('')
                        ->schema([
                            Radio::make('option_price')
                                ->label('Pilih Opsi')
                                ->options(function (Get $get): array {
                                    $accountCount = (float) $get('account_count_created');
                                    $implementerCount = (float) $get('implementer_count');

                                    return [
                                        $accountCount => 'Jumlah Akun',
                                        $implementerCount => 'Jumlah Pelaksanaan'
                                    ];
                                })
                                ->live(200)
                                ->reactive()
                                ->afterStateUpdated(fn (Get $get, Set $set) => $set('total', (float) $get('price') * (float) $get('option_price'))),

                            TextInput::make('total')
                                ->label('Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                        ]),

                    Fieldset::make('')
                        ->label('Exclusion policy')
                        ->schema([
                            TextInput::make('student_count_1')
                                ->label('Jumlah Siswa 1')
                                ->numeric()
                                ->live(debounce: 200)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('student_count_2', (float) $get('student_count') - (float) $get('student_count_1'));
                                    $set('subtotal_1', (float) $get('student_count_1') * (float) $get('net'));
                                    $set('subtotal_2', (float) $get('student_count_2') * (float) $get('net_2'));
                                    $set('total_net', (float) $get('subtotal_1') + (float) $get('subtotal_2'));
                                    $set('difference_total', abs((float) $get('total') - (float) $get('total_net')));
                                }),
                            TextInput::make('net')
                                ->label('Net 1')
                                ->live(debounce: 1000)
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('subtotal_1', (float) $get('student_count_1') * (float) $get('net'));
                                    $set('subtotal_2', (float) $get('student_count_2') * (float) $get('net_2'));
                                    $set('total_net', (float) $get('subtotal_1') + (float) $get('subtotal_2'));
                                    $set('difference_total', abs((float) $get('total') - (float) $get('total_net')));
                                }),
                            TextInput::make('subtotal_1')
                                ->label('Sub Total 1')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),
                            TextInput::make('student_count_2')
                                ->label('Jumlah Siswa 2')
                                ->live()
                                ->numeric(),
                            TextInput::make('net_2')
                                ->label('Net 2')
                                ->live(debounce: 1000)
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('subtotal_2', (float) $get('student_count_2') * (float) $get('net_2'));
                                    $set('total_net', (float) $get('subtotal_1') + (float) $get('subtotal_2'));
                                    $set('difference_total', abs((float) $get('total') - (float) $get('total_net')));
                                }),
                            TextInput::make('subtotal_2')
                                ->label('Sub Total 2')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                        ])->columns(3),

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('total_net')
                                ->label('Total Net')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                            TextInput::make('difference_total')
                                ->label('Selisih Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                        ]),

                    Fieldset::make('')
                        ->schema([
                            DatePicker::make('invoice_date')
                                ->label('Invoice')
                                ->required(),
                            DatePicker::make('payment_date')
                                ->label('Pembayaran')
                                ->required(),
                            DatePicker::make('spk_sent')
                                ->label('SPK di Kirim')
                                ->required(),
                            Select::make('payment')
                                ->label('Pembayaran Via')
                                ->options([
                                    'siplah' => 'Siplah',
                                    'si/tf' => 'SI / TF',
                                    'cash' => 'Cash'
                                ])
                        ]),
                ])->columns(2),

            Section::make('Kwitansi')
                ->schema([
                    Fieldset::make('')
                        ->schema([
                            TextInput::make('schools')
                                ->label('Sekolah')
                                ->readOnly(),
                            TextInput::make('detail_kwitansi')
                                ->label('Guna Pembayaran')
                                ->helperText('Contoh: 146 Paket Program TRY OUT Ujian Tertulis Berbasis Komputer (UTBK SNBT)'),
                        ])->columns(1),
                ]),

            Section::make('Invoice')
                ->schema([
                    Fieldset::make('')
                        ->schema([
                            TextInput::make('schools')
                                ->label('Sekolah')
                                ->readOnly(),
                            TextInput::make('number_invoice')
                                ->prefix('#')
                                ->label('Nomor Invoice')
                                ->live()
                                ->numeric(),
                            TextInput::make('detail_invoice')
                                ->label('Deskripsi')
                                ->helperText('Contoh: Try Out Asesmen Nasional (AKM)'),
                        ])->columns(1),

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('qty_invoice')
                                ->label('Quantity')
                                ->live(200)
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                    $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                    $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                }),
                            TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->prefix('Rp')
                                ->live(200)
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                    $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                    $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                }),
                            TextInput::make('amount_invoice')
                                ->label('Amount')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),
                            TextInput::make('subtotal_invoice')
                                ->label('Sub Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->numeric()
                                ->readOnly(),

                        ])->columns(2),

                    Fieldset::make('Pajak')
                        ->schema([
                            Radio::make('option_tax')
                                ->label('Pilih Opsi Pajak')
                                ->options([
                                    '0.02' => 'PPH 23',
                                    '0.11' => 'PPN',
                                ])
                                ->live(200)
                                ->reactive()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('total_invoice', (float) $get('subtotal_invoice') + ((float) $get('subtotal_invoice') * (float) $get('option_tax')));
                                    if ($get('option_tax') == '0.02') {
                                        $set('tax_rate', "2");
                                        $set('sales_tsx', "0");
                                    } elseif ($get('option_tax') == '0.11') {
                                        $set('tax_rate', "0");
                                        $set('sales_tsx', "11");
                                    }
                                }),
                            Group::make([
                                TextInput::make('tax_rate')
                                    ->label('PPH 23')
                                    ->suffix('%')
                                    ->readOnly()
                                    ->live(200),
                                TextInput::make('sales_tsx')
                                    ->label('PPN')
                                    ->suffix('%')
                                    ->readOnly()
                                    ->live(200),
                            ]),

                        ])->columns(2),

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('total_invoice')
                                ->label('Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->readOnly(),
                        ])->columns(2),
                ]),
        ];
    }

    public static function columns(): array
    {
        return [
            TextColumn::make('no')
                ->rowIndex(),
            TextColumn::make('periode')
                ->label('Periode'),
            TextColumn::make('school_years.name')
                ->label('Tahun Ajaran'),
            TextColumn::make('users.name')
                ->label('User')
                ->searchable(),
            TextColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->date()
                ->sortable(),
            TextColumn::make('provinces')
                ->label('Provinsi'),
            TextColumn::make('regencies')
                ->label('Kota / Kabupaten'),
            TextColumn::make('schools')
                ->label('Sekolah'),
            TextColumn::make('education_level')
                ->label('Jenjang'),
            TextColumn::make('education_level_type')
                ->label('Negeri / Swasta'),
            TextColumn::make('principal')
                ->label('Kepala Sekolah'),
            TextColumn::make('phone_principal')
                ->label('No Hp Kepala Sekolah'),
            TextColumn::make('curriculum_deputies.name')
                ->label('Wakakurikulum'),
            TextColumn::make('curriculum_deputies.phone')
                ->label('No Hp Wakakurikulum'),
            TextColumn::make('counselor_coordinators.name')
                ->label('Koordinator BK'),
            TextColumn::make('counselor_coordinators.phone')
                ->label('No Hp Koordinator BK'),
            TextColumn::make('proctors.name')
                ->label('Proktor'),
            TextColumn::make('proctors.phone')
                ->label('No Hp Proktor'),
            TextColumn::make('student_count')
                ->label('Jumlah Siswa')
                ->numeric(),
            TextColumn::make('implementation_estimate')
                ->label('Estimasi Pelaksana')
                ->date(),
        ];
    }


}
