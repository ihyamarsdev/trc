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
use App\Models\{SchoolYear, CurriculumDeputies, CounselorCoordinator, Proctors, Schools};
use App\Filament\Resources\{SchoolYearResource, CurriculumDeputiesResource, CounselorCoordinatorResource, ProctorsResource, SchoolsResource};


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
                        Select::make('school_years_id')
                            ->label('Tahun Ajaran')
                            ->required()
                            ->relationship('school_years', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm(fn (Form $form) => SchoolYearResource::form($form))
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
                        Select::make('curriculum_deputies_id')
                            ->label('Wakakurikulum')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('curriculum_deputies', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name')->orderBy('phone'), )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} - No HP: {$record->phone}")
                            ->createOptionForm(fn (Form $form) => CurriculumDeputiesResource::form($form)),
                        Select::make('counselor_coordinators_id')
                            ->label('Koordinator BK ')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('counselor_coordinators', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name')->orderBy('phone'), )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} - No HP: {$record->phone}")
                            ->createOptionForm(fn (Form $form) => CounselorCoordinatorResource::form($form)),
                        Select::make('proctors_id')
                            ->label('Proctor ')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('proctors', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name')->orderBy('phone'), )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} - No HP: {$record->phone}")
                            ->createOptionForm(fn (Form $form) => ProctorsResource::form($form)),
                        TextInput::make('student_count')
                            ->label('Jumlah Siswa')
                            ->required()
                            ->numeric(),
                        DatePicker::make('implementation_estimate')
                            ->label('Estimasi Pelaksanaan')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Sekolah')
                    ->description('Pilih Data Sekolah')
                    ->schema([
                        Select::make('schools_id')
                            ->label('Name')
                            ->options(Schools::all()->pluck('name', 'id'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $item = Schools::find($state);
                                $set('phone', $item->phone);
                                $set('education_level', $item->education_level);
                                $set('education_level_type', $item->education_level_type);
                                $set('principal', $item->principal);
                                $set('phone_principal', $item->phone_principal);
                            })
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Form $form) => SchoolsResource::form($form))
                            ->required()
                            ->createOptionUsing(function (array $data): int {
                                $schools = Schools::create([
                                    'name' => $data['name'],
                                    'phone' => $data['phone'],
                                    'education_level' => $data['education_level'],
                                    'education_level_type' => $data['education_level_type'],
                                    'principal' => $data['principal'],
                                    'phone_principal' => $data['phone_principal'],
                                ]);
                                return $schools->id;
                            }),
                                TextInput::make('phone')
                                    ->label('No Handphone Sekolah')
                                    ->disabled(),
                                TextInput::make('education_level')
                                    ->label('Jenjang')
                                    ->disabled(),
                                TextInput::make('education_level_type')
                                    ->label('Negeri / Swasta')
                                    ->disabled(),
                                TextInput::make('principal')
                                    ->label('Kepala Sekolah')
                                    ->disabled(),
                                TextInput::make('phone_principal')
                                    ->label('No Handphone Kepala Sekolah')
                                    ->disabled(),
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
                Tables\Filters\SelectFilter::make('school_years_id')
                    ->label('Tahun Ajaran')
                    ->options(SchoolYear::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->indicator('Tahun Ajaran'),
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
