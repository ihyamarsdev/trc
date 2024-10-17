<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Components\Academic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Creasi\Nusa\Models\{Province, Regency};
use App\Filament\Resources\AcademicResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AcademicResource\RelationManagers;
use Filament\Infolists;

class AcademicResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Rekapitulation';
    protected static ?string $title = 'Akademik';
    protected static ?string $navigationLabel = 'Akademik';
    protected static ?string $modelLabel = 'Akademik';
    protected static ?string $slug = 'academic';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                                Infolists\Components\TextEntry::make('schools.name')
                                    ->label('Sekolah'),
                                Infolists\Components\TextEntry::make('schools.education_level')
                                    ->label('Jenjang'),
                                Infolists\Components\TextEntry::make('schools.education_level_type')
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
                                Infolists\Components\TextEntry::make('schools.principal')
                                    ->label('Kepala Sekolah'),
                                Infolists\Components\TextEntry::make('schools.phone_principal')
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
                                        'Ya' => 'heroicon-s-check-circle',
                                        'Tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'Ya' => 'success',
                                        'Tidak' => 'warning',
                                    }),
                                Infolists\Components\IconEntry::make('schools_download')
                                    ->label('Download Sekolah')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'Ya' => 'heroicon-s-check-circle',
                                        'Tidak' => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'Ya' => 'success',
                                        'Tidak' => 'warning',
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
            'index' => Pages\ListAcademics::route('/'),
            'create' => Pages\CreateAcademic::route('/create'),
            'edit' => Pages\EditAcademic::route('/{record}/edit'),
            'view' => Pages\ViewAcademic::route('/{record}'),
        ];
    }
}
