<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use JaOcero\ActivityTimeline\Enums\IconAnimation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
use JaOcero\ActivityTimeline\Components\ActivityTitle;
use JaOcero\ActivityTimeline\Components\ActivitySection;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use App\Filament\User\Resources\RegistrationDataResource\Pages;
use App\Filament\User\Resources\RegistrationDataResource\RelationManagers;
use Filament\Tables\Filters\SelectFilter;

class RegistrationDataResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Activity';
    protected static ?string $title = 'Sekolah';
    protected static ?string $navigationLabel = 'Sekolah';
    protected static ?string $modelLabel = 'Sekolah';
    protected static ?string $slug = 'activity-school';
    protected static bool $shouldRegisterNavigation = true;

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
                TextColumn::make('no')
                    ->rowIndex(),
                TextColumn::make('periode')
                    ->label('Periode'),
                TextColumn::make('years')
                    ->label('Tahun'),
                TextColumn::make('schools')
                    ->label('Sekolah')
                    ->searchable(),
                TextColumn::make('education_level')
                    ->label('Jenjang')
                    ->searchable(),
                TextColumn::make('latestStatusLog.status.color')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'green'  => 'green',
                        'blue'   => 'blue',
                        'yellow' => 'yellow',
                        'red'  => 'red',
                    })
                    ->toggleable()
                    ->default('red'),
            ])
            ->filters([
                    Tables\Filters\SelectFilter::make('type')
                        ->label('Program')
                        ->options([
                            'anbk' => 'ANBK',
                            'apps' => 'APPS',
                            'snbt' => 'SNBT',
                        ])
                        ->preload()
                        ->indicator('Program'),
                    Tables\Filters\SelectFilter::make('latestStatusLog.status.color')
                        ->label('Status Warna')
                        ->options([
                            'red'    => 'Merah',
                            'yellow' => 'Kuning',
                            'blue'   => 'Biru',
                            'green'  => 'Hijau',
                        ])
                        ->preload()
                        ->indicator('Status Warna')
                        ->query(function (Builder $query, array $data) {
                            if (empty($data['value'])) {
                                return;
                            }

                            $query->whereHas(
                                'status',
                                fn (Builder $q) =>
                                $q->where('color', $data['value'])
                            );
                        }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_activities')
                            ->label('Progres')
                            ->icon('heroicon-m-clock')
                            ->color('purple')
                            ->url(fn ($record) => RegistrationDataResource::getUrl('activities', ['record' => $record])),

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
            'index' => Pages\ListRegistrationData::route('/'),
            'activities' => Pages\ViewActivities::route('{record}/activities'),
        ];
    }
}
