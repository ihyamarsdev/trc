<?php

namespace App\Filament\Resources;

use stdClass;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\Datacenter;
use App\Filament\Components\SalesForce;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Creasi\Nusa\Models\{Province, Regency};
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SalesForceResource\Pages;
use Filament\Forms\Components\{Select, TextInput, DatePicker};
use App\Filament\Components\SalesForce as ComponentsSalesForce;
use App\Filament\Resources\SalesForceResource\RelationManagers;
use Filament\Infolists\Components\{TextEntry, Group, Grid, Fieldset, IconEntry};


class SalesForceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Rekapitulation';
    protected static ?string $title = 'Sales Force';
    protected static ?string $navigationLabel = 'Sales Force';
    protected static ?string $modelLabel = 'Sales Force';
    protected static ?string $slug = 'salesforce';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Periode')
                    ->description('Pilih Periode dan Tahun Ajaran')
                    ->schema([
                        Select::make('periode')
                            ->label('Periode')
                            ->required()
                            ->options([
                                'Januari - Juni' => 'Januari - Juni',
                                'Juli - Desember' => 'Juli - Desember',
                            ]),
                ])->columns(2),

                Forms\Components\Section::make()
                    ->schema([
                        DatePicker::make('date_register')
                            ->label('Tanggal Pendaftaran')
                            ->required(),
                        Select::make('provinces')
                            ->label('Provinsi')
                            ->required()
                            ->options(Province::all()->pluck('name', 'name'))
                            ->searchable(),
                        Select::make('regencies')
                            ->label('Kabupaten')
                            ->required()
                            ->options(Regency::all()->pluck('name', 'name'))
                            ->searchable(),
                        TextInput::make('student_count')
                            ->label('Jumlah Siswa')
                            ->required()
                            ->numeric(),
                        DatePicker::make('implementation_estimate')
                            ->label('Estimasi Pelaksanaan')
                            ->required(),
                    ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns(
                SalesForce::columns()
            )
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Januari - Juni' => 'Januari - Juni',
                        'Juli - Desember' => 'Juli - Desember',
                    ])
                    ->preload()
                    ->indicator('Periode'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Datacenter')
                    ->description('Detail data dari datacenter')
                    ->schema([
                        Fieldset::make('Periode')
                            ->schema([
                                TextEntry::make('periode')
                                        ->label('Periode'),
                                TextEntry::make('school_years.name')
                                        ->label('Tahun Ajaran'),
                            ]),

                        Fieldset::make('Sekolah')
                            ->schema([
                                TextEntry::make('schools.name')
                                    ->label('Sekolah'),
                                TextEntry::make('schools.education_level')
                                    ->label('Jenjang'),
                                TextEntry::make('schools.education_level_type')
                                    ->label('Negeri / Swasta'),
                                TextEntry::make('student_count')
                                    ->label('Jumlah Siswa'),
                                TextEntry::make('provinces')
                                    ->label('Provinsi'),
                                TextEntry::make('regencies')
                                    ->label('Kota / Kabupaten'),
                            ]),


                        Fieldset::make('Bagan')
                            ->schema([
                                TextEntry::make('schools.principal')
                                    ->label('Kepala Sekolah'),
                                TextEntry::make('schools.phone_principal')
                                    ->label('No Hp Kepala Sekolah'),
                                TextEntry::make('curriculum_deputies.name')
                                    ->label('Wakakurikulum'),
                                TextEntry::make('curriculum_deputies.phone')
                                    ->label('No Hp Wakakurikulum'),
                                TextEntry::make('counselor_coordinators.name')
                                    ->label('Koordinator BK'),
                                TextEntry::make('counselor_coordinators.phone')
                                    ->label('No Hp Koordinator BK'),
                                TextEntry::make('proctors.name')
                                    ->label('Proktor'),
                                TextEntry::make('proctors.phone')
                                    ->label('No Hp Proktor'),
                            ]),

                        Fieldset::make('')
                            ->schema([
                                TextEntry::make('date_register')
                                    ->label('Tanggal Pendaftaran')
                                    ->dateTime('l, jS F Y'),
                                TextEntry::make('implementation_estimate')
                                    ->label('Estimasi Pelaksanaan')
                                    ->dateTime('l, jS F Y'),
                            ]),
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
            'index' => Pages\ListSalesForces::route('/'),
            'create' => Pages\CreateSalesForce::route('/create'),
            'edit' => Pages\EditSalesForce::route('/{record}/edit'),
            'view' => Pages\ViewSalesForce::route('/{record}'),
        ];
    }
}
