<?php

namespace App\Filament\User\Resources\Finance;

use App\Filament\User\Resources\Finance\Forms\FinanceForm;
use App\Filament\User\Resources\Finance\Pages\CreateFinance;
use App\Filament\User\Resources\Finance\Pages\EditFinance;
use App\Filament\User\Resources\Finance\Pages\ListFinances;
use App\Filament\User\Resources\Finance\Pages\ViewFinance;
use App\Filament\User\Resources\Finance\Tables\FinanceTable;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class FinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-m-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $title = 'Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?string $modelLabel = 'Finance Database';

    protected static ?string $slug = 'database-finance';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Gate::allows('ViewAny:FinanceResource');
    }

    public static function form(Schema $schema): Schema
    {
        return FinanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinanceTable::configure($table)
            ->deferLoading()
            ->poll('5s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->withMax('activity', 'id')
                    ->where('years', now('Asia/Jakarta')->format('Y'))
                    ->whereRelation('status', 'order', '>=', 7)
                    ->orderByDesc('updated_at')
            )
            ->filters([])
            ->recordAction('view')
            ->recordActions([])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => ListFinances::route('/'),
            'create' => CreateFinance::route('/create'),
            'view' => ViewFinance::route('/{record}'),
            'edit' => EditFinance::route('/{record}/edit'),
        ];
    }
}
