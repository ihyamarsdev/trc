<?php

namespace App\Filament\Exports;

use Carbon\Carbon;
use App\Models\RegistrationData;
use Filament\Actions\Exports\Exporter;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;

class RegistrationDataExporter extends Exporter
{
    protected static ?string $model = RegistrationData::class;

    public static function getColumns(): array
    {
        Carbon::setLocale('id');

        return [
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
            ExportColumn::make('price')
                ->label('Harga'),
            ExportColumn::make('total')
                ->label('Total'),
            ExportColumn::make('net')
                ->label('Net'),
            ExportColumn::make('total_net')
                ->label('Total Net'),
            ExportColumn::make('invoice_date')
                ->label('Tanggal Invoice')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('spk_sent')
                ->label('SPK Terkirim')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('payment_date')
                ->label('Tanggal Pembayaran')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('payment')
                ->label('Pembayaran'),
            ExportColumn::make('cb')
                ->label('CB'),
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
            ExportColumn::make('users.name')
                ->label('User Salesforce'),
            ExportColumn::make('school_years.name')
                ->label('Tahun Ajaran'),
            ExportColumn::make('net_2')
                ->label('Net 2'),
            ExportColumn::make('student_count_1')
                ->label('Jumlah Siswa 1'),
            ExportColumn::make('student_count_2')
                ->label('Jumlah Siswa 2'),
            ExportColumn::make('subtotal_1')
                ->label('Subtotal 1'),
            ExportColumn::make('subtotal_2')
                ->label('Subtotal 2'),
            ExportColumn::make('difference_total')
                ->label('Selisih Total'),
            ExportColumn::make('detail_kwitansi')
                ->label('Detail Kwitansi'),
            ExportColumn::make('detail_invoice')
                ->label('Detail Invoice'),
            ExportColumn::make('number_invoice')
                ->label('Nomor Invoice'),
            ExportColumn::make('qty_invoice')
                ->label('Jumlah Invoice'),
            ExportColumn::make('unit_price')
                ->label('Unit Invoice'),
            ExportColumn::make('amount_invoice')
                ->label('Jumlah Invoice'),
            ExportColumn::make('tax_rate')
                ->label('Pajak'),
            ExportColumn::make('sales_tsx')
                ->label('Sales Tax'),
            ExportColumn::make('other')
                ->label('Lain-lain'),
            ExportColumn::make('subtotal_invoice')
                ->label('Subtotal Invoice'),
            ExportColumn::make('total_invoice')
                ->label('Total Invoice'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your registration data export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
        return "registration-data-{$export->getKey()}";
    }
}
