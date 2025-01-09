<?php

namespace App\Imports\academic;

use COM;
use Carbon\Carbon;
use App\Models\Proctors;
use App\Models\SchoolYear;
use App\Models\RegistrationData;
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

        $anbk = RegistrationData::updateOrCreate([
            'type' => 'anbk',
            'periode' => $row['periode'],
            'school_years_id' => $schoolYear,
            'date_register' => self::parseDate($row['tanggal_pendaftaran']),
            'provinces' => $row['provinsi'],
            'regencies' => $row['kota_kabupaten'],
            'district' => $row['kecamatan'],
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
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString)->format('Y-m-d');
        }

        return null;
    }
}
