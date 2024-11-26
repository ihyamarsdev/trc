<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\Datacenter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\EstimateExecutionDatacenter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\EstimateExecutionDatacenterResource\Pages;
use App\Filament\User\Resources\EstimateExecutionDatacenterResource\RelationManagers;

class EstimateExecutionDatacenterResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'Rekap Berbasis Estimasi Pelaksanaan';
    protected static ?string $navigationGroup = 'Rekap Datacenter';
    protected static ?string $navigationLabel = 'Estimasi Pelaksanaan';
    protected static ?string $modelLabel = 'Rekap Berbasis Estimasi Pelaksanaan';
    protected static ?string $slug = 'rekap-execution';
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(['datacenter', 'admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('implementation_estimate', 'asc'))
            ->columns(
                Datacenter::columns()
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
            ->actions([

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
            'index' => Pages\ListEstimateExecutionDatacenters::route('/'),
        ];
    }
}
