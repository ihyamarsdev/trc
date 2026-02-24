<?php

namespace App\Imports;

use App\Models\RegistrationData;
use Carbon\Carbon;
use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesImport implements ToModel, WithHeadingRow
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Lewati baris yang kosong (biasanya terjadi karena format validasi Excel terbaca sebagai baris)
        if (empty($row['provinsi']) && empty($row['kota_kabupaten']) && empty($row['kecamatan'])) {
            return null;
        }

        $province = Province::search($row['provinsi'])->first();
        if (!$province) {
            throw new \Exception('Provinsi tidak ditemukan: ' . $row['provinsi']);
        }
        $provinceName = $province->name;

        $regency = Regency::search($row['kota_kabupaten'])->first();
        if (!$regency) {
            throw new \Exception('Kota / Kabupaten tidak ditemukan: ' . $row['kota_kabupaten']);
        }
        $regencyName = $regency->name;

        $district = District::search($row['kecamatan'])->first();
        if (!$district) {
            throw new \Exception('Kecamatan tidak ditemukan: ' . $row['kecamatan']);
        }
        $districtName = $district->name;

        // Tentukan apakah memenuhi syarat "Wajib" (Red label) dari SalesForce schema
        $requiredFieldsFilled =
            !empty($row['tanggal_pendaftaran']) &&
            !empty($row['jumlah_siswa']) &&
            !empty($row['estimasi_pelaksanaan']) &&
            !empty($row['sekolah']) &&
            !empty($row['kepala_sekolah']) &&
            !empty($row['no_hp_kepala_sekolah']) &&
            !empty($row['wakakurikulum']) &&
            !empty($row['no_hp_wakakurikulum']);

        // Jika semua terisi, status order = 2. Jika tidak, order = 1.
        $targetOrder = $requiredFieldsFilled ? 2 : 1;

        $statusRecord = \App\Models\Status::where('order', $targetOrder)->first()
            ?? \App\Models\Status::first();

        $data = RegistrationData::updateOrCreate([
            'type' => $row['program'],
            'periode' => $row['periode'],
            'years' => $row['tahun'],
            'date_register' => self::parseDate($row['tanggal_pendaftaran']),
            'provinces' => $provinceName,
            'regencies' => $regencyName,
            'district' => $districtName,
            'area' => $row['wilayah'],
            'curriculum_deputies' => $row['wakakurikulum'],
            'curriculum_deputies_phone' => $row['no_hp_wakakurikulum'],
            'counselor_coordinators' => $row['koordinator_bk'],
            'counselor_coordinators_phone' => $row['no_hp_koordinator_bk'],
            'proctors' => $row['proktor'],
            'proctors_phone' => $row['no_hp_proktor'],
            'student_count' => $row['jumlah_siswa'],
            'implementation_estimate' => self::parseDate($row['estimasi_pelaksanaan']),

            'schools' => $row['sekolah'],
            'class' => $row['kelas'],
            'education_level' => $row['jenjang'],
            'description' => $row['keterangan'],
            'principal' => $row['kepala_sekolah'],
            'principal_phone' => $row['no_hp_kepala_sekolah'],
            'schools_type' => $row['negeri_swasta'],

            'users_id' => Auth::id(),
            'status_id' => $statusRecord?->id ?? 1,
            'status_color' => $statusRecord?->color ?? 'red',

        ]);

        return $data;
    }

    private function parseDate($dateString)
    {
        if ($dateString) {
            if (is_numeric($dateString)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString));
            } else {
                return Carbon::parse($dateString);
            }
        }

        return null;
    }
}
