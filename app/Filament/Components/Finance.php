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
                            MoneyInput::make('price')
                                ->label('Harga')
                                ->currency('IDR')
                                ->locale('id_ID'),
                        ]),

                    Fieldset::make('')
                        ->schema([
                            Radio::make('option_price')
                                ->label('Pilih Opsi')
                                ->options(function (Get $get) :array{
                                    $accountCount = (int) $get('account_count_created');
                                    $implementerCount = (int) $get('implementer_count');

                                    return [
                                        $accountCount => 'Jumlah Akun',
                                        $implementerCount => 'Jumlah Pelaksanaan'
                                    ];
                                })
                                ->reactive()
                                ->afterStateUpdated(fn (Get $get, Set $set) => $set('total', (float) $get('price') * $get('option_price'))),

                            MoneyInput::make('total')
                                ->label('Total')
                                ->readOnly(),
                        ]),

                    Fieldset::make('')
                        ->schema([
                            MoneyInput::make('net')
                                ->label('Net')
                                ->currency('IDR')
                                ->locale('id_ID'),
                        ]),

                    Fieldset::make('')
                        ->schema([
                            Radio::make('option_net')
                                ->label('Pilih Opsi')
                                ->options(function (Get $get) :array{
                                    $accountCount = (int) $get('account_count_created');
                                    $implementerCount = (int) $get('implementer_count');

                                    return [
                                        $accountCount => 'Jumlah Akun',
                                        $implementerCount => 'Jumlah Pelaksanaan'
                                    ];
                                })
                                ->reactive()
                                ->afterStateUpdated(fn (Get $get, Set $set) => $set('total_net', (float) $get('net') * $get('option_net'))),

                            MoneyInput::make('total_net')
                                ->label('Total Net')
                                ->readOnly(),
                        ]),
                ])->columns(2),

            Section::make()
                ->schema([
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
