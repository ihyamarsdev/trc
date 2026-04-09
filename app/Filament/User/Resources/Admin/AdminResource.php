<?php

namespace App\Filament\User\Resources\Admin;

use App\Filament\Components\Admin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\CreateAdmin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\EditAdmin;
use App\Filament\User\Resources\Admin\AdminResource\Pages\ListAdmins;
use App\Filament\User\Resources\Admin\AdminResource\Pages\ViewAdmin;
use App\Models\RegistrationData;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdminResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-building-library';
    }

    protected static ?string $title = 'Admin Database';

    protected static ?string $navigationLabel = 'Database';

    protected static ?string $modelLabel = 'Database';

    protected static ?string $slug = 'admin-database';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(['admin']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema(Admin::formSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('15s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->extremePaginationLinks()
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['latestStatusLog.status'])
                    ->orderByDesc('updated_at')
            )
            ->columns(Admin::columns())
            ->filters(Admin::filters())
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->recordAction('view')
            ->actions([])
            ->bulkActions(Admin::bulkActions());
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
