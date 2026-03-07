<?php

namespace App\Filament\User\Resources\Service\Forms;

use App\Filament\Enum\Program;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServiceForm
{
    protected static function meta(Get $get): array
    {
        return Program::meta($get('type'));
    }

    protected static function metaInfo(Model $record): array
    {
        return Program::meta($record->type, true);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components())
            ->extraAttributes([
                'onkeydown' => "
                if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
                    event.preventDefault();
                    let focusables = Array.from(document.querySelectorAll('input, select, button, [contenteditable]'));
                    let index = focusables.indexOf(event.target);
                    if (index > -1 && focusables[index + 1]) {
                        focusables[index + 1].focus();
                    }
                }
            ",
            ]);
    }

    public static function components(): array
    {
        return [
            Section::make('Form Service')
                ->description('Status, informasi program, dan data konsultasi')
                ->schema([
                    Section::make('Status')
                        ->description('Isi sesuai dengan status saat ini')
                        ->schema([
                            Select::make('status_id')
                                ->label('Status')
                                ->preload()
                                ->relationship(
                                    name: 'status',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query
                                        ->where('order', '<=', 10)
                                        ->orderBy('order'),
                                )
                                ->searchable()
                                ->placeholder('Pilih status...')
                                ->columnSpan(1),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    Section::make(fn (Get $get) => self::meta($get)['nameRegister'])
                        ->description(
                            fn (Get $get) => self::meta($get)['DescriptionRegister'],
                        )
                        ->schema([
                            DatePicker::make('group')
                                ->label('Grup')
                                ->native(false)
                                ->displayFormat('l, jS F Y'),
                            DatePicker::make('bimtek')
                                ->label('Bimtek')
                                ->native(false)
                                ->displayFormat('l, jS F Y'),
                            TextInput::make('account_count_created')
                                ->label('Jumlah Akun Dibuat')
                                ->live(debounce: 1000)
                                ->default('0')
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::getDifference($get, $set);
                                }),
                            TextInput::make('implementer_count')
                                ->label('Jumlah Akun Pelaksanaan')
                                ->live(debounce: 1000)
                                ->default('0')
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::getDifference($get, $set);
                                }),
                            TextInput::make('difference')
                                ->label('Selisih')
                                ->readOnly()
                                ->numeric()
                                ->minValue(0)
                                ->live(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    Section::make('Konsultasi')
                        ->description('Data Konsultasi')
                        ->schema([
                            Section::make('')
                                ->schema([
                                    Radio::make('schools_download')
                                        ->label('Download Sekolah')
                                        ->options([
                                            'YA' => 'YA',
                                            'TIDAK' => 'TIDAK',
                                        ])
                                        ->inline(),
                                    Radio::make('students_download')
                                        ->label('Download Siswa')
                                        ->options([
                                            'YA' => 'YA',
                                            'TIDAK' => 'TIDAK',
                                        ])
                                        ->inline(),
                                    Radio::make('pm')
                                        ->label('PM')
                                        ->options([
                                            'YA' => 'YA',
                                            'TIDAK' => 'TIDAK',
                                        ])
                                        ->inline(),
                                ])
                                ->columns(2),
                            Section::make('')
                                ->schema([
                                    DatePicker::make('counselor_consultation_date')
                                        ->label('Konsul BK')
                                        ->native(false)
                                        ->displayFormat('l, jS F Y'),
                                    DatePicker::make('student_consultation_date')
                                        ->label('Konsul Siswa')
                                        ->native(false)
                                        ->displayFormat('l, jS F Y'),
                                ])
                                ->columns(2),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->columns(['lg' => 12])
                ->columnSpanFull(),
        ];
    }

    public static function getDifference(Get $get, Set $set): void
    {
        $accountCount = (int) $get('account_count_created');
        $implementerCount = (int) $get('implementer_count');

        if ($accountCount !== 0 || $implementerCount !== 0) {
            $set('difference', abs($accountCount - $implementerCount));
        } else {
            $set('difference', 0);
        }
    }
}
