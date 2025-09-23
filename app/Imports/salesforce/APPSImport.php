<?php

namespace App\Imports\salesforce;

use App\Models\Proctors;
use App\Models\RegistrationData;
use App\Models\CurriculumDeputies;
use App\Models\CounselorCoordinator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Creasi\Nusa\Models\{Province, Regency, District};

class APPSImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Validasi data
        $validator = Validator::make($row, [
            'tahun' => 'required',
            'wakakurikulum' => 'required',
            'no_hp_wakakurikulum' => 'required',
            'koordinator_bk' => 'required',
            'no_hp_koordinator_bk' => 'required',
            'proktor' => 'required',
            'no_hp_proktor' => 'required',
            'provinsi' => 'required',
            'kota_kabupaten' => 'required',
            'kecamatan' => 'required',
            'periode' => 'required',
            'tanggal_pendaftaran' => 'required',
            'jumlah_siswa' => 'required',
            'estimasi_pelaksanaan' => 'required',
            'sekolah' => 'required|string',
            'kelas' => 'required',
            'jenjang' => 'required|string',
            'keterangan' => 'nullable|string',
            'kepala_sekolah' => 'required|string',
            'no_hp_kepala_sekolah' => 'required',
            'negeri_swasta' => 'required|string',
        ]);


        // Jika validasi gagal, lempar pengecualian
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

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

        $anbk = RegistrationData::updateOrCreate([
            'type' => 'apps',
            'periode' => $row['periode'],
            'years' => $row['tahun'],
            'date_register' => self::parseDate($row['tanggal_pendaftaran']),
            'provinces' => $provinceName,
            'regencies' => $regencyName,
            'district' => $districtName,
            'sudin' => $row['wilayah'],
            'curriculum_deputies' => $row['wakakurikulum'],
            'curriculum_deputies_phone' => $row['no_hp_wakakurikulum'],
            'counselor_coordinators' => $row['koordinator_bk'],
            'counselor_coordinators_phone' => $row['no_hp_koordinator_bk'],
            'proctors' => $row['proktor'],
            'proctors_phone' => $row['no_hp_proktor'],
            'student_count' => $row['jumlah_siswa'],
            'implementation_estimate' => self::parseDate($row['estimasi_pelaksanaan']),

            'users_id' => Auth::id(),
            'schools' => $row['sekolah'],
            'class' => $row['kelas'],
            'education_level' => $row['jenjang'],
            'description' => $row['keterangan'],
            'principal' => $row['kepala_sekolah'],
            'phone_principal' => $row['no_hp_kepala_sekolah'],
            'education_level_type' => $row['negeri_swasta'],
        ]);

        return $anbk;
    }

    private function parseDate($dateString)
    {

        if ($dateString) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString);
        }

        return null;
    }
}
