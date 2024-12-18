<?php

namespace App\Filament\User\Resources\Finance\ANBK;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\AnbkFinance;
use Filament\Actions\Action;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Components\Finance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\RegistrationDataExporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Finance\ANBK\AnbkFinanceResource\Pages;
use App\Filament\User\Resources\AnbkFinanceResource\RelationManagers;

class AnbkFinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Finance';
    protected static ?string $title = 'ANBK';
    protected static ?string $navigationLabel = 'ANBK';
    protected static ?string $modelLabel = 'ANBK';
    protected static ?string $slug = 'anbk-finance';
    protected static bool $shouldRegisterNavigation = true;


    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(['finance', 'admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                Finance::formSchema(options: [
                    'nameRegister' => 'ANBK',
                    'DescriptionRegister' => 'ASESMEN NASIONAL BERBASIS KOMPUTER'
                ])
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'anbk')->orderBy('date_register', 'asc'))
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
                        ->exporter(RegistrationDataExporter::class)
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
            'index' => Pages\ListAnbkFinances::route('/'),
            'create' => Pages\CreateAnbkFinance::route('/create'),
            'edit' => Pages\EditAnbkFinance::route('/{record}/edit'),
            'view' => Pages\ViewAnbkFinance::route('/{record}'),
        ];
    }
}
