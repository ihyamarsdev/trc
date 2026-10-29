<?php

namespace App\Filament\Components;

use App\Filament\Components\Support\HasProgramMetadata;
use App\Filament\Components\Support\InfolistSchema;
use App\Filament\Components\Support\SharedSchema;
use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Finance
{
    use HasProgramMetadata;

    protected static function metaInfo(Model $record, string $default = 'apps'): array
    {
        return Program::getMetadata($record->type, $default);
    }

    public static function formSchema(): array
    {
        return [
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
                        ->columnSpan(1),
                ])
                ->columns(2),
            Section::make()
                ->description()
                ->schema([
                    Fieldset::make('')->schema([
                        TextInput::make('account_count_created')
                            ->label('Jumlah Akun Dibuat')
                            ->disabled(),
                        TextInput::make('implementer_count')
                            ->label('Jumlah Pelaksanaan')
                            ->disabled(),
                    ]),

                    Fieldset::make('Opsi')->schema([
                        Radio::make('option_price')
                            ->label('Pilih Opsi')
                            ->options(function (Get $get): array {
                                return [
                                    'implementer_'.$get('implementer_count') => 'JUMLAH PELAKSANAAN',
                                    'account_'.$get('account_count_created') => 'JUMLAH AKUN',
                                    'real' => 'JUMLAH REAL',
                                ];
                            })
                            ->live(1000)
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                SharedSchema::syncTotal($get, $set);
                            }),
                        TextInput::make('real_count')
                            ->label('Jumlah Siswa Real')
                            ->numeric()
                            ->minValue(0)
                            ->live(debounce: 1000)
                            ->visible(fn (Get $get): bool => $get('option_price') === 'real')
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                SharedSchema::syncTotal($get, $set);
                            }),
                    ]),

                    Fieldset::make('Invoice')->schema([
                        DatePicker::make('invoice_date')
                            ->label('Tanggal Invoice')
                            ->native(false)
                            ->displayFormat('l, jS F Y'),
                        TextInput::make('invoice_amount')
                            ->label('Nominal Invoice')
                            ->prefix('Rp')
                            ->currencyMask(
                                thousandSeparator: ',',
                                decimalSeparator: '.',
                                precision: 0,
                            ),
                    ]),

                    Fieldset::make('Nominal')
                        ->schema([
                            TextInput::make('price')
                                ->label('Harga SPJ')
                                ->prefix('Rp')
                                ->live(1000)
                                ->reactive()
                                ->currencyMask(
                                    thousandSeparator: ',',
                                    decimalSeparator: '.',
                                    precision: 0,
                                )
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    SharedSchema::syncTotal($get, $set);
                                }),
                            TextInput::make('support_school')
                                ->label('SS (Support Sekolah)')
                                ->prefix('Rp')
                                ->currencyMask(
                                    thousandSeparator: ',',
                                    decimalSeparator: '.',
                                    precision: 0,
                                ),
                            TextInput::make('net_2')
                                ->label('Harga NET')
                                ->prefix('Rp')
                                ->live(1000)
                                ->reactive()
                                ->currencyMask(
                                    thousandSeparator: ',',
                                    decimalSeparator: '.',
                                    precision: 0,
                                )
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set(
                                        'total_net',
                                        abs(
                                            (float) $get('implementer_count') *
                                            (float) $get('net_2'),
                                        ),
                                    );
                                }),
                        ])
                        ->columns(3),

                    Fieldset::make('Pajak')->schema([
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
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    SharedSchema::syncTax($get, $set);
                                }),
                            TextInput::make('tax_custom_rate')
                                ->label('Pajak Manual')
                                ->suffix('%')
                                ->numeric()
                                ->minValue(0)
                                ->live(debounce: 1000)
                                ->visible(fn (Get $get): bool => in_array('custom', (array) $get('tax_rates'), true))
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    SharedSchema::syncTax($get, $set);
                                }),
                        ]),
                        TextInput::make('tax_amount')
                            ->label('Nominal Pajak')
                            ->prefix('Rp')
                            ->currencyMask(
                                thousandSeparator: ',',
                                decimalSeparator: '.',
                                precision: 0,
                            )
                            ->readOnly(),
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

                    Fieldset::make('')->schema([
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
                    ]),
                ])
                ->columns(2),
        ];
    }

    public static function columns(): array
    {
        return SharedSchema::columns();
    }

    public static function exportColumns(): array
    {
        return [
            // Finance
            TextColumn::make('option_price')->label('Opsi Jumlah'),
            TextColumn::make('real_count')->label('Jumlah Siswa Real'),
            TextColumn::make('invoice_date')
                ->label('Tanggal Invoice')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('invoice_amount')
                ->label('Nominal Invoice')
                ->prefix('Rp.'),
            TextColumn::make('price')->label('Harga SPJ')->prefix('Rp.'),
            TextColumn::make('support_school')
                ->label('SS (Support Sekolah)')
                ->prefix('Rp.'),
            TextColumn::make('net_2')->label('Harga NET')->prefix('Rp.'),
            TextColumn::make('tax_rates')
                ->label('Pilihan Pajak')
                ->state(
                    fn (Model $record): string => implode(
                        ', ',
                        SharedSchema::taxRateLabels(
                            $record->tax_rates,
                            $record->tax_custom_rate,
                        ),
                    ),
                ),
            TextColumn::make('tax_amount')
                ->label('Nominal Pajak')
                ->prefix('Rp.'),
            TextColumn::make('total')
                ->label('Total Dana Sesuai SPJ')
                ->prefix('Rp.'),
            TextColumn::make('total_net')->label('Total Net')->prefix('Rp.'),

            TextColumn::make('spk')
                ->label('SPK Terkirim')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('payment_date')
                ->label('Tanggal Pembayaran')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('payment_name')->label('Pembayaran'),
        ];
    }

    public static function infolist(Model $record): array
    {
        return InfolistSchema::compose($record, [
            InfolistSchema::PART_SERVICE,
            InfolistSchema::PART_FINANCE,
            InfolistSchema::PART_DOCUMENTS,
        ]);
    }

    public static function filters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('periode')
                ->label('Periode')
                ->options(Periode::list())
                ->preload(),
            Tables\Filters\SelectFilter::make('education_level')
                ->label('Jenjang')
                ->options(Jenjang::list())
                ->preload()
                ->indicator('Jenjang'),
            Tables\Filters\SelectFilter::make('users_id')
                ->label('User')
                ->options(function () {
                    return User::query()
                        ->select(['id', 'name'])
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all();
                })
                ->preload()
                ->indicator('user'),
            Tables\Filters\SelectFilter::make('status_color')
                ->label('Status Warna')
                ->options([
                    'blue' => 'Biru',
                    'green' => 'Hijau',
                ])
                ->preload()
                ->indicator('Status Warna')
                ->query(function (Builder $query, array $data) {
                    if (empty($data['value'])) {
                        return;
                    }

                    $query->whereHas(
                        'status',
                        fn (Builder $q) => $q->where('color', $data['value']),
                    );
                }),
            Tables\Filters\TernaryFilter::make('payment_date')
                ->label('Status Pembayaran Sekolah')
                ->placeholder('Semua Sekolah')
                ->trueLabel('Sudah Bayar')
                ->falseLabel('Belum Bayar')
                ->queries(
                    true: fn (Builder $query) => $query->whereNotNull(
                        'payment_date',
                    ),
                    false: fn (Builder $query) => $query->whereNull(
                        'payment_date',
                    ),
                    blank: fn (Builder $query) => $query,
                ),
        ];
    }

    public static function actions(): array
    {
        return [Tables\Actions\ViewAction::make()];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function getRoles(): array
    {
        return ['finance'];
    }
}
