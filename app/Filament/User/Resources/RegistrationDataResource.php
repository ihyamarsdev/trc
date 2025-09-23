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

class RegistrationDataResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Activity';
    protected static ?string $title = 'School';
    protected static ?string $navigationLabel = 'School';
    protected static ?string $modelLabel = 'School';
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
                    ->label('Sekolah'),
                TextColumn::make('education_level')
                    ->label('Jenjang'),
                TextColumn::make('status_color')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state)) // Kuning/Biru/Hijau
                    ->color(fn (string $state): string => match ($state) {
                        'hijau'  => 'hijau',
                        'biru'   => 'biru',
                        'kuning' => 'kuning',
                        'merah'  => 'merah',
                    })
                ->sortable()
                ->toggleable(),
            ])
            ->filters([
                    //
            ])
            ->actions([
                Tables\Actions\Action::make('view_activities')
                        ->label('Activities')
                        ->icon('heroicon-m-bolt')
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


    public function activityTimelineInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state([
                'activities' => [
                        [
                            'title' => "Published Article 🔥 - <span class='italic font-normal dark:text-success-400 text-success-600'>Published with Laravel Filament and Tailwind CSS</span>",
                            'description' => "<span>Approved and published. Here is the <a href='#' class='font-bold hover:underline dark:text-orange-300'>link.</a></span>",
                            'status' => 'published',
                            'created_at' => now()->addDays(8),
                        ],
                        [
                            'title' => 'Reviewing Article - Final Touches',
                            'description' => "<span class='italic'>Reviewing the article and making it ready for publication.</span>",
                            'status' => '',
                            'created_at' => now()->addDays(5),
                        ],
                        [
                            'title' => "Drafting Article - <span class='text-sm italic font-normal text-purple-800 dark:text-purple-300'>Make it ready for review</span>",
                            'description' => 'Drafting the article and making it ready for review.',
                            'status' => 'drafting',
                            'created_at' => now()->addDays(2),
                        ],
                        [
                            'title' => 'Ideation - Looking for Ideas 🤯',
                            'description' => 'Idea for my article.',
                            'status' => 'ideation',
                            'created_at' => now()->subDays(7),
                        ]
                    ]
            ])
            ->schema([

                ActivitySection::make('activities')
                    ->label('My Activities')
                    ->description('These are the activities that have been recorded.')
                    ->schema([
                        ActivityTitle::make('title')
                            ->placeholder('No title is set')
                            ->allowHtml(), // Be aware that you will need to ensure that the HTML is safe to render, otherwise your application will be vulnerable to XSS attacks.
                        ActivityDescription::make('description')
                            ->placeholder('No description is set')
                            ->allowHtml(),
                        ActivityDate::make('created_at')
                            ->date('F j, Y', 'Asia/Manila')
                            ->placeholder('No date is set.'),
                        ActivityIcon::make('status')
                            ->icon(fn (string | null $state): string | null => match ($state) {
                                'ideation' => 'heroicon-m-light-bulb',
                                'drafting' => 'heroicon-m-bolt',
                                'reviewing' => 'heroicon-m-document-magnifying-glass',
                                'published' => 'heroicon-m-rocket-launch',
                                default => null,
                            })
                            /*
                                You can animate icon with ->animation() method.
                                Possible values : IconAnimation::Ping, IconAnimation::Pulse, IconAnimation::Bounce, IconAnimation::Spin or a Closure
                             */
                            ->animation(IconAnimation::Ping)
                            ->color(fn (string | null $state): string | null => match ($state) {
                                'ideation' => 'purple',
                                'drafting' => 'info',
                                'reviewing' => 'warning',
                                'published' => 'success',
                                default => 'gray',
                            }),
                    ])
                    ->showItemsCount(5) // Show up to 2 items
                    ->showItemsLabel('View Old') // Show "View Old" as link label
                    ->showItemsIcon('heroicon-m-chevron-down') // Show button icon
                    ->showItemsColor('gray') // Show button color and it supports all colors
                    ->aside(false)
                    ->headingVisible(true) // make heading visible or not
                    ->extraAttributes(['class' => 'my-new-class']) // add extra class
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrationData::route('/'),
            'activities' => Pages\ViewColorActivities::route('{record}/activities'),
        ];
    }
}
