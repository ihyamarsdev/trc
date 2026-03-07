<?php

namespace App\Filament\User\Resources\Service\Monitoring\Tables;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RekapitulasiServiceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated([50, 100, 200])
            ->columns([
                TextColumn::make('row_number')
                    ->label('No')
                    ->alignCenter()
                    ->getStateUsing(fn ($rowLoop) => $rowLoop->iteration),
                TextColumn::make('schools')->label('Schools')->searchable(),
                self::progressColumn('group', 'GRUP', 3),
                self::progressColumn('bimtek', 'BIMTEK', 4),
                self::progressColumn('account_count_created', 'AKUN', 6),
                self::progressColumn('implementer_count', 'EVENT', 7),
                self::progressColumn('students_download', 'DOWNLOAD', 9),
                self::progressColumn('schools_download', 'PM', 10),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, d/m/Y H:i')),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('periode')->label('Periode')->options(Periode::list())->preload(),
                SelectFilter::make('education_level')->label('Jenjang')->options(Jenjang::list())->preload()->indicator('Jenjang'),
                SelectFilter::make('type')->label('Program')->options(Program::list())->preload()->indicator('Program'),
                SelectFilter::make('users_id')
                    ->label('User')
                    ->options(fn () => User::query()->pluck('name', 'id')->toArray())
                    ->preload()
                    ->indicator('user'),
                SelectFilter::make('status_color')
                    ->label('Status Warna')
                    ->options([
                        'yellow' => 'Kuning',
                        'blue' => 'Biru',
                        'green' => 'Hijau',
                    ])
                    ->preload()
                    ->indicator('Status Warna')
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }

                        $query->whereHas('status', fn (Builder $q) => $q->where('color', $data['value']));
                    }),
            ])
            ->recordAction('view')
            ->recordActions([], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function progressColumn(string $name, string $label, int $minOrder): IconColumn
    {
        return IconColumn::make($name)
            ->label($label)
            ->alignCenter()
            ->icon(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= $minOrder ? 'heroicon-s-check' : 'heroicon-s-x-mark')
            ->color(fn ($record) => ($record->latestStatusLog?->status?->order ?? 0) >= $minOrder ? 'success' : 'danger')
            ->default(false);
    }
}
