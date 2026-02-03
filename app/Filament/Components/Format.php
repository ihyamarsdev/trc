<?php

namespace App\Filament\Components;

use Carbon\Carbon;

class Format
{
     public static function tanggal($tanggal): string
    {
        Carbon::setLocale('id');
        return Carbon::parse($tanggal)->translatedFormat('jS F Y');
    }

    public static function formatTanggal($tanggalInput)
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

    public static function formatKeRupiah($angka)
    {
        return terbilang($angka) . ' Rupiah';
    }
}