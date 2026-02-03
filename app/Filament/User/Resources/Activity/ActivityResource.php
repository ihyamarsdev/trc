<?php

namespace App\Filament\User\Resources\Activity;

use App\Filament\Components\Admin;
use App\Filament\Enum\Program;
use App\Filament\User\Resources\Activity\ActivityResource\Pages;
use App\Models\RegistrationData;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $title = 'Activity';

    protected static ?string $navigationLabel = 'Activity';

    protected static ?string $modelLabel = 'Activity';

    protected static ?string $slug = 'activity';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
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
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
