<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Spatie\Activitylog\Models\Activity;

class LatestActivityLogsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Log Aktivitas Terbaru')
            ->query(
                Activity::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('causer.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Aktivitas')
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->label('Modul')
                    ->formatStateUsing(fn (?string $state): string => match (true) {
                        $state === null => '-',
                        str_contains($state, 'User') => 'Manajemen Pengguna',
                        str_contains($state, 'RegistrationData') => 'Pendaftaran',
                        default => class_basename($state),
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
