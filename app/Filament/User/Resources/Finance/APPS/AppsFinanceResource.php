<?php

namespace App\Filament\User\Resources\Finance\APPS;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\AppsFinance;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Components\Finance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\FinanceExporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Finance\APPS\AppsFinanceResource\Pages;
use App\Filament\User\Resources\AppsFinanceResource\RelationManagers;

class AppsFinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Finance';
    protected static ?string $title = 'APPS';
    protected static ?string $navigationLabel = 'APPS';
    protected static ?string $modelLabel = 'APPS';
    protected static ?string $slug = 'apps-finance';
    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Finance::getRoles());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                Finance::formSchema(options: [
                    'nameRegister' => 'APPS',
                    'DescriptionRegister' => 'ASESMEN PSIKOTES POTENSI SISWA'
                ])
            );
    }
    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'apps')->orderBy('date_register', 'asc'))
            ->columns(
                Finance::columns()
            )
            ->filters(Finance::filters())
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
                    ->exporter(FinanceExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(Finance::infolist());
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
            'index' => Pages\ListAppsFinances::route('/'),
            'create' => Pages\CreateAppsFinance::route('/create'),
            'edit' => Pages\EditAppsFinance::route('/{record}/edit'),
            'view' => Pages\ViewAppsFinance::route('/{record}'),
        ];
    }
}
