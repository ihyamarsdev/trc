<?php

namespace App\Filament\User\Resources\Finance;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Filament\Components\Finance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\AcademicExporter;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Finance\FinanceResource\Pages;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\Filament\User\Resources\FinanceResource\RelationManagers;

class FinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;
    protected static ?string $navigationIcon = 'heroicon-m-credit-card';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $title = 'Database';
    protected static ?string $navigationLabel = 'Database';
    protected static ?string $modelLabel = 'database';
    protected static ?string $slug = 'database-finance';
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Finance::getRoles());
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
            ->poll('5s')
            ->searchable()
            ->striped()
            ->modifyQueryUsing(
                fn (Builder $query) =>
                $query->withMax('activity', 'id')
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
            ->actions(Finance::actions(), position: ActionsPosition::BeforeColumns)
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
