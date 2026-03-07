<?php

namespace App\Filament\User\Resources\Service\Tables;

use App\Filament\Enum\Jenjang;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('5s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->withMax('activity', 'id')
                    ->where('years', now('Asia/Jakarta')->format('Y'))
                    ->whereRelation('status', fn ($q) => $q->whereBetween('order', [2, 10]))
                    ->orderByDesc('updated_at')
            )
            ->columns(self::columns())
            ->filters(self::filters())
            ->recordAction('view')
            ->recordActions([])
            ->toolbarActions(self::bulkActions());
    }

    public static function columns(): array
    {
        return [
            Split::make([
                TextColumn::make('type')
                    ->label('Program')
                    ->description('Program', position: 'above')
                    ->extraAttributes(['class' => 'uppercase']),
                TextColumn::make('schools')
                    ->label('Sekolah')
                    ->description('Sekolah', position: 'above')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('periode')
                    ->label('Periode')
                    ->description('Periode', position: 'above')
                    ->extraAttributes(['class' => 'uppercase'])
                    ->wrap(),
                TextColumn::make('years')
                    ->label('Tahun')
                    ->description('Tahun', position: 'above'),
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
            ])->from('md'),
        ];
    }

    public static function textColumns(): array
    {
        return [
            TextColumn::make('group')
                ->label('Grup')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            TextColumn::make('bimtek')
                ->label('Bimtek')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            TextColumn::make('account_count_created')->label('Jumlah Akun Dibuat'),
            TextColumn::make('implementer_count')->label('Jumlah Pelaksanaan'),
            TextColumn::make('difference')->label('Selisih'),
            TextColumn::make('students_download')->label('Siswa Download'),
            TextColumn::make('schools_download')->label('Sekolah Download'),
            TextColumn::make('pm')->label('PM'),
            TextColumn::make('counselor_consultation_date')
                ->label('Tanggal Konseling')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            TextColumn::make('student_consultation_date')
                ->label('Tanggal Konseling Siswa')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
        ];
    }

    public static function filters(): array
    {
        return [
            SelectFilter::make('periode')
                ->label('Periode')
                ->options(Periode::list())
                ->preload(),
            SelectFilter::make('education_level')
                ->label('Jenjang')
                ->options(Jenjang::list())
                ->preload()
                ->indicator('Jenjang'),
            SelectFilter::make('type')
                ->label('Program')
                ->options(Program::list())
                ->preload()
                ->indicator('Program'),
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
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
