<?php

namespace App\Filament\User\Resources\Finance\Monitoring;

use App\Filament\Components\Finance;
use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\RegistrationData;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RekapitulasiFinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Rekapitulasi';

    protected static ?string $modelLabel = 'rekapitulasi';

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Finance::getRoles());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([50, 100, 200])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->with(['latestStatusLog.status'])
            )
            ->columns([
                TextColumn::make('row_number')
                    ->label('No')
                    ->alignCenter()
                    ->getStateUsing(fn ($rowLoop) => $rowLoop->iteration),
                TextColumn::make('schools')->label('Schools')->searchable(),
                IconColumn::make('invoice')
                    ->label('INVOICE')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        11
                            ? 'heroicon-s-check'
                            : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        11
                            ? 'success'
                            : 'danger',
                    )
                    ->default(false),
                IconColumn::make('check_transfer')
                    ->label('TRANSFER')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        12
                            ? 'heroicon-s-check'
                            : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        12
                            ? 'success'
                            : 'danger',
                    )
                    ->default(false),
                IconColumn::make('spj')
                    ->label('SPJ dan Hasil')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        13
                            ? 'heroicon-s-check'
                            : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        13
                            ? 'success'
                            : 'danger',
                    )
                    ->default(false),
                IconColumn::make('kirim_invoice')
                    ->label('KIRIM INV')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        14
                            ? 'heroicon-s-check'
                            : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        14
                            ? 'success'
                            : 'danger',
                    )
                    ->default(false),
                IconColumn::make('support_sekolah')
                    ->label('SUPPORT')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        15
                            ? 'heroicon-s-check'
                            : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        15
                            ? 'success'
                            : 'danger',
                    )
                    ->default(false),
                IconColumn::make('pesanan_selesai')
                    ->label('SELESAI')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        16
                            ? 'heroicon-s-check'
                            : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        16
                            ? 'success'
                            : 'danger',
                    )
                    ->default(false),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->alignCenter()
                    ->formatStateUsing(
                        fn ($state) => Carbon::parse($state)->translatedFormat(
                            'l, d/m/Y H:i',
                        ),
                    ),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options(Periode::list())
                    ->preload(),
                Tables\Filters\SelectFilter::make('education_level')
                    ->label('Jenjang')
                    ->options(Jenjang::list())
                    ->preload()
                    ->indicator('Jenjang'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Program')
                    ->options(Program::list())
                    ->preload()
                    ->indicator('Program'),
                Tables\Filters\SelectFilter::make('users_id')
                    ->label('User')
                    ->options(function () {
                        return \App\Models\User::all()
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->preload()
                    ->indicator('user'),
                Tables\Filters\SelectFilter::make('status_color')
                    ->label('Status Warna')
                    ->options([
                        'yellow' => 'Kuning',
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
                            fn (Builder $q) => $q->where(
                                'color',
                                $data['value'],
                            ),
                        );
                    }),
            ])
            ->recordAction('view')
            ->actions(
                [
                    // Tables\Actions\EditAction::make(),
                ],
                position: RecordActionsPosition::BeforeColumns,
            )
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekapitulasiFinances::route('/'),
            'view' => Pages\ViewRekapitulasiFinance::route('/{record}'),
        ];
    }
}
