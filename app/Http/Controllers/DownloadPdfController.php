<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use NumberFormatter;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\RegistrationData;
use LaravelDaily\Invoices\Invoice;
use Filament\Notifications\Notification;
use LaravelDaily\Invoices\Classes\Buyer;
use PhpOffice\PhpWord\TemplateProcessor;
use LaravelDaily\Invoices\Classes\InvoiceItem;

class DownloadPdfController extends Controller
{
    public function anbk_rasyidu(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);


        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();

        $templateProcessor = new TemplateProcessor('template/rasyidu/spk.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $school_year->name,
            'tanggal' => self::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => self::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => self::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'SPK RASYIDUU ANBK ' . $record->schools . '.docx';

        $recipient = auth()->user();

        $recipient->notify(
            Notification::make()
                ->title('SPK Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function apps_rasyidu(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);
        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();


        $templateProcessor = new TemplateProcessor('template/rasyidu/spk.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $school_year->name,
            'tanggal' => self::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => self::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => self::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'SPK RASYIDUU APPS ' . $record->schools . '.docx';

        $recipient = auth()->user();

        $recipient->notify(
            Notification::make()
                ->title('SPK Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function snbt_rasyidu(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);
        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();

        $templateProcessor = new TemplateProcessor('template/rasyidu/spk.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $school_year->name,
            'tanggal' => self::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => self::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => self::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'SPK RASYIDUU SNBT ' . $record->schools . '.docx';

        $recipient = auth()->user();

        $recipient->notify(
            Notification::make()
                ->title('SPK Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function kwitansi_rasyidu(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);

        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();

        $templateProcessor = new TemplateProcessor('template/rasyidu/kuitansi.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $school_year->name,
            'tanggal' => self::tanggal($record->payment_date),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => self::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => self::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
            'schools' => $record->schools,
            'detail' => $record->detail_kwitansi,
            'principal' => $record->principal
        ]);

        $doc_name = 'KWITANSI RASYIDUU ' . $record->schools . '.docx';

        $recipient = auth()->user();

        $recipient->notify(
            Notification::make()
                ->title('Kwitansi Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function anbk_edunesia(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);
        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();


        $templateProcessor = new TemplateProcessor('template/edunesia/spk.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $school_year->name,
            'tanggal' => self::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => self::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => self::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'SPK EDUNESIA APPS ' . $record->schools . '.docx';

        $recipient = auth()->user();

        $recipient->notify(
            Notification::make()
                ->title('SPK Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function apps_edunesia(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);
        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();


        $templateProcessor = new TemplateProcessor('template/edunesia/spk.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $school_year->name,
            'tanggal' => self::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => self::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => self::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'SPK EDUNESIA APPS ' . $record->schools . '.docx';

        $recipient = auth()->user();

        $recipient->notify(
            Notification::make()
                ->title('SPK Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function snbt_edunesia(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);
        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();


        $templateProcessor = new TemplateProcessor('template/edunesia/spk.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $school_year->name,
            'tanggal' => self::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => self::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => self::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'SPK EDUNESIA APPS ' . $record->schools . '.docx';

        $recipient = auth()->user();

        $recipient->notify(
            Notification::make()
                ->title('SPK Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function kwitansi_edunesia(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);

        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();

        $templateProcessor = new TemplateProcessor('template/edunesia/kuitansi.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $school_year->name,
            'tanggal' => self::tanggal($record->payment_date),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => self::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => self::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
            'schools' => $record->schools,
            'detail' => $record->detail_kwitansi,
            'principal' => $record->principal
        ]);

        $doc_name = 'KWITANSI EDUNESIA ' . $record->schools . '.docx';

        $recipient = auth()->user();

        $recipient->notify(
            Notification::make()
                ->title('Kwitansi Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function tanggal($tanggal): string
    {
        Carbon::setLocale('id');
        return Carbon::parse($tanggal)->translatedFormat('jS F Y');
    }

    public function formatTanggal($tanggalInput)
    {
        // Buat objek Carbon dari tanggal yang diberikan
        $tanggal = Carbon::createFromFormat('Y-m-d', $tanggalInput);

        // Ambil bagian dari tanggal
        $hari = $tanggal->format('l'); // Hari dalam bahasa Inggris
        $tanggalHari = $tanggal->day; // Tanggal
        $bulan = $tanggal->format('F'); // Bulan dalam bahasa Inggris
        $tahun = $tanggal->year; // Tahun

        // Terjemahkan hari dan bulan ke bahasa Indonesia
        $hariTerjemahan = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $bulanTerjemahan = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember',
        ];

        // Konversi tanggal dan tahun ke dalam kata
        $tanggalDalamKata = terbilang($tanggalHari);
        $tahunDalamKata = terbilang($tahun);

        // Gabungkan menjadi kalimat
        $kalimatTanggal = "{$hariTerjemahan[$hari]}, tanggal {$tanggalDalamKata} Bulan {$bulanTerjemahan[$bulan]} Tahun {$tahunDalamKata}";

        return $kalimatTanggal;
    }

    public function formatKeRupiah($angka)
    {
        return terbilang($angka) . ' Rupiah';
    }

}
