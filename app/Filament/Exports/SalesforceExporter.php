<?php

namespace App\Filament\Exports;

use Carbon\Carbon;
use App\Models\Salesforce;
use App\Models\RegistrationData;
use Filament\Actions\Exports\Exporter;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;

;

class SalesforceExporter extends Exporter
{
    protected static ?string $model = RegistrationData::class;

    public static function getColumns(): array
    {
        Carbon::setLocale('id');
        return [
            ExportColumn::make('users.name')
                ->label('User'),
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
            ExportColumn::make('sudin')
                ->label('Wilayah'),
            ExportColumn::make('district')
                ->label('Kecamatan'),
            ExportColumn::make('student_count')
                ->label('Jumlah Siswa'),
            ExportColumn::make('implementation_estimate')
                ->label('Estimasi Pelaksanaan')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('schools')
                ->label('Sekolah'),
            ExportColumn::make('class')
                ->label('Kelas'),
            ExportColumn::make('education_level')
                ->label('Jenjang'),
            ExportColumn::make('description')
                ->label('Keterangan'),
            ExportColumn::make('principal')
                ->label('Kepala Sekolah'),
            ExportColumn::make('phone_principal')
                ->label('No Hp Kepala Sekolah'),
            ExportColumn::make('education_level_type')
                ->label('Negeri / Swasta'),
            ExportColumn::make('curriculum_deputies.name')
                ->label('Wakakurikulum'),
            ExportColumn::make('curriculum_deputies.phone')
                ->label('No Hp Wakakurikulum'),
            ExportColumn::make('counselor_coordinators.name')
                ->label('Koordinator BK'),
            ExportColumn::make('counselor_coordinators.phone')
                ->label('No Hp Koordinator BK'),
            ExportColumn::make('proctors.name')
                ->label('Proktor'),
            ExportColumn::make('proctors.phone')
                ->label('No Hp Proktor'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your salesforce export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
        return "Salesforce-data-{$export->getKey()}";
    }
}
