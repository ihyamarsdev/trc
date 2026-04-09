<?php

namespace App\Filament\User\Resources\Activity;

use App\Filament\Components\Admin;
use App\Filament\Enum\Program;
use App\Filament\User\Resources\Activity\ActivityResource\Pages;
use App\Models\RegistrationData;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('10s')
            ->searchable()
            ->striped()
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['latestStatusLog.status'])
                    ->withMax('activity', 'id')
                    ->orderByDesc('updated_at')
                    ->when(
                        Auth::user()?->hasRole('sales') && ! Auth::user()?->hasRole('admin'),
                        fn (Builder $q) => $q->where('users_id', Auth::id())
                    )
            )
            ->columns(Admin::columns())
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Program')
                    ->options(Program::list())
                    ->preload()
                    ->indicator('Program'),
                Tables\Filters\SelectFilter::make('latestStatusLog.status.color')
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
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->recordUrl(fn ($record) => ActivityResource::getUrl('activities', ['record' => $record]))
            ->actions([
                //
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
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
            'index' => Pages\ListActivity::route('/'),
            'activities' => Pages\ViewActivities::route('{record}/activities'),
        ];
    }
}
