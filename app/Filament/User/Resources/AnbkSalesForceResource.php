<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\AnbkSalesForce;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\SalesForce;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\AnbkSalesForceResource\Pages;
use App\Filament\User\Resources\AnbkSalesForceResource\RelationManagers;

class AnbkSalesForceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Salesforce';
    protected static ?string $title = 'ANBK';
    protected static ?string $navigationLabel = 'ANBK';
    protected static ?string $modelLabel = 'ANBK';
    protected static ?string $slug = 'anbk-salesforce';
    protected static bool $shouldRegisterNavigation = true;


    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('salesforce');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(SalesForce::schema(options: [
                'nameRegister' => 'ANBK',
                'DescriptionRegister' => 'ASESMEN NASIONAL BERBASIS KOMPUTER'
            ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'anbk')->orderBy('implementation_estimate', 'asc'))
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
                    ->indicator('Periode'),
                Tables\Filters\SelectFilter::make('school_years_id')
                    ->label('Tahun Ajaran')
                    ->options(SchoolYear::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->indicator('Tahun Ajaran'),
                    ])
            // ], layout: Tables\Enums\FiltersLayout::AboveContent)
            // ->filtersFormColumns(2)
            ->persistFiltersInSession()
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
            'index' => Pages\ListAnbkSalesForces::route('/'),
            'create' => Pages\CreateAnbkSalesForce::route('/create'),
            'edit' => Pages\EditAnbkSalesForce::route('/{record}/edit'),
        ];
    }
}
