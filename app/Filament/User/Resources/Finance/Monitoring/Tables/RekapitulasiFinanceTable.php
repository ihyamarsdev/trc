<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Tables;

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

class RekapitulasiFinanceTable
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
                self::progressColumn('invoice', 'INVOICE', 11),
                self::progressColumn('check_transfer', 'TRANSFER', 12),
                self::progressColumn('spj', 'SPJ', 13),
                self::progressColumn('kirim_invoice', 'KIRIM INV', 14),
                self::progressColumn('support_sekolah', 'SUPPORT', 15),
                self::progressColumn('pesanan_selesai', 'SELESAI', 16),
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
