<?php

namespace App\Imports;

use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use App\Models\Status;
use Carbon\Carbon;
use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SalesImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        $provinceNames = $rows->pluck('provinsi')->filter()->unique()->values()->all();
        $regencyNames = $rows->pluck('kota_kabupaten')->filter()->unique()->values()->all();
        $districtNames = $rows->pluck('kecamatan')->filter()->unique()->values()->all();

        $provinceLookup = $this->buildLookup($provinceNames, Province::class);
        $regencyLookup = $this->buildLookup($regencyNames, Regency::class);
        $districtLookup = $this->buildLookup($districtNames, District::class);

        $statuses = Status::query()->whereIn('order', [1, 2])->get()->keyBy('order');
        $fallbackStatus = Status::query()->first();
        $statusOrder1 = $statuses->get(1) ?? $fallbackStatus;
        $statusOrder2 = $statuses->get(2) ?? $fallbackStatus;

        $userId = Auth::id();

        foreach ($rows as $row) {
            // Lewati baris yang kosong (biasanya terjadi karena format validasi Excel terbaca sebagai baris)
            if (empty($row['provinsi']) && empty($row['kota_kabupaten']) && empty($row['kecamatan'])) {
                continue;
            }

            if (empty($row['provinsi'])) {
                throw new Exception('Kolom Provinsi kosong pada baris data Excel.');
            }

            $provinceKey = $this->normalizeKey($row['provinsi']);
            $province = $provinceLookup[$provinceKey] ?? null;
            if (! $province) {
                throw new Exception('Provinsi tidak ditemukan: '.$row['provinsi']);
            }
            $provinceName = $province->name;

            if (empty($row['kota_kabupaten'])) {
                throw new Exception('Kolom Kota / Kabupaten kosong pada baris data Excel.');
            }

            $regencyKey = $this->normalizeKey($row['kota_kabupaten']);
            $regency = $regencyLookup[$regencyKey] ?? null;
            if (! $regency) {
                throw new Exception('Kota / Kabupaten tidak ditemukan: '.$row['kota_kabupaten']);
            }
            $regencyName = $regency->name;

            if (empty($row['kecamatan'])) {
                throw new Exception('Kolom Kecamatan kosong pada baris data Excel.');
            }

            $districtKey = $this->normalizeKey($row['kecamatan']);
            $district = $districtLookup[$districtKey] ?? null;
            if (! $district) {
                throw new Exception('Kecamatan tidak ditemukan: '.$row['kecamatan']);
            }
            $districtName = $district->name;

            // Tentukan apakah memenuhi syarat "Wajib" (Red label) dari SalesForce schema
            $requiredFieldsFilled =
                ! empty($row['tanggal_pendaftaran']) &&
                ! empty($row['jumlah_siswa']) &&
                ! empty($row['estimasi_pelaksanaan']) &&
                ! empty($row['sekolah']) &&
                ! empty($row['kepala_sekolah']) &&
                ! empty($row['no_hp_kepala_sekolah']) &&
                ! empty($row['wakakurikulum']) &&
                ! empty($row['no_hp_wakakurikulum']);

            // Jika semua terisi, status order = 2. Jika tidak, order = 1.
            $statusRecord = $requiredFieldsFilled ? $statusOrder2 : $statusOrder1;

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

                'users_id' => $userId,
                'status_id' => $statusRecord?->id,
                'status_color' => $statusRecord?->color,
            ]);

            // Dapatkan Status Log terakhir untuk record ini
            $latestStatusId = RegistrationStatus::query()
                ->where('registration_id', $data->id)
                ->latest('id')
                ->value('status_id');

            // Jika belum ada log sama sekali (data baru) ATAU statusnya berubah dari log terakhir
            $computedStatusId = $statusRecord?->id ?? 1;

            if ($latestStatusId != $computedStatusId) {
                RegistrationStatus::create([
                    'registration_id' => $data->id,
                    'status_id' => $computedStatusId,
                    'user_id' => $userId,
                    'notes' => 'Di-import dari sistem',
                ]);
            }
        }
    }

    private function buildLookup(array $names, string $modelClass): array
    {
        if (empty($names)) {
            return [];
        }

        $normalizedNames = collect($names)
            ->map(fn ($name) => $this->normalizeKey($name))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedNames->isEmpty()) {
            return [];
        }

        $records = $modelClass::query()
            ->whereIn('name', $names)
            ->get();

        $lookup = [];

        foreach ($records as $record) {
            $lookup[$this->normalizeKey($record->name)] = $record;
        }

        $unresolved = $normalizedNames->filter(
            fn (string $name) => ! array_key_exists($name, $lookup)
        );

        foreach ($unresolved as $name) {
            $record = $modelClass::query()
                ->whereRaw('LOWER(name) = ?', [$name])
                ->first();

            if ($record instanceof Model) {
                $lookup[$name] = $record;
            }
        }

        return $lookup;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private static function normalizeKey(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    private static function parseDate($dateString)
    {
        if ($dateString) {
            if (is_numeric($dateString)) {
                return Carbon::instance(Date::excelToDateTimeObject($dateString));
            } else {
                $translatedDate = Carbon::translateTimeString($dateString, 'id', 'en');

                return Carbon::parse($translatedDate);
            }
        }

        return null;
    }
}
