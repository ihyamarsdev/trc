<?php

namespace App\Imports\datacenter;

use App\Models\Proctors;
use App\Models\RegistrationData;
use App\Models\CurriculumDeputies;
use App\Models\CounselorCoordinator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class APPSImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $anbk = RegistrationData::updateOrCreate([
            'type' => 'apps',
            'periode' => $row['periode'],
            'years' => $row['tahun'],
            'date_register' => self::parseDate($row['tanggal_pendaftaran']),
            'provinces' => $row['provinsi'],
            'regencies' => $row['kota_kabupaten'],
            'district' => $row['kecamatan'],
            'area' => $row['wilayah'],
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
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString)->format('Y-m-d');
        }

        return null;
    }
}
