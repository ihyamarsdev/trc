<?php

namespace App\Filament\User\Resources\Admin;

use App\Filament\User\Resources\Admin\Forms\AdminForm;
use App\Filament\User\Resources\Admin\Pages\CreateAdmin;
use App\Filament\User\Resources\Admin\Pages\EditAdmin;
use App\Filament\User\Resources\Admin\Pages\ListAdmins;
use App\Filament\User\Resources\Admin\Pages\ViewAdmin;
use App\Filament\User\Resources\Admin\Tables\AdminTable;
use App\Models\RegistrationData;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class AdminResource extends Resource
{
    use HasShieldFormComponents;

    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $title = 'Admin Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?string $modelLabel = 'Admin Database';

    protected static ?string $slug = 'admin-database';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return Gate::allows('ViewAny:AdminResource');
    }

    public static function form(Schema $schema): Schema
    {
        return AdminForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminTable::configure($table)
            ->deferLoading()
            ->poll('5s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->extremePaginationLinks()
            ->modifyQueryUsing(
                fn (Builder $query) => $query->withMax('activity', 'id')
                    ->orderByDesc('updated_at')
            )
            ->recordAction('view')
            ->recordActions([])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'edit' => EditAdmin::route('/{record}/edit'),
            'view' => ViewAdmin::route('/{record}'),
        ];
    }
}
