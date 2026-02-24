<?php

namespace App\Filament\Components;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\District;

class SampleExcel implements FromArray, WithEvents
{
    public function array(): array
    {
        return [
            ['Program', 'Periode', 'Tahun', 'Tanggal Pendaftaran', 'Provinsi', 'Kota / Kabupaten', 'Kecamatan', 'Wilayah', 'Jumlah Siswa', 'Estimasi Pelaksanaan', 'Sekolah', 'Kelas', 'Jenjang', 'Keterangan', 'Kepala Sekolah', 'No Hp Kepala Sekolah', 'Negeri / Swasta', 'Wakakurikulum', 'No Hp Wakakurikulum', 'Koordinator BK', 'No Hp Koordinator BK', 'Proktor', 'No Hp Proktor'],
            ['anbk', 'Periode 1', '2025', '06/06/2025', 'Jawa Tengah', 'Kabupaten Magelang', 'Mungkid', null, 156, '07/07/2025', 'SMAN 1 Magelang', 11, 'SMA', 'NON-ABK', 'ABDUL AZIZ', '85649450877', 'Negeri', 'ABDUL AZIZ', '85649450877', 'ABDUL AZIZ', '85649450877', 'ABDUL AZIZ', '85649450877',]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Ambil semua data master
                $provinces = Province::orderBy('name')->pluck('name')->toArray();

                $regencies = Regency::join('provinces', 'regencies.province_code', '=', 'provinces.code')
                    ->orderBy('provinces.name')->orderBy('regencies.name')
                    ->select('regencies.name as regency', 'provinces.name as province')
                    ->get();

                $districts = District::join('regencies', 'districts.regency_code', '=', 'regencies.code')
                    ->orderBy('regencies.name')->orderBy('districts.name')
                    ->select('districts.name as district', 'regencies.name as regency')
                    ->get();

                $wilayahs = [
                    "KS 01",
                    "KS 02",
                    "JP 01",
                    "JP 02",
                    "JU 01",
                    "JU 02",
                    "JB 01",
                    "JB 02",
                    "JS 01",
                    "JS 02",
                    "JT 01",
                    "JT 02"
                ];

                // 2. Tulis data ke kolom tersembunyi
                // AA = Provinces
                // AB = Parent Province, AC = Regency
                // AD = Parent Regency, AE = District
                // AF = Wilayahs
    
                foreach ($provinces as $index => $item) {
                    $sheet->setCellValue('AA' . ($index + 1), $item);
                }
                foreach ($regencies as $index => $item) {
                    $sheet->setCellValue('AB' . ($index + 1), $item->province);
                    $sheet->setCellValue('AC' . ($index + 1), $item->regency);
                }
                foreach ($districts as $index => $item) {
                    $sheet->setCellValue('AD' . ($index + 1), $item->regency);
                    $sheet->setCellValue('AE' . ($index + 1), $item->district);
                }
                foreach ($wilayahs as $index => $item) {
                    $sheet->setCellValue('AF' . ($index + 1), $item);
                }

                // Sembunyikan kolom referensi tersebut
                $sheet->getColumnDimension('AA')->setVisible(false);
                $sheet->getColumnDimension('AB')->setVisible(false);
                $sheet->getColumnDimension('AC')->setVisible(false);
                $sheet->getColumnDimension('AD')->setVisible(false);
                $sheet->getColumnDimension('AE')->setVisible(false);
                $sheet->getColumnDimension('AF')->setVisible(false);

                // 3. Buat aturan Validasi (Dropdown List)
                $createValidation = function ($formula) {
                    $validation = new DataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Input tidak valid');
                    $validation->setError('Harap pilih data dari dropdown yang tersedia.');
                    $validation->setFormula1($formula);
                    return $validation;
                };

                $provValidation = $createValidation('=$AA$1:$AA$' . count($provinces));
                $wilValidation = $createValidation('=$AF$1:$AF$' . count($wilayahs));

                // 4. Terapkan validasi ke kolom input (mulai baris ke-2 hingga ke-1000)
                for ($row = 2; $row <= 1000; $row++) {
                    // Kolom E: Provinsi
                    $sheet->getCell('E' . $row)->setDataValidation(clone $provValidation);

                    // Kolom F: Kota / Kabupaten (Dependent List via OFFSET MATCH)
                    // IF E is blank, return empty range (or a single cell #N/A). To prevent formula errors, we can wrap it. 
                    // However, in PhpSpreadsheet, assigning the formula text directly is preferred.
                    $regFormula = '=IF(E' . $row . '="", $AC$1:$AC$1, OFFSET($AC$1, MATCH(E' . $row . ', $AB:$AB, 0)-1, 0, COUNTIF($AB:$AB, E' . $row . '), 1))';
                    $regValidation = $createValidation($regFormula);
                    $sheet->getCell('F' . $row)->setDataValidation($regValidation);

                    // Kolom G: Kecamatan (Dependent List via OFFSET MATCH)
                    $distFormula = '=IF(F' . $row . '="", $AE$1:$AE$1, OFFSET($AE$1, MATCH(F' . $row . ', $AD:$AD, 0)-1, 0, COUNTIF($AD:$AD, F' . $row . '), 1))';
                    $distValidation = $createValidation($distFormula);
                    $sheet->getCell('G' . $row)->setDataValidation($distValidation);

                    // Kolom H: Wilayah
                    $sheet->getCell('H' . $row)->setDataValidation(clone $wilValidation);
                }
            },
        ];
    }
}
