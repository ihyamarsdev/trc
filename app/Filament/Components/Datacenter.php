<?php

namespace App\Filament\Components;

use Filament\Tables\Columns\TextColumn;
use Creasi\Nusa\Models\{Province, Regency};

class Datacenter
{
    public static function columns(): array
    {
        return [
            TextColumn::make('no')
                ->rowIndex(),
            TextColumn::make('periode')
                ->label('Periode'),
            TextColumn::make('school_years.name')
                ->label('Tahun Ajaran'),
            TextColumn::make('users.name')
                ->label('User')
                ->searchable(),
            TextColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->date()
                ->sortable(),
            TextColumn::make('provinces')
                ->label('Provinsi')
                ->formatStateUsing(function ($state) {
                    $province = Province::search($state)->first() ;
                    return $province ? $province->name : 'Unknown';
                }),
            TextColumn::make('regencies')
                ->label('Kota / Kabupaten')
                ->formatStateUsing(function ($state) {
                    $regency = Regency::search($state)->first();
                    return $regency ? $regency->name : 'Unknown';
                }),
            TextColumn::make('sudin')
                ->label('Daerah Tambahan'),
            TextColumn::make('schools')
                ->label('Sekolah'),
            TextColumn::make('education_level')
                ->label('Jenjang'),
            TextColumn::make('education_level_type')
                ->label('Negeri / Swasta'),
            TextColumn::make('principal')
                ->label('Kepala Sekolah'),
            TextColumn::make('phone_principal')
                ->label('No Hp Kepala Sekolah'),
            TextColumn::make('curriculum_deputies.name')
                ->label('Wakakurikulum'),
            TextColumn::make('curriculum_deputies.phone')
                ->label('No Hp Wakakurikulum'),
            TextColumn::make('counselor_coordinators.name')
                ->label('Koordinator BK'),
            TextColumn::make('counselor_coordinators.phone')
                ->label('No Hp Koordinator BK'),
            TextColumn::make('proctors.name')
                ->label('Proktor'),
            TextColumn::make('proctors.phone')
                ->label('No Hp Proktor'),
            TextColumn::make('student_count')
                ->label('Jumlah Siswa')
                ->numeric(),
            TextColumn::make('implementation_estimate')
                ->label('Estimasi Pelaksana')
                ->date(),
        ];
    }
}
