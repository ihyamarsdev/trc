<?php

namespace App\Filament\User\Resources\Service\Monitoring;

use App\Filament\User\Resources\Service\Monitoring\Pages\ListRekapitulasiServices;
use App\Filament\User\Resources\Service\Monitoring\Pages\ViewRekapitulasiService;
use App\Filament\User\Resources\Service\Monitoring\Tables\RekapitulasiServiceTable;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class RekapitulasiServiceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Service';

    protected static ?string $navigationLabel = 'Rekapitulasi';

    protected static ?string $modelLabel = 'Rekapitulasi Service';

    public static function canViewAny(): bool
    {
        return Gate::allows('ViewAny:RekapitulasiServiceResource');
    }

    public static function table(Table $table): Table
    {
        return RekapitulasiServiceTable::configure($table);
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
            'index' => ListRekapitulasiServices::route('/'),
            'view' => ViewRekapitulasiService::route('/{record}'),
        ];
    }
}
