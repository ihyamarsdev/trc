<?php

namespace App\Filament\User\Resources\Salesforce\APPS;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\AppsSalesForce;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\SalesForce;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\SalesforceExporter;
use App\Models\{SchoolYear, RegistrationData};
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Salesforce\APPS\AppsSalesForceResource\Pages;
use App\Filament\User\Resources\AppsSalesForceResource\RelationManagers;

class AppsSalesForceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Salesforce';
    protected static ?string $title = 'APPS';
    protected static ?string $navigationLabel = 'APPS';
    protected static ?string $modelLabel = 'APPS';
    protected static ?string $slug = 'apps-salesforce';
    protected static bool $shouldRegisterNavigation = true;


    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Salesforce::getRoles());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(SalesForce::schema(options: [
                'nameRegister' => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA'
            ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'apps')->when(!Auth::user()->hasRole(['admin']), function ($query) {
                // Assuming you have a 'user_id' field in your 'apps' table
                return $query->where('users_id', Auth::id());
            })->orderBy('implementation_estimate', 'asc'))
            ->columns(
                SalesForce::columns()
            )
            ->filters(SalesForce::filters())
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    // Tables\Actions\EditAction::make(),
                    // Tables\Actions\DeleteAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppsSalesForces::route('/'),
            'create' => Pages\CreateAppsSalesForce::route('/create'),
            // 'edit' => Pages\EditAppsSalesForce::route('/{record}/edit'),
        ];
    }
}
