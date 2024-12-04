<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\SnbtDatacenter;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\Datacenter;
use App\Filament\Components\SalesForce;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\DatacenterExporter;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\SnbtDatacenterResource\Pages;
use App\Filament\User\Resources\SnbtDatacenterResource\RelationManagers;

class SnbtDatacenterResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Datacenter';
    protected static ?string $title = 'SNBT';
    protected static ?string $navigationLabel = 'SNBT';
    protected static ?string $modelLabel = 'SNBT';
    protected static ?string $slug = 'snbt-datacenter';
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(['datacenter', 'admin']);
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
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'snbt')->orderBy('date_register', 'asc'))
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(DatacenterExporter::class)
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
            'index' => Pages\ListSnbtDatacenters::route('/'),
            'edit' => Pages\EditSnbtDatacenter::route('/{record}/edit'),
        ];
    }
}
