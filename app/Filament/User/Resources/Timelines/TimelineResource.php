<?php

namespace App\Filament\User\Resources\Timelines;

use App\Filament\User\Resources\Timelines\Pages\ListTimelines;
use App\Filament\User\Resources\Timelines\Pages\ViewTimeline;
use App\Models\RegistrationData;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TimelineResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $title = 'Database';

    protected static ?string $modelLabel = 'Timeline';

    protected static ?string $slug = 'database-timeline';

    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ViewAny:TimelineResource') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListTimelines::route('/'),
            // 'create' => Pages\CreateTimeline::route('/create'),
            'view' => ViewTimeline::route('/{record}'),
            // 'edit' => Pages\EditTimeline::route('/{record}/edit'),
        ];
    }
}
