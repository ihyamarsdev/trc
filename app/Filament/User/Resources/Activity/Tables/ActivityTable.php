<?php

namespace App\Filament\User\Resources\Activity\Tables;

use App\Filament\Enum\Program;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityTable
{
    public static function configure(Table $table): Table
    {
        $currentUserId = Filament::auth()->id();
        $isSalesWithoutAdmin = $currentUserId !== null
            && User::query()
                ->whereKey($currentUserId)
                ->whereHas('roles', fn (Builder $query) => $query->where('name', 'sales'))
                ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'admin'))
                ->exists();

        return $table
            ->deferLoading()
            ->poll('3s')
            ->searchable()
            ->striped()
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->withMax('activity', 'id')
                    ->orderByDesc('updated_at')
                    ->when(
                        $isSalesWithoutAdmin,
                        fn (Builder $query) => $query->where('users_id', $currentUserId)
                    )
            )
            ->columns([
                TextColumn::make('schools')->label('Sekolah')->searchable()->wrap()->sortable(),
                TextColumn::make('type')->label('Program')->badge()->extraAttributes(['class' => 'uppercase']),
                TextColumn::make('periode')->label('Periode')->badge()->extraAttributes(['class' => 'uppercase']),
                TextColumn::make('latestStatusLog.status.name')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn ($record) => match ($record->latestStatusLog?->status?->color) {
                            'green' => 'success',
                            'blue' => 'blue',
                            'yellow' => 'warning',
                            'red' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->placeholder('Belum Ada Status'),
                TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, d/m/Y H:i'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Program')
                    ->options(Program::list())
                    ->preload()
                    ->indicator('Program'),
                SelectFilter::make('latestStatusLog.status.color')
                    ->label('Status Warna')
                    ->options([
                        'red' => 'Merah',
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
            ->recordUrl(fn ($record) => \App\Filament\User\Resources\Activity\ActivityResource::getUrl('activities', ['record' => $record]))
            ->recordActions([], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
