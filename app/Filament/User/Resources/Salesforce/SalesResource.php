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
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\SalesForce;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\SalesforceExporter;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Salesforce\SalesResource\Pages;
use App\Filament\User\Resources\SalesResource\RelationManagers;

class SalesResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    // protected static ?string $navigationGroup = 'Salesforce';
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
            // ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'anbk')->when(!Auth::user()->hasRole(['admin']), function ($query) {
            //     // Assuming you have a 'user_id' field in your 'apps' table
            //     return $query->where('users_id', Auth::id());
            // })->orderBy('implementation_estimate', 'asc'))
            ->modifyQueryUsing(
                fn (Builder $query) =>
                $query->orderBy('implementation_estimate', 'asc')
            )
            ->columns(SalesForce::columns())
            ->filters(SalesForce::filters())
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(SalesforceExporter::class)
                        ->formats([
                            ExportFormat::Xlsx,
                        ]),
                ]),
            ]);
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
