<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\TimelineResource\Pages;
use App\Filament\User\Resources\TimelineResource\RelationManagers;
use App\Models\RegistrationData;
use App\Models\Timeline;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TimelineResource extends Resource
{
    protected static ?string $model = RegistrationData::class;
    protected static ?string $title = 'Database';
    protected static ?string $modelLabel = 'database';
    protected static ?string $slug = 'database-timeline';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTimelines::route('/'),
            'create' => Pages\CreateTimeline::route('/create'),
            'view' => Pages\ViewTimeline::route('/{record}'),
            'edit' => Pages\EditTimeline::route('/{record}/edit'),
        ];
    }
}
