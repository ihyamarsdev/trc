<?php

namespace App\Filament\Components;

use App\Filament\Components\Support\FormSchema;
use App\Filament\Components\Support\HasProgramMetadata;
use App\Filament\Components\Support\InfolistSchema;
use App\Filament\Components\Support\SharedSchema;
use App\Filament\Enum\OptionGroup;
use App\Models\Option;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service
{
    use HasProgramMetadata;

    public static function formSchema(): array
    {
        return [
            Section::make(fn (Get $get) => self::meta($get)['nameRegister'])
                ->description(fn (Get $get) => self::meta($get)['DescriptionRegister'])
                ->icon('heroicon-o-flag')
                ->schema([
                    Select::make('status_id')
                        ->label('Status')
                        ->helperText('Isi sesuai dengan status saat ini')
                        ->preload()
                        ->relationship(
                            name: 'status',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->where('order', '<=', 10)
                                ->orderBy('order'),
                        )
                        ->searchable()
                        ->placeholder('Pilih status...'),
                ]),

            ...FormSchema::serviceSections(),
        ];
    }

    public static function columns(): array
    {
        return SharedSchema::columns();
    }

    public static function TextColumns(): array
    {
        return [
            TextColumn::make('group')
                ->label('Grup')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('bimtek')
                ->label('Bimtek')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('account_count_created')->label(
                'Jumlah Akun Dibuat',
            ),
            TextColumn::make('implementer_count')->label('Jumlah Pelaksanaan'),
            TextColumn::make('difference')->label('Selisih'),
            TextColumn::make('students_download')->label('Siswa Download'),
            TextColumn::make('schools_download')->label('Sekolah Download'),
            TextColumn::make('pm')->label('PM'),
            TextColumn::make('counselor_consultation_date')
                ->label('Tanggal Konseling')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
            TextColumn::make('student_consultation_date')
                ->label('Tanggal Konseling Siswa')
                ->formatStateUsing(
                    fn ($state) => Carbon::parse($state)->translatedFormat(
                        'l, jS F Y',
                    ),
                ),
        ];
    }

    public static function infolist(Model $record): array
    {
        return InfolistSchema::compose($record, [InfolistSchema::PART_SERVICE]);
    }

    public static function filters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('periode')
                ->label('Periode')
                ->options(Option::list(OptionGroup::Periode, onlyActive: false))
                ->preload(),
            Tables\Filters\SelectFilter::make('education_level')
                ->label('Jenjang')
                ->options(Option::list(OptionGroup::Jenjang, onlyActive: false))
                ->preload()
                ->indicator('Jenjang'),
            Tables\Filters\SelectFilter::make('type')
                ->label('Program')
                ->options(Option::list(OptionGroup::Program, onlyActive: false))
                ->preload()
                ->indicator('Program'),
            Tables\Filters\SelectFilter::make('users_id')
                ->label('User')
                ->options(function () {
                    return User::query()
                        ->select(['id', 'name'])
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all();
                })
                ->preload()
                ->indicator('user'),
            Tables\Filters\SelectFilter::make('status_color')
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

                    $query->whereHas(
                        'status',
                        fn (Builder $q) => $q->where('color', $data['value']),
                    );
                }),
        ];
    }

    public static function getDifference(Get $get, Set $set): void
    {
        SharedSchema::getDifference($get, $set);
    }

    public static function actions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            // Tables\Actions\EditAction::make(),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function getRoles(): array
    {
        return ['service'];
    }
}
