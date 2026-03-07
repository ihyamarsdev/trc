<?php

namespace App\Filament\User\Resources\Salesforce;

use App\Filament\User\Resources\Salesforce\Forms\SalesForm;
use App\Filament\User\Resources\Salesforce\Infolists\SalesInfolist;
use App\Filament\User\Resources\Salesforce\Pages\CreateSales;
use App\Filament\User\Resources\Salesforce\Pages\EditSales;
use App\Filament\User\Resources\Salesforce\Pages\ListSales;
use App\Filament\User\Resources\Salesforce\Pages\ViewSales;
use App\Filament\User\Resources\Salesforce\Tables\SalesTable;
use App\Models\RegistrationData;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class SalesResource extends Resource
{
    use HasShieldFormComponents;

    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Salesforce';

    protected static ?string $title = 'Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?string $modelLabel = 'Sales';

    protected static ?string $slug = 'database-salesforce';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Gate::allows('ViewAny:SalesResource');
    }

    public static function form(Schema $schema): Schema
    {
        return SalesForm::configure($schema)
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
        return SalesTable::configure($table)
            ->deferLoading()
            ->poll('5s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->recordAction('view')
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->where('years', now('Asia/Jakarta')->format('Y'))
                    ->when(
                        fn ($query) => $query->where('users_id', Filament::auth()->id())
                    )
                    ->orderBy('implementation_estimate', 'asc')
            )
            ->recordActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SalesInfolist::configure($schema);
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
            'index' => ListSales::route('/'),
            'create' => CreateSales::route('/create'),
            'view' => ViewSales::route('/{record}'),
            'edit' => EditSales::route('/{record}/edit'),
        ];
    }
}
