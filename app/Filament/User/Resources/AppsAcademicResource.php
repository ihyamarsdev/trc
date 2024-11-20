<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\AppsAcademic;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\Academic;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\AcademicExporter;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\AppsAcademicResource\Pages;
use App\Filament\User\Resources\AppsAcademicResource\RelationManagers;

class AppsAcademicResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Akademik';
    protected static ?string $title = 'APPS';
    protected static ?string $navigationLabel = 'APPS';
    protected static ?string $modelLabel = 'APPS';
    protected static ?string $slug = 'apps-academic';
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('academic');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('APPS')
                ->description('ASESMEN PSIKOTES POTENSI SISWA')
                ->schema([
                    Forms\Components\DatePicker::make('group')
                        ->label('Grup')
                        ->required(),
                    Forms\Components\DatePicker::make('bimtek')
                        ->label('Bimtek')
                        ->required(),
                    Forms\Components\TextInput::make('account_count_created')
                        ->label('Akun Dibuat')
                        ->required()
                        ->live(debounce: 500)
                        ->default('0')
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            Academic::getDifference($get, $set);
                        }),
                    Forms\Components\TextInput::make('implementer_count')
                        ->label('Pelaksanaan')
                        ->required()
                        ->live(debounce: 500)
                        ->default('0')
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            Academic::getDifference($get, $set);
                        }),
                    Forms\Components\TextInput::make('difference')
                        ->label('Selisih')
                        ->readOnly()
                        ->numeric()
                        ->minValue(0)
                        ->live(),
                ])->columns(2),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Radio::make('students_download')
                        ->label('Download Siswa')
                        ->options([
                            'ya' => 'Ya',
                            'tidak' => 'Tidak',
                        ])
                        ->inline(),
                    Forms\Components\Radio::make('schools_download')
                        ->label('Download Sekolah')
                        ->options([
                            'ya' => 'Ya',
                            'tidak' => 'Tidak',
                        ])
                        ->inline(),
                    Forms\Components\DatePicker::make('counselor_consultation_date')
                        ->label('Konsul BK')
                        ->required(),
                    Forms\Components\DatePicker::make('student_consultation_date')
                        ->label('Konsul Siswa')
                        ->required(),
                ])->columns(2),
                ]
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'apps')->orderBy('date_register', 'asc'))
            ->columns(
                Academic::columns()
            )
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Januari - Juni' => 'Januari - Juni',
                        'Juli - Desember' => 'Juli - Desember',
                    ])
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('school_years_id')
                    ->label('Tahun Ajaran')
                    ->options(SchoolYear::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
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
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(AcademicExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
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
                        Infolists\Components\Fieldset::make('Periode')
                            ->schema([
                                Infolists\Components\TextEntry::make('periode')
                                        ->label('Periode'),
                                Infolists\Components\TextEntry::make('school_years.name')
                                        ->label('Tahun Ajaran'),
                            ]),

                        Infolists\Components\Fieldset::make('Salesforce')
                            ->schema([
                                Infolists\Components\TextEntry::make('users.name')
                                    ->label('User'),
                            ]),

                        Infolists\Components\Fieldset::make('Sekolah')
                            ->schema([
                                Infolists\Components\TextEntry::make('schools')
                                    ->label('Sekolah'),
                                Infolists\Components\TextEntry::make('education_level')
                                    ->label('Jenjang'),
                                Infolists\Components\TextEntry::make('education_level_type')
                                    ->label('Negeri / Swasta'),
                                Infolists\Components\TextEntry::make('student_count')
                                    ->label('Jumlah Siswa'),
                                Infolists\Components\TextEntry::make('provinces')
                                    ->label('Provinsi'),
                                Infolists\Components\TextEntry::make('regencies')
                                    ->label('Kota / Kabupaten'),
                            ]),


                        Infolists\Components\Fieldset::make('Bagan')
                            ->schema([
                                Infolists\Components\TextEntry::make('principal')
                                    ->label('Kepala Sekolah'),
                                Infolists\Components\TextEntry::make('phone_principal')
                                    ->label('No Hp Kepala Sekolah'),
                                Infolists\Components\TextEntry::make('curriculum_deputies.name')
                                    ->label('Wakakurikulum'),
                                Infolists\Components\TextEntry::make('curriculum_deputies.phone')
                                    ->label('No Hp Wakakurikulum'),
                                Infolists\Components\TextEntry::make('counselor_coordinators.name')
                                    ->label('Koordinator BK'),
                                Infolists\Components\TextEntry::make('counselor_coordinators.phone')
                                    ->label('No Hp Koordinator BK'),
                                Infolists\Components\TextEntry::make('proctors.name')
                                    ->label('Proktor'),
                                Infolists\Components\TextEntry::make('proctors.phone')
                                    ->label('No Hp Proktor'),
                            ]),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('date_register')
                                    ->label('Tanggal Pendaftaran')
                                    ->dateTime('l, jS F Y'),
                                Infolists\Components\TextEntry::make('implementation_estimate')
                                    ->label('Estimasi Pelaksanaan')
                                    ->dateTime('l, jS F Y'),
                            ]),
                        ]),
                Infolists\Components\Section::make('Academic')
                    ->description('Detail Data Academik')
                    ->schema([

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('group')
                                    ->label('Grup')
                                    ->dateTime('l, jS F Y'),
                                Infolists\Components\TextEntry::make('bimtek')
                                    ->label('Bimtek')
                                    ->dateTime('l, jS F Y'),
                            ]),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('account_count_created')
                                    ->label('Akun Dibuat'),
                                Infolists\Components\TextEntry::make('implementer_count')
                                    ->label('Pelaksanaan'),
                                Infolists\Components\TextEntry::make('difference')
                                    ->label('Selisih'),
                            ]),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\IconEntry::make('students_download')
                                    ->label('Download Siswa')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'danger',
                                    }),
                                Infolists\Components\IconEntry::make('schools_download')
                                    ->label('Download Sekolah')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ya' => 'heroicon-s-check-circle',
                                        'tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'ya' => 'success',
                                        'tidak' => 'danger',
                                    }),
                            ]),

                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('counselor_consultation_date')
                                    ->label('Konsul BK')
                                    ->dateTime('l, jS F Y'),
                                Infolists\Components\TextEntry::make('student_consultation_date')
                                    ->label('Konsul Siswa')
                                    ->dateTime('l, jS F Y'),
                            ]),

                    ])->columns(2),
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
            'index' => Pages\ListAppsAcademics::route('/'),
            'create' => Pages\CreateAppsAcademic::route('/create'),
            'edit' => Pages\EditAppsAcademic::route('/{record}/edit'),
            'view' => Pages\ViewAppsAcademic::route('/{record}'),
        ];
    }
}
