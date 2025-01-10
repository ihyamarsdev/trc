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

class FinanceExporter extends Exporter
{
    protected static ?string $model = RegistrationData::class;

    public static function getColumns(): array
    {
        Carbon::setLocale('id');

        return [
            //Datacenter
            ExportColumn::make('type')
                ->label('Program'),
            ExportColumn::make('periode')
                ->label('Periode'),
            ExportColumn::make('school_years.name')
                ->label('Tahun Ajaran'),
            ExportColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),
            ExportColumn::make('users.name')
                ->label('User Salesforce'),
            ExportColumn::make('provinces')
                ->label('Provinsi'),
            ExportColumn::make('regencies')
                ->label('Kota / Kabupaten'),
            ExportColumn::make('sudin')
                ->label('Daerah Tambahan'),
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
            ExportColumn::make('student_count')
                ->label('Jumlah Siswa'),
            ExportColumn::make('implementation_estimate')
                ->label('Estimasi Pelaksanaan')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('l, jS F Y')),

            //Akademic
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

            //Finance
            ExportColumn::make('price')
                ->label('Harga SPJ')
                ->prefix('Rp.'),
            ExportColumn::make('net_2')
                ->label('Harga NET')
                ->prefix('Rp.'),
            ExportColumn::make('option_price')
                ->label('Opsi Jumlah Akun / Jumlah Pelaksanaan '),
            ExportColumn::make('total')
                ->label('Total Dana Sesuai SPJ')
                ->prefix('Rp.'),
            ExportColumn::make('total_net')
                ->label('Total Net')
                ->prefix('Rp.'),

            ExportColumn::make('student_count_1')
                ->label('Selisih Siswa TRC'),
            ExportColumn::make('net')
                ->label('Satuan')
                ->prefix('Rp.'),
            ExportColumn::make('subtotal_1')
                ->label('Subtotal')
                ->prefix('Rp.'),

            ExportColumn::make('mitra_difference')
                ->label('elisih Siswa Sekolah'),
            ExportColumn::make('net')
                ->label('Satuan')
                ->prefix('Rp.'),
            ExportColumn::make('mitra_subtotal')
                ->label('Subtotal')
                ->prefix('Rp.'),

            ExportColumn::make('implementer_count')
                ->label('SS'),
            ExportColumn::make('ss_net')
                ->label('Satuan')
                ->prefix('Rp.'),
            ExportColumn::make('ss_subtotal')
                ->label('Subtotal')
                ->prefix('Rp.'),

            ExportColumn::make('dll_difference')
                ->label('Lain-lain'),
            ExportColumn::make('dll_net')
                ->label('Satuan')
                ->prefix('Rp.'),
            ExportColumn::make('dll_subtotal')
                ->label('Subtotal')
                ->prefix('Rp.'),

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


            //Kwitansi
            ExportColumn::make('detail_kwitansi')
                ->label('Detail Kwitansi'),

            //Invoice
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
                ->label('PPH 23')
                ->suffix('%'),
            ExportColumn::make('sales_tsx')
                ->label('PPN')
                ->suffix('%'),
            ExportColumn::make('subtotal_invoice')
                ->label('Subtotal Invoice')
                ->prefix('Rp.'),
            ExportColumn::make('total_invoice')
                ->label('Total Invoice')
                ->prefix('Rp.'),
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
        return "Finance-data-{$export->getKey()}";
    }
}
