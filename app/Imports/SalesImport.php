<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\RegistrationData;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Creasi\Nusa\Models\{Province, Regency, District};

class SalesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $province = Province::search($row['provinsi'])->first();
        if (!$province) {
            throw new \Exception("Provinsi tidak ditemukan: " . $row['provinsi']);
        }
        $provinceName = $province->name;

        $regency = Regency::search($row['kota_kabupaten'])->first();
        if (!$regency) {
            throw new \Exception("Kota / Kabupaten tidak ditemukan: " . $row['kota_kabupaten']);
        }
        $regencyName = $regency->name;

        $district = District::search($row['kecamatan'])->first();
        if (!$district) {
            throw new \Exception("Kecamatan tidak ditemukan: " . $row['kecamatan']);
        }
        $districtName = $district->name;


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
            'phone_principal' => $row['no_hp_kepala_sekolah'],
            'schools_type' => $row['negeri_swasta'],

            'users_id' => Auth::id(),
            'status_id' => 1,
            'status_color' => 'red',

        ]);

        return $data;
    }

    private function parseDate($dateString)
    {

        if ($dateString) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString));
        }

        return null;
    }
}
