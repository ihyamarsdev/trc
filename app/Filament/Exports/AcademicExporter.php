<?php

namespace App\Filament\Exports;

use Carbon\Carbon;
use App\Models\Academic;
use App\Models\RegistrationData;
use Filament\Actions\Exports\Exporter;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;

class AcademicExporter extends Exporter
{
    protected static ?string $model = RegistrationData::class;

    public static function getColumns(): array
    {
        Carbon::setLocale('id');
        return [
            ExportColumn::make('users.name')
                ->label('User Salesforce'),
            ExportColumn::make('type')
                ->label('Program'),
            ExportColumn::make('periode')
                ->label('Periode'),
            ExportColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('provinces')
                ->label('Provinsi'),
            ExportColumn::make('regencies')
                ->label('Kota / Kabupaten'),
            ExportColumn::make('student_count')
                ->label('Jumlah Siswa'),
            ExportColumn::make('implementation_estimate')
                ->label('Estimasi Pelaksanaan')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('schools')
                ->label('Sekolah'),
            ExportColumn::make('education_level')
                ->label('Jenjang'),
            ExportColumn::make('principal')
                ->label('Kepala Sekolah'),
            ExportColumn::make('phone_principal')
                ->label('No Hp Kepala Sekolah'),
            ExportColumn::make('education_level_type')
                ->label('Negeri / Swasta'),
            ExportColumn::make('curriculum_deputies.name')
                ->label('Wakakurikulum'),
            ExportColumn::make('counselor_coordinators.name')
                ->label('Koordinator Konseling'),
            ExportColumn::make('proctors.name')
                ->label('Pembimbing'),
            ExportColumn::make('group')
                ->label('Grup')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('bimtek')
                ->label('Bimtek')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('account_count_created')
                ->label('Jumlah Akun Dibuat'),
            ExportColumn::make('implementer_count')
                ->label('Jumlah Pelaksanaan'),
            ExportColumn::make('difference')
                ->label('Selisih'),
            ExportColumn::make('students_download')
                ->label('Siswa Download'),
            ExportColumn::make('schools_download')
                ->label('Sekolah Download'),
            ExportColumn::make('pm')
                ->label('PM'),
            ExportColumn::make('counselor_consultation_date')
                ->label('Tanggal Konseling')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('student_consultation_date')
                ->label('Tanggal Konseling Siswa')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your academic export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }


    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontName('Arial')
            ->setFontColor(Color::BLACK)
            ->setCellAlignment(CellAlignment::JUSTIFY)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText();
    }

    public function getFileName(Export $export): string
    {
        return "Akademik-data-{$export->getKey()}";
    }
}
