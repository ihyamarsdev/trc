<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use NumberFormatter;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\RegistrationData;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use PhpOffice\PhpWord\TemplateProcessor;
use LaravelDaily\Invoices\Classes\InvoiceItem;

class DownloadPdfController extends Controller
{
    public function download(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);


        $templateProcessor = new TemplateProcessor('template/rasyidu/anbk.docx');

        $templateProcessor->setValues([
            'deskripsi' => self::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => '2021/2022',
            'tanggal' => self::tanggal($record->date_register->format('Y-m-d')),
            'to' => 'ENAY WINARNI, S.Pd., M.Pd',
            'jabatan' => 'KEPALA SEKOLAH SMP NEGERI 1 DRAMAGA',
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => '150.000',
            'hargaSpell' => 'Seratus Lima Puluh Ribu Rupiah',
            'total' => '30.000.000',
            'totalSpell' => 'Tiga Puluh Juta Rupiah',
            'payment' => 'SIPLAH'
        ]);

        $doc_name = 'tes.docx';

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

}
