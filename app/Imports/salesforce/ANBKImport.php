<?php

namespace App\Imports\salesforce;

use COM;
use Carbon\Carbon;
use App\Models\Proctors;
use App\Models\SchoolYear;
use App\Models\RegistrationData;
use Creasi\Nusa\Models\{Province, Regency, District};
use App\Models\CurriculumDeputies;
use App\Models\CounselorCoordinator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ANBKImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $schoolYear = SchoolYear::firstOrCreate(
            ['name' => $row['tahun']],
        )->id;

        $curriculum_deputies = CurriculumDeputies::firstOrCreate([
            'name' => $row['wakakurikulum'],
            'phone' => $row['no_hp_wakakurikulum'],
        ])->id;

        $counselor_coordinators = CounselorCoordinator::firstOrCreate([
            'name' => $row['koordinator_bk'],
            'phone' => $row['no_hp_koordinator_bk'],
        ])->id;

        $proctors = Proctors::firstOrCreate([
            'name' => $row['proktor'],
            'phone' => $row['no_hp_proktor'],
        ])->id;

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
            'type' => 'anbk',
            'periode' => $row['periode'],
            'school_years_id' => $schoolYear,
            'date_register' => self::parseDate($row['tanggal_pendaftaran']),
            'provinces' => $provinceName,
            'regencies' => $regencyName,
            'district' => $districtName,
            'sudin' => $row['wilayah'],
            'curriculum_deputies_id' => $curriculum_deputies,
            'counselor_coordinators_id' => $counselor_coordinators,
            'proctors_id' => $proctors,
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
