<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\Datacenter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\EstimateRegisterDatacenter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Datacenter\Monitoring\EstimateRegisterDatacenterResource\Pages;
use App\Filament\User\Resources\EstimateRegisterDatacenterResource\RelationManagers;

class EstimateRegisterDatacenterResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'Rekap Berbasis Pendaftaran';
    protected static ?string $navigationGroup = 'Rekap Datacenter';
    protected static ?string $navigationLabel = 'Pendaftaran';
    protected static ?string $modelLabel = 'Rekap Berbasis Pendaftaran';
    protected static ?string $slug = 'rekap-register';
    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Datacenter::getRoles());
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
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('date_register', 'asc'))
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
            'index' => Pages\ListEstimateRegisterDatacenters::route('/'),
        ];
    }
}
