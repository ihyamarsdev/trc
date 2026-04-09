<?php

namespace App\Filament\User\Resources\Finance;

use App\Filament\Components\Finance;
use App\Filament\User\Resources\Finance\FinanceResource\Pages;
use App\Models\RegistrationData;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-m-credit-card';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    protected static ?string $title = 'Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?string $modelLabel = 'database';

    protected static ?string $slug = 'database-finance';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasRole(Finance::getRoles()) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Finance::formSchema());
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
                fn (Builder $query) => $query
                    ->with(['latestStatusLog.status'])
                    ->where('years', now('Asia/Jakarta')->format('Y'))
                    ->whereRelation('status', 'order', '>=', 7)
                    ->orderByDesc('updated_at')
            )
            ->columns(Finance::columns())
            ->filters(Finance::filters())
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->recordAction('view')
            ->actions([])
            ->bulkActions(Finance::bulkActions());
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
            'index' => Pages\ListFinances::route('/'),
            'create' => Pages\CreateFinance::route('/create'),
            'view' => Pages\ViewFinance::route('/{record}'),
            'edit' => Pages\EditFinance::route('/{record}/edit'),
        ];
    }
}
