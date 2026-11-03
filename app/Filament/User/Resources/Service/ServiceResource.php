<?php

namespace App\Filament\User\Resources\Service;

use App\Filament\Components\Service;
use App\Filament\User\Resources\Service\ServiceResource\Pages;
use App\Models\RegistrationData;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ServiceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-wrench-screwdriver';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Service';
    }

    protected static ?string $title = 'Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'database';

    protected static ?string $slug = 'database-service';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasRole(Service::getRoles()) ?? false;
    }

    /**
     * Service only sees yellow (just handed over by Sales) and blue (being handled) records.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereRelation('status', fn (Builder $query) => $query->whereIn('color', ['yellow', 'blue']));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Service::formSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('15s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->modifyQueryUsing(
                function (Builder $query) {
                    RegistrationData::updateOverdueYellowStatuses();

                    return $query
                        ->with(['latestStatusLog.status', 'status'])
                        ->where('years', now('Asia/Jakarta')->format('Y'))
                        ->orderByDesc('updated_at');
                }
            )
            ->columns(
                Service::columns()
            )
            ->filters(Service::filters())
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->recordAction('view')
            ->actions([])
            ->bulkActions(Service::bulkActions());
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
