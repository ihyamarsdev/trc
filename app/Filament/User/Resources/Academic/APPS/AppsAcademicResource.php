<?php

namespace App\Filament\User\Resources\Academic\APPS;

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
use App\Filament\User\Resources\Academic\APPS\AppsAcademicResource\Pages;
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
        return Auth::user()->hasRole(Academic::getRoles());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Academic::formSchema(options: [
                'nameRegister' => 'APPS',
                'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA',
                'radio_name_1' => 'students_download',
                'radio_name_2' => 'schools_download',
                'radio_label_1' => 'Download Siswa',
                'radio_label_2' => 'Download Sekolah'
            ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'apps')->orderBy('implementation_estimate', 'asc'))
            ->columns(
                Academic::columns()
            )
            ->filters(Academic::filters())
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
            ->schema(Academic::infolist(options: [
                'radio_name_1' => 'students_download',
                'radio_name_2' => 'schools_download',
                'radio_label_1' => 'Download Siswa',
                'radio_label_2' => 'Download Sekolah'
            ]));
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
