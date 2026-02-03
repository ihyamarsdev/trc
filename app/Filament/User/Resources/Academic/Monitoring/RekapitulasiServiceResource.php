<?php

namespace App\Filament\User\Resources\Academic\Monitoring;

use App\Filament\Components\Academic;
use App\Filament\User\Resources\Academic\Monitoring\RekapitulasiServiceResource\Pages;
use App\Models\RegistrationData;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RekapitulasiServiceResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Service';

    protected static ?string $navigationLabel = 'Rekapitulasi';

    protected static ?string $modelLabel = 'rekapitulasi';

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Academic::getRoles());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([50, 100, 200])
            ->columns([
                TextColumn::make('row_number')
                    ->label('No')
                    ->alignCenter()
                    ->getStateUsing(fn ($rowLoop) => $rowLoop->iteration),
                TextColumn::make('schools')
                    ->label('Schools')
                    ->searchable(),
                IconColumn::make('group')
                    ->label('GRUP')
                    ->alignCenter()
                    ->icon(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 3 ? 'heroicon-s-check' : 'heroicon-s-x-mark')
                    ->color(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 3 ? 'success' : 'danger')
                    ->default(false),
                IconColumn::make('bimtek')
                    ->label('BIMTEK')
                    ->alignCenter()
                    ->icon(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 4 ? 'heroicon-s-check' : 'heroicon-s-x-mark')
                    ->color(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 4 ? 'success' : 'danger')
                    ->default(false),
                IconColumn::make('account_count_created')
                    ->label('AKUN')
                    ->alignCenter()
                    ->icon(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 6 ? 'heroicon-s-check' : 'heroicon-s-x-mark')
                    ->color(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 6 ? 'success' : 'danger')
                    ->default(false),
                IconColumn::make('implementer_count')
                    ->label('EVENT')
                    ->alignCenter()
                    ->icon(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 7 ? 'heroicon-s-check' : 'heroicon-s-x-mark')
                    ->color(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 7 ? 'success' : 'danger')
                    ->default(false),
                IconColumn::make('students_download')
                    ->label('DOWNLOAD')
                    ->alignCenter()
                    ->icon(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 9 ? 'heroicon-s-check' : 'heroicon-s-x-mark')
                    ->color(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 9 ? 'success' : 'danger')
                    ->default(false),
                IconColumn::make('schools_download')
                    ->label('PM')
                    ->alignCenter()
                    ->icon(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 10 ? 'heroicon-s-check' : 'heroicon-s-x-mark')
                    ->color(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= 10 ? 'success' : 'danger')
                    ->default(false),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, d/m/Y H:i')),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->recordAction('view')
            ->actions([
                // Tables\Actions\EditAction::make(),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([]);
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
            'index' => Pages\ListRekapitulasiServices::route('/'),
            'view' => Pages\ViewRekapitulasiService::route('/{record}'),
        ];
    }
}
