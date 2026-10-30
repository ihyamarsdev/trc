<?php

namespace App\Filament\Components;

use App\Filament\Components\Support\FormSchema;
use App\Filament\Components\Support\HasProgramMetadata;
use App\Filament\Components\Support\InfolistSchema;
use App\Filament\Components\Support\SharedSchema;
use App\Filament\Enum\OptionGroup;
use App\Models\Option;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Finance
{
    use HasProgramMetadata;

    protected static function metaInfo(Model $record, string $default = 'apps'): array
    {
        return Option::programMeta($record->type, $default);
    }

    public static function formSchema(): array
    {
        return [
            Section::make('Status')
                ->icon('heroicon-o-flag')
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
                        ->placeholder('Pilih status...'),
                ]),

            ...FormSchema::financeSections(),
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
            TextColumn::make('total_received')->label('Total Uang Masuk Rekening')->prefix('Rp.'),
            TextColumn::make('bank_name')->label('Bank'),
            TextColumn::make('total_support_school')->label('Total SS')->prefix('Rp.'),
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
                ->options(Option::list(OptionGroup::Periode, onlyActive: false))
                ->preload(),
            Tables\Filters\SelectFilter::make('education_level')
                ->label('Jenjang')
                ->options(Option::list(OptionGroup::Jenjang, onlyActive: false))
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
