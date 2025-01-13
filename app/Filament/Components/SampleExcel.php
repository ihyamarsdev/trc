<?php

namespace App\Filament\Components;

use Maatwebsite\Excel\Concerns\FromArray;

class SampleExcel implements FromArray
{
    public function array(): array
    {
        return [
            ['Periode', 'Tahun', 'Tanggal Pendaftaran', 'Provinsi', 'Kota / Kabupaten', 'Kecamatan', 'Wilayah', 'Jumlah Siswa', 'Estimasi Pelaksanaan', 'Sekolah', 'Kelas', 'Jenjang', 'Keterangan', 'Kepala Sekolah', 'No Hp Kepala Sekolah', 'Negeri / Swasta', 'Wakakurikulum', 'No Hp Wakakurikulum', 'Koordinator BK', 'No Hp Koordinator BK', 'Proktor', 'No Hp Proktor'],
            ['Januari - Juni', '2025', '1/1/2025', 'Jawa Tengah', 'Kab. Magelang', 'Mungkid', null, 156, '1/1/2025', 'SMAN 1 Magelang', 11, 'SMA', 'NON-ABK', 'ABDUL AZIZ', '85649450877', 'Negeri', 'ABDUL AZIZ', '85649450877', 'ABDUL AZIZ', '85649450877', 'ABDUL AZIZ', '85649450877',],
        ];
    }
}
