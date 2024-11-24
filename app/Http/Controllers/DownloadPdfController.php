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
            'schools' => $record->schools,
            'detail' => $record->detail_kwitansi
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
            'schools' => $record->schools,
            'detail' => $record->detail_kwitansi
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
        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun

        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
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
        $tanggalDalamKata = $this->angkaKeKata($tanggalHari);
        $tahunDalamKata = $this->angkaKeKata($tahun);

        // Gabungkan menjadi kalimat
        $kalimatTanggal = "{$hariTerjemahan[$hari]}, tanggal {$tanggalDalamKata} Bulan {$bulanTerjemahan[$bulan]} Tahun {$tahunDalamKata}";

        return $kalimatTanggal;
    }

    // Fungsi untuk mengonversi angka menjadi kata
    private function angkaKeKata($angka)
    {
        $angkaKata = [
            1 => 'Satu',
            2 => 'Dua',
            3 => 'Tiga',
            4 => 'Empat',
            5 => 'Lima',
            6 => 'Enam',
            7 => 'Tujuh',
            8 => 'Delapan',
            9 => 'Sembilan',
            10 => 'Sepuluh',
            11 => 'Sebelas',
            12 => 'Dua belas',
            13 => 'Tiga belas',
            14 => 'Empat belas',
            15 => 'Lima belas',
            16 => 'Enam belas',
            17 => 'Tujuh belas',
            18 => 'Delapan belas',
            19 => 'Sembilan belas',
            20 => 'Dua puluh',
            30 => 'Tiga puluh',
            31 => 'Tiga puluh satu',
            // Tambahkan lebih banyak angka sesuai kebutuhan
        ];

        return $angkaKata[$angka] ?? $angka; // Kembalikan angka jika tidak ada terjemahan
    }

    public function angkaKeTeks($angka)
    {
        // Array untuk menyimpan nama angka
        $huruf = [
            0 => 'Nol',
            1 => 'Satu',
            2 => 'Dua',
            3 => 'Tiga',
            4 => 'Empat',
            5 => 'Lima',
            6 => 'Enam',
            7 => 'Tujuh',
            8 => 'Delapan',
            9 => 'Sembilan',
            10 => 'Sepuluh',
            11 => 'Sebelas',
            12 => 'Dua Belas',
            13 => 'Tiga Belas',
            14 => 'Empat Belas',
            15 => 'Lima Belas',
            16 => 'Enam Belas',
            17 => 'Tujuh Belas',
            18 => 'Delapan Belas',
            19 => 'Sembilan Belas',
            20 => 'Dua Puluh',
            30 => 'Tiga Puluh',
            40 => 'Empat Puluh',
            50 => 'Lima Puluh',
            60 => 'Enam Puluh',
            70 => 'Tujuh Puluh',
            80 => 'Delapan Puluh',
            90 => 'Sembilan Puluh',
            100 => 'Seratus',
            200 => 'Dua Ratus',
            300 => 'Tiga Ratus',
            400 => 'Empat Ratus',
            500 => 'Lima Ratus',
            600 => 'Enam Ratus',
            700 => 'Tujuh Ratus',
            800 => 'Delapan Ratus',
            900 => 'Sembilan Ratus',
            1000 => 'Seribu',
            1000000 => 'Satu Juta',
            1000000000 => 'Satu Miliar'
        ];

        // Cek jika angka lebih dari 1 miliar
        if ($angka >= 1000000000) {
            return 'Angka terlalu besar';
        }

        // Proses konversi
        if ($angka < 0) {
            return 'Minus ' . self::angkaKeTeks(abs($angka));
        } elseif ($angka < 21) {
            return $huruf[$angka];
        } elseif ($angka < 100) {
            return $huruf[floor($angka / 10) * 10] . ($angka % 10 ? ' ' . $huruf[$angka % 10] : '');
        } elseif ($angka < 1000) {
            return $huruf[floor($angka / 100) * 100] . ($angka % 100 ? ' ' . self::angkaKeTeks($angka % 100) : '');
        } elseif ($angka < 1000000) {
            return self::angkaKeTeks(floor($angka / 1000)) . ' Ribu' . ($angka % 1000 ? ' ' . self::angkaKeTeks($angka % 1000) : '');
        } elseif ($angka < 1000000000) {
            return self::angkaKeTeks(floor($angka / 1000000)) . ' Juta' . ($angka % 1000000 ? ' ' . self::angkaKeTeks($angka % 1000000) : '');
        }
    }

    // Fungsi untuk mengonversi angka menjadi teks dan menambahkan "Rupiah"
    public function formatKeRupiah($angka)
    {
        return self::angkaKeTeks($angka) . ' Rupiah';
    }

}
