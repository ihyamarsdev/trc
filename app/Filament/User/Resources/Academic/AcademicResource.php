<?php

namespace App\Filament\User\Resources\Academic;

use App\Filament\User\Resources\Academic\Forms\AcademicForm;
use App\Filament\User\Resources\Academic\Pages\CreateAcademic;
use App\Filament\User\Resources\Academic\Pages\EditAcademic;
use App\Filament\User\Resources\Academic\Pages\ListAcademics;
use App\Filament\User\Resources\Academic\Pages\ViewAcademic;
use App\Filament\User\Resources\Academic\Tables\AcademicTable;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AcademicResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|\UnitEnum|null $navigationGroup = 'Service';

    protected static ?string $title = 'Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?string $modelLabel = 'Academic Database';

    protected static ?string $slug = 'database-service';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ViewAny:AcademicResource') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(AcademicForm::configure())
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
        return AcademicTable::configure($table);
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
            'index' => ListAcademics::route('/'),
            'create' => CreateAcademic::route('/create'),
            'view' => ViewAcademic::route('/{record}'),
            'edit' => EditAcademic::route('/{record}/edit'),
        ];
    }
}
