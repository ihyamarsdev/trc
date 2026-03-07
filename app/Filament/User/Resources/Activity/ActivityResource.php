<?php

namespace App\Filament\User\Resources\Activity;

use App\Filament\User\Resources\Activity\Forms\ActivityForm;
use App\Filament\User\Resources\Activity\Pages\ListActivity;
use App\Filament\User\Resources\Activity\Pages\ViewActivities;
use App\Filament\User\Resources\Activity\Tables\ActivityTable;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class ActivityResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $title = 'Activity';

    protected static ?string $navigationLabel = 'Activity';

    protected static ?string $modelLabel = 'Activity';

    protected static ?string $slug = 'activity';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return Gate::allows('ViewAny:ActivityResource');
    }

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityTable::configure($table);
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
            'index' => ListActivity::route('/'),
            'activities' => ViewActivities::route('{record}/activities'),
        ];
    }
}
