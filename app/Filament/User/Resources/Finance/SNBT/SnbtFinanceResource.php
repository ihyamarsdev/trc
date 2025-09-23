<?php

namespace App\Filament\User\Resources\Finance\SNBT;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\SnbtFinance;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Filament\Components\Finance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\FinanceExporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\Finance\SNBT\SnbtFinanceResource\Pages;
use App\Filament\User\Resources\SnbtFinanceResource\RelationManagers;

class SnbtFinanceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Program Finance';
    protected static ?string $title = 'SNBT';
    protected static ?string $navigationLabel = 'SNBT';
    protected static ?string $modelLabel = 'SNBT';
    protected static ?string $slug = 'snbt-finance';
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
                    'nameRegister' => 'SNBT',
                    'DescriptionRegister' => 'SELEKSI NASIONAL BERDASARKAN TES'
                ])
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'snbt')->orderBy('date_register', 'asc'))
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
            'index' => Pages\ListSnbtFinances::route('/'),
            'create' => Pages\CreateSnbtFinance::route('/create'),
            'edit' => Pages\EditSnbtFinance::route('/{record}/edit'),
            'view' => Pages\ViewSnbtFinance::route('/{record}'),
        ];
    }
}
