<?php

namespace App\Filament\User\Resources\Timelines;

use App\Filament\User\Resources\Timelines\Forms\TimelineForm;
use App\Filament\User\Resources\Timelines\Pages\ListTimelines;
use App\Filament\User\Resources\Timelines\Pages\ViewTimeline;
use App\Filament\User\Resources\Timelines\Tables\TimelineTable;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class TimelineResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $title = 'Database';

    protected static ?string $modelLabel = 'Timeline';

    protected static ?string $slug = 'database-timeline';

    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return Gate::allows('ViewAny:TimelineResource');
    }

    public static function form(Schema $schema): Schema
    {
        return TimelineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimelineTable::configure($table);
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
            'index' => ListTimelines::route('/'),
            // 'create' => Pages\CreateTimeline::route('/create'),
            'view' => ViewTimeline::route('/{record}'),
            // 'edit' => Pages\EditTimeline::route('/{record}/edit'),
        ];
    }
}
