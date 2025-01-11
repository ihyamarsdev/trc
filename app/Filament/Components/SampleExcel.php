<?php

namespace App\Filament\Components;

use Maatwebsite\Excel\Concerns\FromArray;

class SampleExcel implements FromArray
{
    public function array(): array
    {
        return [
            ['Periode', 'Tahun', 'Tanggal Pendaftaran', 'Provinsi', 'Kota / Kabupaten', 'Kecamatan', 'Wilayah', 'Jumlah Siswa', 'Estimasi Pelaksanaan', 'Sekolah', 'Kelas', 'Jenjang', 'Keterangan', 'Principal', 'Phone Principal', 'Negeri / Swasta', 'Wakakurikulum', 'No Hp Wakakurikulum', 'Koordinator BK', 'No Hp Koordinator BK', 'Proktor', 'No Hp Proktor'],
            ['Januari - Juni', '2025', '01-01-2025', 'Jawa Tengah', 'Kab. Magelang', 'Mungkit', null, 156, '01-01-2025', 'SMAN 1 Magelang', 11, 'SMA', 'NON-ABK', 'ABDUL AZIZ', '85649450877', 'Negeri', 'ABDUL AZIZ', '85649450877', 'ABDUL AZIZ', '85649450877', 'ABDUL AZIZ', '85649450877',],
        ];
    }
}
