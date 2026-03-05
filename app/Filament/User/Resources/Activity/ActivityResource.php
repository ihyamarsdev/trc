<?php

namespace App\Filament\User\Resources\Activity;

use App\Filament\Enum\Program;
use App\Filament\User\Resources\Activity\Pages\ListActivity;
use App\Filament\User\Resources\Activity\Pages\ViewActivities;
use App\Models\RegistrationData;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $title = 'Activity';

    protected static ?string $navigationLabel = 'Activity';

    protected static ?string $modelLabel = 'Activity';

    protected static ?string $slug = 'activity';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ViewAny:ActivityResource') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('3s')
            ->searchable()
            ->striped()
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->withMax('activity', 'id')
                    ->orderByDesc('updated_at')
                    ->when(
                        auth()->user()->hasRole('sales') && ! auth()->user()->hasRole('admin'),
                        fn (Builder $q) => $q->where('users_id', auth()->id())
                    )
            )
            ->columns([
                TextColumn::make('schools')
                    ->label('Sekolah')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Program')
                    ->badge()
                    ->extraAttributes(['class' => 'uppercase']),
                TextColumn::make('periode')
                    ->label('Periode')
                    ->badge()
                    ->extraAttributes(['class' => 'uppercase']),
                TextColumn::make('latestStatusLog.status.name')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn ($record) => match ($record->latestStatusLog?->status?->color) {
                            'green' => 'success',
                            'blue' => 'blue',
                            'yellow' => 'warning',
                            'red' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->placeholder('Belum Ada Status'),
                TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->alignCenter()
                    ->formatStateUsing(
                        fn ($state) => Carbon::parse($state)->translatedFormat('l, d/m/Y H:i'),
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Program')
                    ->options(Program::list())
                    ->preload()
                    ->indicator('Program'),
                SelectFilter::make('latestStatusLog.status.color')
                    ->label('Status Warna')
                    ->options([
                        'red' => 'Merah',
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
                            fn (Builder $q) => $q->where('color', $data['value'])
                        );
                    }),
            ])
            ->recordUrl(fn ($record) => ActivityResource::getUrl('activities', ['record' => $record]))
            ->recordActions([
                //
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListActivity::route('/'),
            'activities' => ViewActivities::route('{record}/activities'),
        ];
    }
}
