<?php

namespace App\Filament\User\Resources\Admin;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use App\Filament\Components\Admin;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\AdminResource\Pages;
use App\Filament\User\Resources\AdminResource\RelationManagers;
use App\Filament\User\Resources\Admin\AdminResource\Pages\{ListAdmins, CreateAdmin, EditAdmin, ViewAdmin};

class AdminResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Admin::formSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('5s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->extremePaginationLinks()
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->withMax('activity', 'id')
                    ->orderByDesc('updated_at')
            )
            ->columns(Admin::columns())
            ->filters(Admin::filters())
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->actions(Admin::actions(), position: ActionsPosition::BeforeColumns)
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
