<?php

namespace App\Filament\User\Resources\Service\Monitoring;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Filament\User\Resources\Service\Monitoring\Pages\ListRekapitulasiServices;
use App\Filament\User\Resources\Service\Monitoring\Pages\ViewRekapitulasiService;
use App\Models\RegistrationData;
use App\Models\User;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RekapitulasiServiceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Service';

    protected static ?string $navigationLabel = 'Rekapitulasi';

    protected static ?string $modelLabel = 'rekapitulasi';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ViewAny:RekapitulasiServiceResource') ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([50, 100, 200])
            ->columns([
                TextColumn::make('row_number')
                    ->label('No')
                    ->alignCenter()
                    ->getStateUsing(fn ($rowLoop) => $rowLoop->iteration),
                TextColumn::make('schools')->label('Schools')->searchable(),
                IconColumn::make('group')
                    ->label('GRUP')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        3
                        ? 'heroicon-s-check'
                        : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        3
                        ? 'success'
                        : 'danger',
                    )
                    ->default(false),
                IconColumn::make('bimtek')
                    ->label('BIMTEK')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        4
                        ? 'heroicon-s-check'
                        : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        4
                        ? 'success'
                        : 'danger',
                    )
                    ->default(false),
                IconColumn::make('account_count_created')
                    ->label('AKUN')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        6
                        ? 'heroicon-s-check'
                        : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        6
                        ? 'success'
                        : 'danger',
                    )
                    ->default(false),
                IconColumn::make('implementer_count')
                    ->label('EVENT')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        7
                        ? 'heroicon-s-check'
                        : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        7
                        ? 'success'
                        : 'danger',
                    )
                    ->default(false),
                IconColumn::make('students_download')
                    ->label('DOWNLOAD')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        9
                        ? 'heroicon-s-check'
                        : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        9
                        ? 'success'
                        : 'danger',
                    )
                    ->default(false),
                IconColumn::make('schools_download')
                    ->label('PM')
                    ->alignCenter()
                    ->icon(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        10
                        ? 'heroicon-s-check'
                        : 'heroicon-s-x-mark',
                    )
                    ->color(
                        fn ($record) => ($record->latestStatusLog?->status
                            ?->order ??
                            0) >=
                        10
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
                SelectFilter::make('periode')
                    ->label('Periode')
                    ->options(Periode::list())
                    ->preload(),
                SelectFilter::make('education_level')
                    ->label('Jenjang')
                    ->options(Jenjang::list())
                    ->preload()
                    ->indicator('Jenjang'),
                SelectFilter::make('type')
                    ->label('Program')
                    ->options(Program::list())
                    ->preload()
                    ->indicator('Program'),
                SelectFilter::make('users_id')
                    ->label('User')
                    ->options(function () {
                        return User::all()
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->preload()
                    ->indicator('user'),
                SelectFilter::make('status_color')
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
            ->recordActions(
                [
                    // Tables\Actions\EditAction::make(),
                ],
                position: RecordActionsPosition::BeforeColumns,
            )
            ->toolbarActions([]);
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
            'index' => ListRekapitulasiServices::route('/'),
            'view' => ViewRekapitulasiService::route('/{record}'),
        ];
    }
}
