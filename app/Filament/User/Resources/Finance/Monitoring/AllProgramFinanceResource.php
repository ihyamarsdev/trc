<?php

namespace App\Filament\User\Resources\Finance\Monitoring;

use App\Filament\User\Resources\Finance\Monitoring\Forms\AllProgramFinanceForm;
use App\Filament\User\Resources\Finance\Monitoring\Pages\ListAllProgramFinances;
use App\Filament\User\Resources\Finance\Monitoring\Tables\AllProgramFinanceTable;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class AllProgramFinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $title = 'Rekap All Program';

    protected static string|\UnitEnum|null $navigationGroup = 'Rekap Finance';

    protected static ?string $navigationLabel = 'All Program';

    protected static ?string $modelLabel = 'Rekap All Program';

    protected static ?string $slug = 'rekap-all-program-finance';

    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return Gate::allows('ViewAny:AllProgramFinanceResource');
    }

    public static function form(Schema $schema): Schema
    {
        return AllProgramFinanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AllProgramFinanceTable::configure($table);
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
            'index' => ListAllProgramFinances::route('/'),
        ];
    }
}
