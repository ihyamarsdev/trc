<?php

namespace App\Filament\User\Resources\Salesforce;

use Filament\Forms;
use Filament\Tables;
use App\Models\Sales;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\SalesForce;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Exports\SalesforceExporter;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages;
use App\Filament\User\Resources\SalesResource\RelationManagers;

class SalesResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Salesforce';
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
            ->schema(SalesForce::schema());
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
                    $query
                        ->where('years', now('Asia/Jakarta')->format('Y'))
                        ->orderBy('implementation_estimate', 'asc')
            )
            ->columns(SalesForce::columns())
            ->filters(SalesForce::filters())
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->actions(SalesForce::actions(), position: ActionsPosition::BeforeColumns)
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
