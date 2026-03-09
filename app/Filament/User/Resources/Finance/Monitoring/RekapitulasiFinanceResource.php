<?php

namespace App\Filament\User\Resources\Finance\Monitoring;

use App\Filament\User\Resources\Finance\Monitoring\Pages\ListRekapitulasiFinances;
use App\Filament\User\Resources\Finance\Monitoring\Pages\ViewRekapitulasiFinance;
use App\Filament\User\Resources\Finance\Monitoring\Tables\RekapitulasiFinanceTable;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class RekapitulasiFinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Rekapitulasi';

    protected static ?string $modelLabel = 'Rekapitulasi Finance';

    protected static ?string $slug = 'rekapitulasi-finance';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Gate::allows('ViewAny:RekapitulasiFinanceResource');
    }

    public static function table(Table $table): Table
    {
        return RekapitulasiFinanceTable::configure($table);
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
            'index' => ListRekapitulasiFinances::route('/'),
            'view' => ViewRekapitulasiFinance::route('/{record}'),
        ];
    }
}
