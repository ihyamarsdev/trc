<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\SnbtSalesForce;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\SalesForce;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\SalesforceExporter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\SnbtSalesForceResource\Pages;
use App\Filament\User\Resources\SnbtSalesForceResource\RelationManagers;
use App\Filament\User\Resources\SnbtSalesForceResource\Pages\EditSnbtSalesForce;
use App\Filament\User\Resources\SnbtSalesForceResource\Pages\ListSnbtSalesForces;
use App\Filament\User\Resources\SnbtSalesForceResource\Pages\CreateSnbtSalesForce;

class SnbtSalesForceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Salesforce';
    protected static ?string $title = 'SNBT';
    protected static ?string $navigationLabel = 'SNBT';
    protected static ?string $modelLabel = 'SNBT';
    protected static ?string $slug = 'snbt-salesforce';
    protected static bool $shouldRegisterNavigation = true;


    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('salesforce');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(SalesForce::schema(options: [
                'nameRegister' => 'SNBT',
                'DescriptionRegister' => 'SELEKSI NASIONAL BERDASARKAN TES'
            ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'snbt')->orderBy('implementation_estimate', 'asc'))
            ->columns(
                SalesForce::columns()
            )
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Januari - Juni' => 'Januari - Juni',
                        'Juli - Desember' => 'Juli - Desember',
                    ])
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('school_years_id')
                    ->label('Tahun Ajaran')
                    ->options(SchoolYear::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
            ])
            ->filtersFormColumns(2)
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSnbtSalesForces::route('/'),
            'create' => Pages\CreateSnbtSalesForce::route('/create'),
            'edit' => Pages\EditSnbtSalesForce::route('/{record}/edit'),
        ];
    }
}
