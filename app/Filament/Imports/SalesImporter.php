<?php

namespace App\Filament\Imports;

use App\Models\Sales;
use App\Models\RegistrationData;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use HayderHatem\FilamentExcelImport\Traits\CanAccessAdditionalFormData;

class SalesImporter extends Importer
{
    
    protected static ?string $model = RegistrationData::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('Program')->label('type')->requiredMapping(),
            ImportColumn::make('Periode')->label('periode'),
            ImportColumn::make('Tahun')->label('years')->requiredMapping(),
            ImportColumn::make('Tanggal Pendaftaran')->label('date_register')->requiredMapping(),
            ImportColumn::make('Provinsi')->label('provinces')->requiredMapping(),
            ImportColumn::make('Kabupaten')->label('regencies')->requiredMapping(),
            ImportColumn::make('Kecamatan')->label('district')->requiredMapping(),
            ImportColumn::make('Area')->label('area'),
            ImportColumn::make('Jumlah Siswa')->label('student_count'),
            ImportColumn::make('Koordinator BK')->label('counselor_coordinators'),
            ImportColumn::make('No HP Koordinator BK')->label('counselor_coordinators_phone'),
            ImportColumn::make('Wakakurikulum')->label('curriculum_deputies'),
            ImportColumn::make('No HP Wakakurikulum')->label('curriculum_deputies_phone'),
            ImportColumn::make('Proktor')->label('proctors'),
            ImportColumn::make('No HP Proktor')->label('proctors_phone'),
            ImportColumn::make('Sekolah')->label('schools')->requiredMapping(),
            ImportColumn::make('Negeri')->label('schools_type'),
            ImportColumn::make('Kelas')->label('class'),
            ImportColumn::make('Jenjang')->label('education_level'),
            ImportColumn::make('Keterangan')->label('description'),
            ImportColumn::make('Kepala Sekolah')->label('principal'),
            ImportColumn::make('No HP Kepala Sekolah')->label('principal_phone'),
            ImportColumn::make('Estimasi Pelaksanaan')->label('implementation_estimate'),
        ];
    }

    public function resolveRecord(): ?RegistrationData
    {
        // return Sales::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new RegistrationData();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your sales import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
