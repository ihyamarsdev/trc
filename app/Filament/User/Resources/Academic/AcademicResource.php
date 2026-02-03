<?php

namespace App\Filament\User\Resources\Academic;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\Academic;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\AcademicExporter;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Academic\AcademicResource\Pages;
use App\Filament\User\Resources\AcademicResource\RelationManagers;

class AcademicResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Service';
    protected static ?string $title = 'Database';
    protected static ?string $navigationLabel = 'Database';
    protected static ?string $modelLabel = 'database';
    protected static ?string $slug = 'database-service';
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Academic::getRoles());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Academic::formSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('5s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->withMax('activity', 'id')
                    ->where('years', now('Asia/Jakarta')->format('Y'))
                    ->whereRelation('status', fn($q) => $q->whereBetween('order', [2, 10]))
                    ->orderByDesc('updated_at')
            )
            ->columns(
                Academic::columns()
            )
            ->filters(Academic::filters())
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->recordAction('view')
            ->actions([])
            ->bulkActions(Academic::bulkActions());
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
            'index' => Pages\ListAcademics::route('/'),
            'create' => Pages\CreateAcademic::route('/create'),
            'view' => Pages\ViewAcademic::route('/{record}'),
            'edit' => Pages\EditAcademic::route('/{record}/edit'),
        ];
    }
}
