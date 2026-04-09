<?php

namespace App\Filament\User\Resources\Salesforce;

use App\Filament\Components\SalesForce;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages;
use App\Models\RegistrationData;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SalesResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-presentation-chart-line';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Salesforce';
    }

    protected static ?string $title = 'Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?string $modelLabel = 'database';

    protected static ?string $slug = 'database-salesforce';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Salesforce::getRoles());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(SalesForce::schema())
            ->extraAttributes([
                'onkeydown' => "
                if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
                    event.preventDefault();
                    let focusables = Array.from(document.querySelectorAll('input, select, button, [contenteditable]'));
                    let index = focusables.indexOf(event.target);
                    if (index > -1 && focusables[index + 1]) {
                        focusables[index + 1].focus();
                    }
                }
            ",
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('15s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->recordAction('view')
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['latestStatusLog.status'])
                    ->where('years', now('Asia/Jakarta')->format('Y'))
                    ->when(Auth::id(), fn (Builder $builder, int $userId) => $builder->where('users_id', $userId))
                    ->orderBy('implementation_estimate', 'asc')
            )
            ->columns(SalesForce::columns())
            ->filters(SalesForce::filters())
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->actions([])
            ->bulkActions(SalesForce::bulkActions());
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(SalesForce::infolist());
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'view' => Pages\ViewSales::route('/{record}'),
            'edit' => Pages\EditSales::route('/{record}/edit'),
        ];
    }
}
