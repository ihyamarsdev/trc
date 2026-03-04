<?php

namespace App\Filament\User\Resources\Service;

use App\Filament\User\Resources\Service\Forms\ServiceForm;
use App\Filament\User\Resources\Service\Pages\CreateService;
use App\Filament\User\Resources\Service\Pages\EditService;
use App\Filament\User\Resources\Service\Pages\ListServices;
use App\Filament\User\Resources\Service\Pages\ViewService;
use App\Filament\User\Resources\Service\Tables\ServiceTable;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|\UnitEnum|null $navigationGroup = 'Service';

    protected static ?string $title = 'Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?string $modelLabel = 'Service Database';

    protected static ?string $slug = 'database-service';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ViewAny:ServiceResource') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(ServiceForm::configure())
            ->extraAttributes([
                'onkeydown' => "
                if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
                    event.preventDefault();
                    let focusables = Array.from(document.querySelectorAll('input, select, button, [contenteditable]'));
                    let index = focusables.indexOf(event.target);
                    if (index > -1 && focusables[index + 1]) {
                        focusables[index + 1].focus();
                    }
                }
            ",
            ]);
    }

    public static function table(Table $table): Table
    {
        return ServiceTable::configure($table);
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'view' => ViewService::route('/{record}'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
