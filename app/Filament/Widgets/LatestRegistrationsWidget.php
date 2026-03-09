<?php

namespace App\Filament\Widgets;

use App\Models\RegistrationData;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestRegistrationsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Sekolah Terbaru')
            ->query(
                RegistrationData::query()
                    ->latest('date_register')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('schools')
                    ->label('Nama Sekolah')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('principal')
                    ->label('Kepala Sekolah')
                    ->searchable(),
                TextColumn::make('student_count')
                    ->label('Jumlah Siswa')
                    ->numeric(),
                TextColumn::make('date_register')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
