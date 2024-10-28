<?php

namespace App\Filament\Components;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\{TextColumn};
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Filament\Forms\Components\{Select, TextInput, Section, DatePicker, Radio, Fieldset};

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
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 0),
                        ]),

                    Fieldset::make('')
                        ->schema([
                            Radio::make('option_price')
                                ->label('Pilih Opsi')
                                ->options(function (Get $get): array {
                                    $accountCount = (int) $get('account_count_created');
                                    $implementerCount = (int) $get('implementer_count');

                                    return [
                                        $accountCount => 'Jumlah Akun',
                                        $implementerCount => 'Jumlah Pelaksanaan'
                                    ];
                                })
                                ->reactive()
                                ->afterStateUpdated(fn (Get $get, Set $set) => $set('total', (float) $get('price') * $get('option_price'))),

                            TextInput::make('total')
                                ->label('Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 0)
                                ->readOnly(),
                        ]),

                    Fieldset::make('')
                        ->label('Exclusion policy')
                        ->schema([
                            TextInput::make('student_count_1')
                                ->label('Jumlah Siswa 1')
                                ->numeric()
                                ->live(debounce: 500)
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
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 0)
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
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 0)
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
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 0)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('subtotal_2', (float) $get('student_count_2') * (float) $get('net_2'));
                                    $set('total_net', (float) $get('subtotal_1') + (float) $get('subtotal_2'));
                                    $set('difference_total', abs((float) $get('total') - (float) $get('total_net')));
                                }),
                            TextInput::make('subtotal_2')
                                ->label('Sub Total 2')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 0)
                                ->readOnly(),
                        ])->columns(3),

                    Fieldset::make('')
                        ->schema([
                            TextInput::make('total_net')
                                ->label('Total Net')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 0)
                                ->readOnly(),
                            TextInput::make('difference_total')
                                ->label('Selisih Total')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 0)
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
